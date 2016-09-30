<?php

namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;

use MohandesPlusBot\Enums\Messages;
use MohandesPlusBot\Enums\States;
use MohandesPlusBot\Handler\KeyboardHandler;
use MohandesPlusBot\Handler\MessageHandler;

use MohandesPlusBot\Model\User;

class ManagementToolsMenuCommand extends MessageHandler
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
        $this->name = Command::MANAGEMENT_TOOLS_MENU;
        $this->description = Command::MANAGEMENT_TOOLS_MENU_DESC;
        $this->usage = Command::MANAGEMENT_TOOLS_MENU_USAGE;
        $this->version = Command::MANAGEMENT_TOOLS_MENU_VERSION;
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
         * contact info
         *
         */
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
        $userId = $this->getUpdate()->getMessage()->getFrom()->getId();

        /**
         * All Objects
         *
         */
        $user = new User();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());

        if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }



        if ( $user->getStateByChatId($chatId) == States::MANAGEMENT_TOOLS )
        {
            switch ($this->getUpdate()->getMessage()->getText())
            {

                case (Buttons::MANAGEMENT_TOOLS):

                    $this->managementToolsMenuResponse($chatId);
                    break;

                //Run channel management command
                case (Buttons::CHANNEL_MANAGEMENT):

                    $this->runChannelManagement($user, $chatId);
                    break;

                //Run send photo command
                case(Buttons::MANAGE_POST_QUEUE):

                    $this->runPostManagement($user, $chatId);
                    break;

                case (Buttons::BACK):

                    $this->runStart($user, $chatId);
                    break;
            }
        }
    }
}