<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class TestCommand extends UserCommand {

    protected $name = 'test';                      //your command's name
    protected $description = 'A command for test'; //Your command description
    protected $usage = '/test';                    // Usage of your command
    protected $version = '1.0.0';
    protected $enabled = true;
    protected $public = true;
    protected $message;

    public function execute() {

        $message = $this->getMessage();              // get Message info

        $chat_id = $message->getChat()->getId();     //Get Chat Id
        $message_id = $message->getMessageId();      //Get message Id
//        $text = $message->getText(true);           // Get received text

        $data = array();                             // prepare $data
        $data['chat_id'] = $chat_id;                 //set chat Id
        $data['reply_to_message_id'] = $message_id;  //set message Id
        $data['text'] = 'This is just a Test...';    //set reply message

        $result = Request::sendMessage($data);       //send message
        return $result;

    }

}