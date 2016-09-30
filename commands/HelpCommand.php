<?php


namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Commands\Entities;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\Messages;
use MohandesPlusBot\Handler\MessageHandler;

class HelpCommand extends MessageHandler
{
    protected $name;
    protected $description;
    protected $usage;
    protected $version;
    protected $enabled;
    protected $need_mysql;
    protected $message;
    protected $public;

    protected $conversation;
    protected $telegram;
    protected $userResponse;

    public function __construct(Telegram $telegram, $update)
    {
        parent::__construct($telegram, $update);
        $this->telegram = $telegram;
        $this->name = Command::HELP;
        $this->description = Command::HELP_DESC;
        $this->usage = Command::HELP_USAGE;
        $this->version = Command::HELP_VERSION;
        $this->enabled = true;
        $this->public = true;
        $this->need_mysql = false;
    }


    public function execute()
    {

        echo "Help command" . PHP_EOL;
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
        $userId = $this->getUpdate()->getMessage()->getFrom()->getId();

        $botResponse = [];
        $botResponse['text'] = Messages::HELP;
        $botResponse['chat_id'] = $chatId;
        Request::sendMessage($botResponse);
    }
}