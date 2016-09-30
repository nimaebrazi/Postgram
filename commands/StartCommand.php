<?php


namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\States;

use MohandesPlusBot\Handler\MessageHandler;
use MohandesPlusBot\Model\User;

/**
 * Start command
 */
class StartCommand extends MessageHandler
{

    /**#@+
     * {@inheritdoc}
     */
    protected $name;
    protected $description;
    protected $usage;
    protected $version;
    protected $enabled;
    protected $public;
    protected $message;
    /**#@-*/

    protected $conversation;
    protected $telegram;

    public function __construct(Telegram $telegram, $update)
    {
        parent::__construct($telegram, $update);
        $this->telegram = $telegram;

        $this->name = Command::START;
        $this->description = Command::START_DESC;
        $this->usage = Command::START_USAGE;
        $this->version = Command::START_VERSION;
        $this->enabled = true;
        $this->public = true;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {

        /**
         * contact info
         *
         */
        $messageId = $this->getUpdate()->getMessage()->getMessageId();
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
        $userId = $this->getUpdate()->getMessage()->getFrom()->getId();
        $telegramFirstName = $this->getUpdate()->getMessage()->getFrom()->getFirstName();
        $telegramLastName = $this->getUpdate()->getMessage()->getFrom()->getLastName();
        $telegramUserName = $this->getUpdate()->getMessage()->getFrom()->getUsername();

        /**
         * All Objects
         *
         */
        $user = new User();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());


        //After enter every one we check is he on our db or nor.
        $this->studyUserInfo($user, $chatId, $telegramUserName, $telegramFirstName, $telegramLastName, null);


        if ( $user->getStateByChatId($chatId) == States::JUST_STARTED || $user->getStateByChatId($chatId) == States::BACK_TO_START )
        {
            switch ($this->getUpdate()->getMessage()->getText())
            {
                case(Command::START_USAGE):
                case(Buttons::DONE_AND_SEND):
                case (Buttons::BACK):
                case (Buttons::DONE):
                case (Buttons::YES):
                case(Buttons::CANCEL):
                case (is_numeric($this->getUpdate()->getMessage()->getText())):

                    $this->mainMenuResponse($chatId);
                    break;

                case (Buttons::ADD_POST):

                    $user->updateState(States::ADD_POST, $chatId);
                    $this->conversation->stop();
                    $this->telegram->executeCommand(Command::POST_MENU);
                    break;

                case(Buttons::MANAGEMENT_TOOLS):

                    $user->updateState(States::MANAGEMENT_TOOLS, $chatId);
                    $this->conversation->stop();
                    $this->telegram->executeCommand(Command::MANAGEMENT_TOOLS_MENU);
                    break;

                case(Buttons::HELP):

                    $this->telegram->executeCommand(Command::HELP);
                    break;

                case(Buttons::CONTACT_US):

                    $this->telegram->executeCommand(Command::CONTACT);
                    break;


            }
        }

    }
}