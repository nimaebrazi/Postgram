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
use MohandesPlusBot\Model\AddAdmins;
use MohandesPlusBot\Model\Channel;
use MohandesPlusBot\Model\ChannelUser;
use MohandesPlusBot\Model\User;

class AddAdminCommand extends MessageHandler
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
        $this->name = Command::ADD_ADMIN;
        $this->description = Command::ADD_ADMIN_DESC;
        $this->usage = Command::ADD_ADMIN_USAGE;
        $this->version = Command::ADD_ADMIN_VERSION;
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
        $messageId = $this->getUpdate()->getMessage()->getMessageId();
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();
        $userId = $this->getUpdate()->getMessage()->getFrom()->getId();


        $user = new User();
        $channel = new Channel();
        $addAdmin = new AddAdmins();
        $channelUser = new ChannelUser();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());

        $channels = $user->hasChannels($chatId);


        if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        if ( States::START_ADD_ADMIN == $user->getStateByChatId($chatId) )
        {
            $addAdmin->insertChatId($chatId);
        }

        $addAdminLastId = $addAdmin->getLastId($chatId);


        switch ($user->getStateByChatId($chatId))
        {
            case(States::START_ADD_ADMIN):

                $adminManagementType = $this->getUpdate()->getMessage()->getText();

                if ( $adminManagementType == Buttons::ADD_ADMIN )
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

                    $addAdmin->updateLastId(

                        $addAdminLastId,
                        "channel_id",
                        $channel->findIdByUsername($channelName)

                    );

                    $user->updateState(States::CHANNEL_SAVED, $chatId);

                    $this->addAdminResponse($chatId, $messageId);
                }
                else
                {
                    $this->chooseChannelFailedResponse($chatId, $messageId);
                }

                break;


            case (States::CHANNEL_SAVED):

                //not him-self
                //must be forwarded
                if ( ! empty($this->getUpdate()->getMessage()->getForwardFrom())
                    && ($this->getUpdate()->getMessage()->getForwardFrom()->getId() != $chatId)
                )
                {
                    //check has username or no
                    if ( ! empty($this->getUpdate()->getMessage()->getForwardFrom()->getUserName()) )
                    {
                        //should be merge in one method
                        $addAdmin->updateLastId(

                            $addAdminLastId,
                            "admin_chat_id",
                            $this->getUpdate()->getMessage()->getForwardFrom()->getId()

                        );

                        $addAdmin->updateLastId(

                            $addAdminLastId,
                            "admin_username",
                            $this->getUpdate()->getMessage()->getForwardFrom()->getUserName()

                        );

                        $addAdmin->updateLastId(

                            $addAdminLastId,
                            "admin_first_name",
                            $this->getUpdate()->getMessage()->getForwardFrom()->getFirstName()

                        );

                        $addAdmin->updateLastId(

                            $addAdminLastId,
                            "admin_last_name",
                            $this->getUpdate()->getMessage()->getForwardFrom()->getLastName()

                        );

                        $user->updateState(States::ADMIN_SAVED, $chatId);
                        $this->areYouSureAddAdminResponse($chatId, $messageId, $this->getUpdate()->getMessage()->getForwardFrom()->getUserName());

                    }
                    else
                    {
                        $this->adminMustHaveUsernameResponse($chatId, $messageId);
                    }


                }
                else
                {
                    $this->notForwardPostResponse($chatId, $messageId);
                }

                break;


            case (States::ADMIN_SAVED):

                if ( $this->getUpdate()->getMessage()->getText() == Buttons::YES )
                {
                    $addAdminLastRow = $addAdmin->getLastRowInfo($addAdminLastId);

                    $userIdDB = $this->studyUserInfo(
                        $user,
                        $addAdminLastRow['admin_chat_id'],
                        $addAdminLastRow['admin_username'],
                        $addAdminLastRow['admin_first_name'],
                        $addAdminLastRow['admin_last_name'],
                        $addAdminLastRow['id']
                    );

                    $channelUser->insert(
                        $userIdDB,
                        $addAdminLastRow['channel_id'],
                        $addAdminLastRow['id']
                    );

                    $this->adminAddedSuccessResponse($chatId);

                    $this->runStart($user, $chatId);
                }

                else
                {
                    $this->notConfirmedResponse($chatId);
                }

                break;
        }
    }
}
