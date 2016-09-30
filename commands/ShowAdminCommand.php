<?php

namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Commands\Entities;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\States;
use MohandesPlusBot\Handler\KeyboardHandler;
use MohandesPlusBot\Handler\MessageHandler;
use MohandesPlusBot\Model\Channel;
use MohandesPlusBot\Model\User;

class ShowAdminCommand extends MessageHandler
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
        $this->name = Command::SHOW_ADMIN;
        $this->description = Command::SHOW_ADMIN_DESC;
        $this->usage = Command::SHOW_ADMIN_USAGE;
        $this->version = Command::SHOW_ADMIN_VERSION;
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


        $user = new User();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());

        $channels = $user->hasChannels($chatId);

        if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        switch ($user->getStateByChatId($chatId))
        {
            case(States::START_SHOW_ADMIN):

                $adminManagementType = $this->getUpdate()->getMessage()->getText();

                if ( $adminManagementType == Buttons::SHOW_ADMINS )
                {
                    $user->updateState(States::ADMIN_MANAGEMENT_TYPE_SAVED, $chatId);
                    $this->chooseChannelResponse($chatId, $messageId, $channels);
                }
                else
                {
                    //failed
                }

                break;

            case(States::ADMIN_MANAGEMENT_TYPE_SAVED):

                $channelName = $this->getUpdate()->getMessage()->getText();

                if ( in_array($channelName, $channels) )
                {

                    $adminIds = $user->findAdminsByChannel($chatId, $channelName);

                    $admins = [];
                    foreach ($adminIds as $adminId)
                    {
                        $admins[] = $user->findUsernameById($adminId);
                    }

                    $adminText = $this->generateAdminText($admins);

                    $botResponse = [];
                    $botResponse['text'] = "ادمین های شما: " . "\n" . $adminText;
                    $botResponse['chat_id'] = $chatId;
                    Request::sendMessage($botResponse);
//                    $this->showAdminsResponse($chatId );
                }
                else
                {
                    //failed
                }

                break;

        }
    }

    protected function generateAdminText($admins)
    {
        $text = "\n";
        foreach ($admins as $admin)
        {
            $text .= '@' . $admin . "\n";
        }

        return $text;
    }
}