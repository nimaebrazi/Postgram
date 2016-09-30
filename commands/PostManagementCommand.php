<?php

namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Commands\Entities;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\States;
use MohandesPlusBot\Handler\MessageHandler;
use MohandesPlusBot\Model\Channel;
use MohandesPlusBot\Model\Post;
use MohandesPlusBot\Model\User;

class PostManagementCommand extends MessageHandler
{


    protected $name;
    protected $description;
    protected $usage;
    protected $version;
    protected $enabled;
    protected $public;
    protected $message;

    protected $conversation;
    protected $telegram;


    public function __construct(Telegram $telegram, $update)
    {
        parent::__construct($telegram, $update);
        $this->telegram = $telegram;

        $this->name = Command::POST_MANAGEMENT;
        $this->description = Command::POST_MANAGEMENT_DESC;
        $this->usage = Command::POST_MANAGEMENT_USAGE;
        $this->version = Command::POST_MANAGEMENT_VERSION;
        $this->enabled = true;
        $this->public = true;
        $this->need_mysql = false;
    }


    public function execute()
    {

        /**
         * contact info
         *
         */
        $messageId = $this->getUpdate()->getMessage()->getMessageId();
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
        $userId = $this->getUpdate()->getMessage()->getFrom()->getId();


        /**
         * All Objects
         *
         */
        $user = new User();
        $post = new Post();
        $channel = new Channel();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());

        //fetch all channels for specific user
        $channels = $user->hasChannels($chatId);

        if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        switch ($user->getStateByChatId($chatId))
        {
            //get channel management type | add channel response
            case(States::START_POST_MANAGEMENT):

                $managementType = $this->getUpdate()->getMessage()->getText();

                if ( $managementType == Buttons::MANAGE_POST_QUEUE )
                {
                    $user->updateState(States::POST_MANAGEMENT_TYPE_SAVED, $chatId);
                    $this->chooseChannelResponse($chatId, $messageId, $channels);
                    break;
                }

            case (States::POST_MANAGEMENT_TYPE_SAVED):

                $channelName = $this->getUpdate()->getMessage()->getText();

                if ( in_array($channelName, $channels) )
                {
                    $channelId = $channel->findIdByUsername($channelName);
                    $posts = $post->getQueue($chatId, $channelId);
                    $postIds = $post->getQueueIds($chatId, $channelId);

                    if ( ! empty($posts) )
                    {
                        //start process response(please wait response)
                        $botResponse = [];
                        $botResponse['chat_id'] = $chatId;
                        $botResponse['text'] = "این عملیات ممکن است زمانبر باشد، لطفا شکیبا باشید...";
                        Request::sendMessage($botResponse);

                        foreach ($posts as $post)
                        {
                            $text = $this->generatePostInfoText($post);

                            //post info response
                            $botResponse = [];
                            $botResponse['chat_id'] = $chatId;
                            $botResponse['text'] = $text;
                            Request::sendMessage($botResponse);

                            $this->postPreview($chatId, $post);
                        }

                        //end process response
                        $botResponse = [];
                        $botResponse['chat_id'] = $chatId;
                        $botResponse['text'] = "عملیات به پایان رسید.";
                        Request::sendMessage($botResponse);

                        $this->removePostResponse($chatId, $postIds);


                        $user->updateState(States::CHANNEL_SAVED, $chatId);
                    }
                    else
                    {
                        //start process response(please wait response)
                        $botResponse = [];
                        $botResponse['chat_id'] = $chatId;
                        $botResponse['text'] = "شما هیچ پستی در صف انتظار نداری." . "\n" . "میتوانید با دکمه بیخیال به منوی اصلی انتقال پیدا کنید.";
                        Request::sendMessage($botResponse);
                    }

                }
                else
                {
                    $this->chooseChannelFailedResponse($chatId, $messageId);
                }
                break;

            case(States::CHANNEL_SAVED):

                $postId = $this->getUpdate()->getMessage()->getText();

                $result = $post->remove($postId);

                if ( $result != 0 )
                {
                    //success response
                    $botResponse = [];
                    $botResponse['chat_id'] = $chatId;
                    $botResponse['text'] = "پست مورد نظر از صف حذف گردید." . "\n" . "الان به منوی اصلی منتقل میشوید.";
                    Request::sendMessage($botResponse);

                    $this->runStart($user, $chatId);

                }

                break;
        }


    }

    public function generatePostInfoText($post)
    {
        $text = "➖➖➖➖➖‏➖➖➖➖" . "\n";

        $text .= "شماره: " . $post['id'] . "\n";

        $text .= "زمان: " . $post['hour'] . ":" . $post['minute'] . "\n";

        $text .= "تاریخ: " . $post['year'] . "/" . $post['month'] . "/" . $post['date'] . "\n";

        $text .= "وضعیت: " . "در صف انتظار" . "\n\n";

        $text .= "👇👇👇👇👇👇👇👇";

        return $text;
    }

    public function postPreview($chatId, $post)
    {
        switch ($post['type'])
        {
            case ('context'):
                $this->contextPostPreviewResponse($chatId, $post);
                break;

            case ('photo'):
                $this->photoPostPreviewResponse($chatId, $post);
                break;

            case ('document'):
            case ('gif'):
                $this->gifPostPreviewResponse($chatId, $post);
                break;

            case ('video'):
                $this->videoPostPreviewResponse($chatId, $post);
                break;
        }
    }
}