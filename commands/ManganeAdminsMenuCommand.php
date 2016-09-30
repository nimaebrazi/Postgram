<?php
namespace Longman\TelegramBot\Commands\UserCommands
{

    use Longman\TelegramBot\Request;
    use Longman\TelegramBot\Telegram;
    use Longman\TelegramBot\Conversation;
    use Longman\TelegramBot\Entities\ReplyKeyboardHide;

    use MohandesPlusBot\Enums\States;
    use MohandesPlusBot\Enums\Buttons;
    use MohandesPlusBot\Enums\Command;
    use MohandesPlusBot\Enums\Messages;

    use MohandesPlusBot\Handler\MessageHandler;
    use MohandesPlusBot\Handler\KeyboardHandler;

    use MohandesPlusBot\Model\User;

    class ManageAdminsCommand extends MessageHandler
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
            $this->name = Command::MANAGE_ADMINS;
            $this->description = Command::MANAGE_ADMINS_DESC;
            $this->usage = Command::MANAGE_ADMINS_USAGE;
            $this->version = Command::MANAGE_ADMINS_VERSION;
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
            $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
            $userId = $this->getUpdate()->getMessage()->getFrom()->getId();


            $user = new User();
            $this->conversation = new Conversation($userId, $chatId, $this->getName());

            if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
            {
                $this->runStart($user, $chatId);
            }


            if ( $user->getStateByChatId($chatId) == States::ADMIN_MANAGEMENT )
            {
                switch ($this->getUpdate()->getMessage()->getText())
                {
                    case (Buttons::MY_CHANNELS):

                        $this->adminsManagementMenuResponse($chatId);
                        break;

                    case (Buttons::SHOW_ADMINS):

                        $user->updateState(States::START_SHOW_ADMIN, $chatId);
                        $this->conversation->stop();
                        $this->telegram->executeCommand(Command::SHOW_ADMIN);
                        break;

                    case (Buttons::ADD_ADMIN):

                        $user->updateState(States::START_ADD_ADMIN, $chatId);
                        $this->conversation->stop();
                        $this->telegram->executeCommand(Command::ADD_ADMIN);
                        break;

                    case (Buttons::REMOVE_ADMIN):
                        $user->updateState(States::START_REMOVE_ADMIN, $chatId);
                        $this->conversation->stop();
                        $this->telegram->executeCommand(Command::REMOVE_ADMIN);

                }
            }
        }

        /**
         * @param $chatId
         *
         * @throws \Longman\TelegramBot\Exception\TelegramException
         */
        public function notFoundChannelResponse($chatId)
        {
            $botResponse = [];
            $botResponse['chat_id'] = $chatId;
            $botResponse['text'] = Messages::HAVE_NOT_CHANNEL_FOR_MANAGE;
            $botResponse['reply_markup'] = new ReplyKeyboardHide(['selective' => true]);
            Request::sendMessage($botResponse);
        }

        /**
         * @param $messageId
         * @param $keyboardHandler
         *
         * @throws \Longman\TelegramBot\Exception\TelegramException
         */
        public function chooseOptionResponse($messageId, $keyboardHandler)
        {
            $botResponse = [];
            $botResponse['text'] = Messages::CHOOSE_OPTION;
            $botResponse['reply_to_message_id'] = $messageId;
            $keyboard = $keyboardHandler->getKeyboard('manageChannels');
            $botResponse['reply_markup'] = $keyboardHandler->makeReplyKeyboardMarkupInstance($keyboard);
            Request::sendMessage($botResponse);
        }
    }
}