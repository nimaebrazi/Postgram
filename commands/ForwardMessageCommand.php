<?php


namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\MessageEntity;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\Messages;
use MohandesPlusBot\Enums\States;

use MohandesPlusBot\Handler\KeyboardHandler;
use MohandesPlusBot\Handler\MessageHandler;

use MohandesPlusBot\Model\Channel;
use MohandesPlusBot\Model\Post;
use MohandesPlusBot\Model\User;

use MohandesPlusBot\utils\Time;
use MohandesPlusBot\utils\Translator\ButtonTranslator;
use MohandesPlusBot\utils\Translator\NumberTranslator;
use MohandesPlusBot\utils\Validator\Validator;

class ForwardMessageCommand extends MessageHandler
{

    protected $name = Command::FORWARD_POST;
    protected $description = Command::FORWARD_POST_DESC;
    protected $usage = Command::FORWARD_POST_USAGE;
    protected $version = Command::FORWARD_POST_VERSION;
    protected $enabled = true;
    protected $public = true;
    protected $message;

    protected $conversation;
    protected $telegram;
    protected $userResponse;

    public function __construct(Telegram $telegram, $update)
    {
        parent::__construct($telegram, $update);
        $this->telegram = $telegram;
    }

    public function execute()
    {

        /**
         * user info
         *
         */
        $messageId = $this->getUpdate()->getMessage()->getMessageId();
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
        $userId = $this->getUpdate()->getMessage()->getFrom()->getId();
        $this->userResponse = $this->getUpdate()->getMessage()->getText();


        /**
         * All Objects
         *
         */
        $user = new User();
        $time = new Time();
        $post = new Post();
        $channel = new Channel();
        $validator = new Validator();
        $translator = new NumberTranslator();
        $keyboardHandler = new KeyboardHandler();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());

