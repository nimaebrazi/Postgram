<?php


namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\States;
use MohandesPlusBot\Handler\KeyboardHandler;
use MohandesPlusBot\Handler\MessageHandler;
use MohandesPlusBot\Model\Channel;
use MohandesPlusBot\Model\ChannelUser;
use MohandesPlusBot\Model\RemoveAdmins;
use MohandesPlusBot\Model\User;

class RemoveAdminCommand extends MessageHandler
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
        $this->name = Command::REMOVE_ADMIN;
        $this->description = Command::REMOVE_ADMIN_DESC;
        $this->usage = Command::REMOVE_ADMIN_USAGE;
        $this->version = Command::REMOVE_ADMIN_VERSION;
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
        $channel = new Channel();
        $removeAdmin = new RemoveAdmins();
        $keyboardHandler = new KeyboardHandler();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());

        $channels = $user->hasChannels($chatId);
        $admins = $user->getAdmins($chatId);

        if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        if ( States::START_REMOVE_ADMIN == $user->getStateByChatId($chatId) )
        {
            $removeAdmin->insertChatId($chatId);
        }

        $removeAdminLastId = $removeAdmin->getLastId($chatId);


        switch ($user->getStateByChatId($chatId))
        {
            case(States::START_REMOVE_ADMIN):

                $adminManagementType = $this->getUpdate()->getMessage()->getText();

                if ( $adminManagementType == Buttons::REMOVE_ADMIN )
                {
                    $user->updateState(States::ADMIN_MANAGEMENT_TYPE_SAVED, $chatId);
                    $this->chooseChannelResponse($chatId, $messageId, $channels);
                }
                else
                {
                    //failed
                }

                break;

            case (States::ADMIN_MANAGEMENT_TYPE_SAVED):

                $channelName = $this->getUpdate()->getMessage()->getText();

                if ( in_array($channelName, $channels) )
                {

                    $removeAdmin->updateLastId(
                        $removeAdminLastId,
                        "channel_id",
                        $channel->findIdByUsername($channelName)
                    );

                    $user->updateState(States::CHANNEL_SAVED, $chatId);

                    $this->removeAdminResponse($chatId, $messageId, $admins);
                }
                else
                {
                    $this->chooseChannelFailedResponse($chatId, $messageId);
                }

                break;


            case (States::CHANNEL_SAVED):

                $admin = $this->getUpdate()->getMessage()->getText();

                if ( in_array($admin, $admins) )
                {

                    $adminInfo = $user->findByUsername($admin);

                    $removeAdmin->updateLastId(
                        $removeAdminLastId,
                        "admin_id",
                        $adminInfo['id']
                    );

                    $removeAdmin->updateLastId(
                        $removeAdminLastId,
                        'added_by',
                        $adminInfo['added_by']
                    );

                    $removeAdmin->updateLastId(
                        $removeAdminLastId,
                        'removed_by',
                        $user->findIdByChatId($chatId)
                    );


                    $user->updateState(States::REMOVE_ADMIN_SAVED, $chatId);

                    $botResponse = [];
                    $botResponse['text'] = "آیا از حذف ادمین مطمئن هستید؟" . "\n" . "@" . $admin;
                    $botResponse['chat_id'] = $chatId;
                    $botResponse['reply_markup'] = $keyboardHandler->makeReplyKeyboardMarkupInstance([[Buttons::YES]]);
                    Request::sendMessage($botResponse);

                }
                else
                {
                    //filed
                }


                break;


            case (States::REMOVE_ADMIN_SAVED):

                if ( $this->getUpdate()->getMessage()->getText() == Buttons::YES )
                {
                    $removeAdminLastRow = $removeAdmin->getLastRowInfo($removeAdminLastId);

                    $result = $user->remove(
                        $removeAdminLastRow['added_by'],
                        $removeAdminLastRow['channel_id']
                    );


                    if ( $result > 0 )
                    {
                        $botResponse['chat_id'] = $chatId;
                        $botResponse['text'] = "ادمین با موفیت حذف شد.";
                        Request::sendMessage($botResponse);
                        $this->runStart($user, $chatId);
                    }
                    else
                    {
                        $botResponse['chat_id'] = $chatId;
                        $botResponse['text'] = "عملیات ناموفق بود. مراحل را به درستی طی نمایید.";
                        Request::sendMessage($botResponse);
                        $this->runRemoveAdminCommand($user, $chatId);
                    }

                }

                else
                {
                    $botResponse['chat_id'] = $chatId;
                    $botResponse['text'] = "هنوز تایید نشده است.";
                    Request::sendMessage($botResponse);
                }

                break;
        }
    }
}