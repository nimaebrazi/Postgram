<?php


/**
 * Clear Hard code messages from AddChannelCommand.
 * @author nimaebrazi
 *
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Commands\Entities;

use MohandesPlusBot\Enums\States;
use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;

use MohandesPlusBot\Model\User;
use MohandesPlusBot\Model\Channel;

use MohandesPlusBot\Handler\MessageHandler;


class AddChannelCommand extends MessageHandler
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
        $this->name = Command::ADD_CHANNEL;
        $this->description = Command::ADD_CHANNEL_DESC;
        $this->usage = Command::ADD_CHANNEL_USAGE;
        $this->version = Command::ADD_CHANNEL_VERSION;
        $this->enabled = true;
        $this->public = true;
        $this->need_mysql = false;
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
        $telegramUserName = $this->getUpdate()->getMessage()->getFrom()->getUsername();


        /**
         * All Objects
         *
         */
        $user = new User();
        $channel = new Channel();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());

        //fetch all channels from DB
        $channels = $channel->getAllChannels();

        if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        //check user state
        if ( $user->getStateByChatId($chatId) == States::START_ADD_CHANNEL )
        {
            //check user has user name
            if ( empty($telegramUserName) )
            {
                $this->addUserNameResponse($chatId, $messageId);
            }

        }

        switch ($user->getStateByChatId($chatId))
        {
            //get channel management type | add channel response
            case(States::START_ADD_CHANNEL):

                $channelManagementType = $this->getUpdate()->getMessage()->getText();

                if ( $channelManagementType == Buttons::ADD_CHANNEL )
                {
                    $user->updateState(States::CHANNEL_MANAGEMENT_TYPE_SAVED, $chatId);
                    $this->addChannelResponse($chatId, $messageId);
                    break;
                }

            case (States::CHANNEL_MANAGEMENT_TYPE_SAVED):

                $channelName = $this->getUpdate()->getMessage()->getText();

                if ( $this->isTelegramUrl($channelName) )
                {
                    $channelName = $this->fetchChannelNameFromUrl($channelName);
                }
                //first check channel is exists on DB or no
                if ( ! in_array($channelName, $channels) )
                {
                    //send message to channel insure bot is admin
                    $result = $this->sendTestMessageToChannel($channelName);

                    //check message sent or no
                    if ( $result->isOk() )
                    {
                        $user->updateState(States::CHANNEL_SAVED, $chatId);
                        $channel->saveChannel($channelName, $user->findIdByChatId($chatId));
                        $this->addChannelConfirmationResponse($chatId, $channelName);
                    }
                    else
                    {
                        //bot not admin in channel
                        $this->botNotAdminChannelResponse($chatId, $messageId);
                    }

                }
                else
                {
                    //your channel is exists on DB
                    $this->channelExistsResponse($chatId, $messageId);
                }

                break;


            case (States::CHANNEL_SAVED):

                $isAccept = $this->getUpdate()->getMessage()->getText();

                if ( $isAccept == Buttons::DONE )
                {
                    $this->addChannelSuccessResponse($chatId);
                    $this->runStart($user, $chatId);
                }
                else
                {
                    //failed
                }
                break;


        }

    }

    public function isTelegramUrl($input)
    {
        $pattern = '/(^http\w?)(\W{1,3})(telegram.me\/)/';

        if ( preg_match($pattern, $input) )
        {
            return true;
        }

        return false;
    }

    public function fetchChannelNameFromUrl($input)
    {
        $pattern = '/(^http\w?)(\W{1,3})(telegram.me\/)/';
        $result = trim(preg_replace($pattern, "", $input));

        return $result;
    }
}