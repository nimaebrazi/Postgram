<?php

namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Commands\Entities;

use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\States;

use MohandesPlusBot\Handler\MessageHandler;

use MohandesPlusBot\Model\User;

class ChannelManagementMenuCommand extends MessageHandler
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
        $this->name = Command::CHANNEL_MANAGEMENT_MENU;
        $this->description = Command::CHANNEL_MANAGEMENT_MENU_DESC;
        $this->usage = Command::CHANNEL_MANAGEMENT_MENU_USAGE;
        $this->version = Command::CHANNEL_MANAGEMENT_MENU_VERSION;
        $this->enabled = true;
        $this->public = true;
        $this->need_mysql = false;
    }

    /**
     * Execute command
     *
     * @return Entities\ServerResponse
     */
    public function execute()
    {

        /**
         * user info
         *
         */
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
        $userId = $this->getUpdate()->getMessage()->getFrom()->getId();

        $user = new User();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());

        if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        if ( $user->getStateByChatId($chatId) == States::CHANNEL_MANAGEMENT )
        {
            switch ($this->getUpdate()->getMessage()->getText())
            {

                case (Buttons::CHANNEL_MANAGEMENT):

                    $this->channelsManagementMenuResponse($chatId);
                    break;

                case (Buttons::ADD_CHANNEL):

                    $this->runAddChannel($user, $chatId);
                    break;

                case (Buttons::REMOVE_CHANNEL):

                    $this->runRemoveChannel($user, $chatId);
                    break;

                case (Buttons::MY_CHANNELS):

                    $user->updateState(States::ADMIN_MANAGEMENT, $chatId);
                    $this->conversation->stop();
                    $this->telegram->executeCommand(Command::MANAGE_ADMINS);
                    break;
//                    if ( ! empty($user->hasChannels()) )
//                    {
//
//                    }
//                    else
//                    {
//                        $this->notFoundChannelResponse($chatId);
//                        $this->runStart($user, $chatId);
//                    }
//                    break;


            }
        }
    }
}