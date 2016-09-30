<?php


namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ReplyKeyboardHide;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\Messages;
use MohandesPlusBot\Enums\States;
use MohandesPlusBot\Handler\KeyboardHandler;
use MohandesPlusBot\Handler\MessageHandler;
use MohandesPlusBot\Model\Channel;
use MohandesPlusBot\Model\User;

class RemoveChannelCommand extends MessageHandler
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

        $this->name = Command::REMOVE_CHANNEL;
        $this->description = Command::REMOVE_CHANNEL_DESC;
        $this->usage = Command::REMOVE_CHANNEL_USAGE;
        $this->version = Command::REMOVE_CHANNEL_VERSION;
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
        $telegramUserName = $this->getUpdate()->getMessage()->getFrom()->getUsername();


        /**
         * All Objects
         *
         */
        $user = new User();
        $channel = new Channel();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());

        //fetch all channels for specific user
        $channels = $user->hasChannels($chatId);

        if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        if ( $user->getStateByChatId($chatId) == States::START_REMOVE_CHANNEL )
        {
            if ( empty($channels) )
            {
                $this->notFoundChannelResponse($chatId, $messageId);
            }
            //check user has user name
            if ( empty($telegramUserName) )
            {
                $this->addUserNameResponse($chatId, $messageId);
            }

        }

        switch ($user->getStateByChatId($chatId))
        {
            case (States::START_REMOVE_CHANNEL):

                $channelManagementType = $this->getUpdate()->getMessage()->getText();

                if ( $channelManagementType == Buttons::REMOVE_CHANNEL )
                {
                    $user->updateState(States::CHANNEL_MANAGEMENT_TYPE_SAVED, $chatId);
                    $this->chooseChannelResponse($chatId, $messageId, $channels);
                }
                break;


            case (States::CHANNEL_MANAGEMENT_TYPE_SAVED):

                $channelName = $this->getUpdate()->getMessage()->getText();

                if ( in_array($channelName, $channels) )
                {
                    $channel->removeChannel($channelName, $chatId);
                    $user->updateState(States::REMOVE_CHANNEL_CONFIRMATION, $chatId);
                    $this->removeChannelConfirmationResponse($chatId, $channelName);
                }

                else
                {
                    $this->removeChannelFailedResponse($chatId);
                }
                break;

            case (States::REMOVE_CHANNEL_CONFIRMATION):

                $isAccepted = $this->getUpdate()->getMessage()->getText();

                if ( $isAccepted == Buttons::DONE )
                {
                    $this->removeChannelSuccessResponse($chatId);
                    $this->runStart($user, $chatId);
                }
                else
                {
                    //failed
                }
                break;


        }

    }
}