        if ( $this->userResponse == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        if ( States::START_FORWARD_POST == $user->getStateByChatId($chatId) )
        {
            $post->insertChatId($chatId);
        }

        $postData = (int)$post->getPostId($chatId);
        $post->updatePost($postData, "user_id", $user->findIdByChatId($chatId));
        $channels = $user->hasChannels($chatId);


        switch ($user->getStateByChatId($chatId))
        {
            case (States::START_FORWARD_POST):


                $postType = $this->getUpdate()->getMessage()->getText();

                if ( $postType != null && $postType != "" )
                {
                    $post->updatePost($postData, "type", ButtonTranslator::englishTranslate($postType));
                    $post->updatePost($postData, "create_at", date("Y-m-d H:i:s"));
                    $user->updateState(States::TYPE_SAVED, $chatId);
                    $this->chooseChannelResponse($chatId, $messageId, $channels);
                }

//                    else
//                    {
//
//                    }
                break;


            case (States::TYPE_SAVED):

                $channelUserName = $this->getUpdate()->getMessage()->getText();

                if ( $channelUserName != null && $channelUserName != "" && in_array($channelUserName, $channels) )
                {

                    $post->updatePost($postData, "channel_id", $channel->findIdByUsername($channelUserName));
                    $user->updateState(States::CHANNEL_SAVED, $chatId);

                    //forward post response
                    $botResponse = [];
                    $botResponse['chat_id'] = $chatId;
                    $botResponse['reply_to_message_id'] = $messageId;
                    $botResponse['text'] = Messages::FORWARD_POST;
                    $keyboard = $keyboardHandler->getKeyboard('backAndCancel');
                    $botResponse['reply_markup'] = $keyboardHandler->makeReplyKeyboardMarkupInstance($keyboard);
                    Request::sendMessage($botResponse);
                }
                else
                {
                    $this->chooseChannelFailedResponse($chatId, $messageId);
                }
                break;


            case (States::CHANNEL_SAVED):

                $postInfo = $this->getPostInfo($this->getUpdate()->getMessage());

                echo "POST INFO: " . PHP_EOL;
                print_r($postInfo);
                //update our post row on DB
                $post->updatePost($postData, "type", $postInfo['type']);
                if ( $postInfo['type'] == 'context' )
                {
                    $post->updatePost($postData, "content", $postInfo['content']);
                }
                elseif ( $postInfo['type'] != 'context' )
                {
                    $post->updatePost($postData, "file_id", $postInfo["file_id"]);
                    $post->updatePost($postData, "content", $this->removeEmoji($postInfo["caption"]));
//                    $post->updatePost($postData, "content", (string)$postInfo['caption']);
                }

                $user->updateState(States::POST_INFO_SAVED, $chatId);
                $this->saveYearResponse($messageId, $chatId);
                break;


            /**
             * save post year
             */
            case(States::POST_INFO_SAVED):

                if ( $validator->isYear($translator->toEnglishNumber($this->userResponse)) )
                {
                    $post->updatePost($postData, "year", $translator->toEnglishNumber($this->userResponse));
                    $user->updateState(States::YEAR_SAVED, $chatId);
                    $this->saveMonthResponse($messageId, $chatId);
                }
                else
                {
                    $this->chooseYearFailedResponse($chatId, $messageId);
                }

                break;


            /**
             * save post month
             */
            case(States::YEAR_SAVED):

                $userResponse = $translator->toEnglishNumber($this->userResponse);

                if ( $validator->isMonth($userResponse) )
                {
                    if ( $post->getTime($postData)['year'] == $time->getPresentYear() )
                    {
                        if ( $userResponse >= $time->getPresentMonth() )
                        {
                            $post->updatePost($postData, "month", $userResponse);
                            $user->updateState(States::MONTH_SAVED, $chatId);
                            $this->saveDateResponse($messageId, $chatId);
                        }
                        else
                        {
                            $this->chooseMonthFailedResponse($chatId, $messageId);
                        }
                    }

                    elseif ( $post->getTime($postData)['year'] > $time->getPresentYear() )
                    {
                        $post->updatePost($postData, "month", $userResponse);
                        $user->updateState(States::MONTH_SAVED, $chatId);
                        $this->saveDateResponse($messageId, $chatId);
                    }

                }
                else
                {
                    $this->chooseMonthFailedResponse($chatId, $messageId);
                }
                break;


            /**
             * save post date
             */
            case(States::MONTH_SAVED):

                $userResponse = $translator->toEnglishNumber($this->userResponse);

                if ( $validator->isDate($userResponse) )
                {
                    if ( $post->getTime($postData)['year'] == $time->getPresentYear()
                        && $post->getTime($postData)['month'] == $time->getPresentMonth()
                    )

                    {
                        if ( $userResponse >= $time->getPresentDate() )
                        {
                            $post->updatePost($postData, "date", $userResponse);
                            $user->updateState(States::DATE_SAVED, $chatId);
                            $this->saveHourResponse($messageId, $chatId);
                        }
                        else
                        {
                            $this->chooseDateFailedResponse($chatId, $messageId);
                        }


                    }
                    else
                    {
                        $post->updatePost($postData, "date", $userResponse);
                        $user->updateState(States::DATE_SAVED, $chatId);
                        $this->saveHourResponse($messageId, $chatId);
                    }
                }
                else
                {
                    $this->chooseDateFailedResponse($chatId, $messageId);
                }

                break;

            /**
             * save post hour
             */
            case
            (States::DATE_SAVED):

                $userResponse = $translator->toEnglishNumber($this->userResponse);

                if ( $validator->isHour($userResponse) )
                {
                    if ( $post->getTime($postData)['year'] == $time->getPresentYear()
                        && $post->getTime($postData)['month'] == $time->getPresentMonth()
                        && $post->getTime($postData)['date'] == $time->getPresentDate()
                    )
                    {

                        if ( $userResponse >= $time->getPresentHour() )
                        {
                            $post->updatePost($postData, "hour", $userResponse);
                            $user->updateState(States::HOUR_SAVED, $chatId);
                            $this->saveMinuteResponse($messageId, $chatId);
                        }
                        else
                        {
                            $this->chooseHourFailedResponse($chatId, $messageId);
                        }


                    }

                    else
                    {
                        $post->updatePost($postData, "hour", $userResponse);
                        $user->updateState(States::HOUR_SAVED, $chatId);
                        $this->saveMinuteResponse($messageId, $chatId);
                    }

                }
                else
                {
                    $this->chooseHourFailedResponse($chatId, $messageId);
                }
                break;


            /**
             * save post minute
             */
            case(States::HOUR_SAVED):

                $userResponse = $translator->toEnglishNumber($this->userResponse);

                if ( $validator->isMinute($userResponse) )
                {
                    if ( $post->getTime($postData)['year'] == $time->getPresentYear()
                        && $post->getTime($postData)['month'] == $time->getPresentMonth()
                        && $post->getTime($postData)['date'] == $time->getPresentDate()
                        && $post->getTime($postData)['hour'] == $time->getPresentHour()
                    )
                    {
                        if ( $userResponse >= $time->getPresentMinute() )
                        {
                            $post->updatePost($postData, "minute", $userResponse);
                            $user->updateState(States::MINUTE_SAVED, $chatId);

                            $this->postPreview(
                                $chatId,
                                $post->getPostContent($postData)
                            );

                            $this->confirmationResponse($chatId);
                        }
                        else
                        {
                            $this->chooseMinuteFailedResponse($chatId, $messageId);
                        }
                    }
                    else
                    {
                        $post->updatePost($postData, "minute", $userResponse);
                        $user->updateState(States::MINUTE_SAVED, $chatId);
                        $this->postPreview(
                            $chatId,
                            $post->getPostContent($postData)
                        );
                        $this->confirmationResponse($chatId);
                    }
                }
                else
                {
                    $this->chooseMinuteFailedResponse($chatId, $messageId);
                }
                break;

            /**
             * user confirmation
             */
            case(States::MINUTE_SAVED):

                if ( $this->userResponse == Buttons::DONE_AND_SEND )
                {
                    $post->updatePost($postData, "is_accepted", 'true');
                    $post->updatePost($postData, "sent", 'false');
                    $user->updateState(States::IS_ACCEPTED, $chatId);

                    $this->showTimeSendingResponse($chatId, $post->getTime($postData));

                    $this->runStart($user, $chatId);

                }
                else
                {
                    $this->confirmationResponseFailed($chatId, $messageId);
                }

                break;
        }

    }


