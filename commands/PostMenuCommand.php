<?php

namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Commands\Entities;
use Longman\TelegramBot\Conversation;

use MohandesPlusBot\Enums\States;
use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\Messages;

use MohandesPlusBot\Handler\MessageHandler;
use MohandesPlusBot\Handler\KeyboardHandler;

use MohandesPlusBot\Model\User;

class PostMenuCommand extends MessageHandler
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
        $this->name = Command::POST_MENU;
        $this->description = Command::POST_MENU_DESC;
        $this->usage = Command::POST_MENU_USAGE;
        $this->version = Command::POST_MENU_VERSION;
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

        $user = new User();
        $keyboardHandler = new KeyboardHandler();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());

        if ( $user->getStateByChatId($chatId) == States::ADD_POST )
        {

            switch ($this->getUpdate()->getMessage()->getText())
            {
                //first time visit this depth
                case (Buttons::ADD_POST):

                    $this->choosePostTypeResponse($chatId, $keyboardHandler);
                    break;

                //Run send text command
                case (Buttons::CONTEXT):

                    $this->runSendText($user, $chatId);
                    break;

                //Run send photo command
                case(Buttons::PICTURE_AND_CONTEXT):

                    $this->runSendPhoto($user, $chatId);
                    break;

                //Run send video command
                case(Buttons::VIDEO_AND_CONTEXT):

                    $this->runSendVideo($user, $chatId);
                    break;

                //Run send gif command
                case(Buttons::GIF_AND_CONTEXT):

                    $this->runSendGif($user, $chatId);
                    break;

                case(Buttons::FORWARD_POST):

                    $this->runForwardPost($user, $chatId);
                    break;

                //back button functionality
                //run old state
                case (Buttons::BACK):

                    $this->runStart($user, $chatId);
                    break;

            }
        }

    }

    /**
     * @param $chatId
     * @param $keyboardHandler
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function choosePostTypeResponse($chatId, $keyboardHandler)
    {
        $botResponse = [];
        $botResponse['chat_id'] = $chatId;
        $botResponse['text'] = Messages::POST_TYPE;
        $keyboard = $keyboardHandler->getKeyboard('postType');
        $botResponse['reply_markup'] = $keyboardHandler->makeReplyKeyboardMarkupInstance($keyboard);
        Request::sendChatAction(['chat_id' => $botResponse['chat_id'], 'action' => 'typing']);
        Request::sendMessage($botResponse);
    }
}