    /**
     * Get all forwarded post info (caption, text, file_id, type) and return in an array.
     *
     * @param $forwardPost
     *
     * @return array
     */
    public function getPostInfo($forwardPost)
    {

        $post = [];

        $message = $forwardPost;

        if ( $message->getText() != null )
        {
            $post['content'] = $message->getText();
            $post['type'] = 'context';

            return $post;
        }

        elseif ( $message->getPhoto() != null )
        {
            if ( $message->getCaption() != null )
            {
                $post['caption'] = (string)$message->getCaption();
            }
            $mediumPhotoSize = (count($message->getPhoto())) - 1;
            $post['file_id'] = $message->getPhoto()[$mediumPhotoSize]->getFileId();
            $post['type'] = 'photo';

            return $post;
        }

        elseif ( $message->getVideo() != null )
        {
            if ( $message->getCaption() != null )
            {
                $post['caption'] = (string)$message->getCaption();
            }
            $post['file_id'] = $message->getVideo()->getFileId();
            $post['type'] = 'video';

            return $post;
        }

        elseif ( $message->getDocument() != null )
        {
            if ( $message->getCaption() != null )
            {
                $post['caption'] = (string)$message->getCaption();
            }
            $post['file_id'] = $message->getDocument()->getFileId();
            $post['type'] = 'document';

            return $post;
        }


    }


    public function postPreview($chatId, $postData)
    {
        switch ($postData['type'])
        {
            case ('context'):

                $this->contextPostPreviewResponse($chatId, $postData);
                break;

            case ('photo'):

                $this->photoPostPreviewResponse($chatId, $postData);
                break;

            case ('document'):
            case ('gif'):

                $this->gifPostPreviewResponse($chatId, $postData);
                break;

            case ('video'):

                $this->videoPostPreviewResponse($chatId, $postData);
                break;
        }
    }
}