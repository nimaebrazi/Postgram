<?php

namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Telegram;

use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\States;

use MohandesPlusBot\Handler\DataPackage;
use MohandesPlusBot\Handler\MessageHandler;

use MohandesPlusBot\Model\Channel;
use MohandesPlusBot\Model\Post;
use MohandesPlusBot\Model\User;

use MohandesPlusBot\utils\Translator\ButtonTranslator;
use MohandesPlusBot\utils\Translator\NumberTranslator;
use MohandesPlusBot\utils\Time;
use MohandesPlusBot\utils\Validator\Validator;

class SendTextCommand extends MessageHandler
{

    protected $name;
    protected $description;
    protected $usage;
    protected $version;
    protected $enabled;
    protected $need_mysql;
    protected $message;
    protected $public;
    protected $userResponse;
    protected $conversation;
    protected $telegram;
    protected $state;

    public function __construct(Telegram $telegram, $update)
    {
        parent::__construct($telegram, $update);
        $this->telegram = $telegram;
        $this->name = Command::SEND_TEXT;
        $this->description = Command::SEND_TEXT_DESC;
        $this->usage = Command::SEND_TEXT_USAGE;
        $this->version = Command::SEND_TEXT_VERSION;
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
        $this->userResponse = $this->getUpdate()->getMessage()->getText();

        /**
         * All Objects
         *
         */

        $user = new User();
        $post = new Post();
        $time = new Time();
        $channel = new Channel();
        $validator = new Validator();
        $translator = new NumberTranslator();
        $this->conversation = new Conversation($userId, $chatId, $this->getName());


        if ( $this->userResponse == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        $this->state = $user->getStateByChatId($chatId);

        if ( States::START_SEND_CONTEXT == $this->state )
        {
            $post->insertChatId($chatId);
        }

        $postData = (int)$post->getPostId($chatId);
        $post->updatePost($postData, "user_id", $user->findIdByChatId($chatId));
        $channels = $user->hasChannels($chatId);


        /**
         * START Interact bot-user block
         *
         */

        switch ($this->state)
        {

            /**
             * save post type
             */
            case (States::START_SEND_CONTEXT):

                $post->updatePost($postData, "type", ButtonTranslator::englishTranslate($this->userResponse));
                $post->updatePost($postData, "create_at", date("Y-m-d H:i:s"));
                $user->updateState(States::TYPE_SAVED, $chatId);
                $this->chooseChannelResponse($chatId, $messageId, $channels);

//            else
//            {
//            }
                break;

            /**
             * save post channels
             */
            case (States::TYPE_SAVED):

                if ( in_array($this->userResponse, $channels) )
                {
                    $post->updatePost($postData, "channel_id", $channel->findIdByUsername($this->userResponse));
                    $user->updateState(States::CHANNEL_SAVED, $chatId);

                    $this->sendPostContextResponse($chatId, $messageId);
                }

                else
                {
                    $this->chooseChannelFailedResponse($chatId, $messageId);
                }

                break;


            /**
             * save post content
             */
            case(States::CHANNEL_SAVED):

                if ( strlen($this->userResponse) < 3378 )
                {
                    $post->updatePost($postData, "content", $this->userResponse);
                    $user->updateState(States::CONTEXT_SAVED, $chatId);
                    $this->saveYearResponse($messageId, $chatId);
                }
                else
                {
                    $this->sendPostContextFailedResponse($messageId, $chatId);
                }

                break;


            /**
             * save post year
             */
            case(States::CONTEXT_SAVED):

                if ( $validator->isYear($translator->toEnglishNumber($this->userResponse)) )
                {
                    $post->updatePost($postData, "year", $translator->toEnglishNumber($this->userResponse));
                    $user->updateState(States::YEAR_SAVED, $chatId);
                    $this->saveMonthResponse($messageId, $chatId);
                }
                else
                {
                    $this->chooseYearFailedResponse($chatId, $messageId);
                }

                break;


            /**
             * save post month
             */
            case(States::YEAR_SAVED):

                $userResponse = $translator->toEnglishNumber($this->userResponse);

                if ( $validator->isMonth($userResponse) )
                {
                    if ( $post->getTime($postData)['year'] == $time->getPresentYear() )
                    {
                        if ( $userResponse >= $time->getPresentMonth() )
                        {
                            $post->updatePost($postData, "month", $userResponse);
                            $user->updateState(States::MONTH_SAVED, $chatId);
                            $this->saveDateResponse($messageId, $chatId);
                        }
                        else
                        {
                            $this->chooseMonthFailedResponse($chatId, $messageId);
                        }
                    }

                    elseif ( $post->getTime($postData)['year'] > $time->getPresentYear() )
                    {
                        $post->updatePost($postData, "month", $userResponse);
                        $user->updateState(States::MONTH_SAVED, $chatId);
                        $this->saveDateResponse($messageId, $chatId);
                    }

                }
                else
                {
                    $this->chooseMonthFailedResponse($chatId, $messageId);
                }
                break;


            /**
             * save post date
             */
            case(States::MONTH_SAVED):

                $userResponse = $translator->toEnglishNumber($this->userResponse);

                if ( $validator->isDate($userResponse) )
                {
                    if ( $post->getTime($postData)['year'] == $time->getPresentYear()
                        && $post->getTime($postData)['month'] == $time->getPresentMonth()
                    )

                    {
                        if ( $userResponse >= $time->getPresentDate() )
                        {
                            $post->updatePost($postData, "date", $userResponse);
                            $user->updateState(States::DATE_SAVED, $chatId);
                            $this->saveHourResponse($messageId, $chatId);
                        }
                        else
                        {
                            $this->chooseDateFailedResponse($chatId, $messageId);
                        }


                    }
                    else
                    {
                        $post->updatePost($postData, "date", $userResponse);
                        $user->updateState(States::DATE_SAVED, $chatId);
                        $this->saveHourResponse($messageId, $chatId);
                    }
                }
                else
                {
                    $this->chooseDateFailedResponse($chatId, $messageId);
                }

                break;

            /**
             * save post hour
             */
            case(States::DATE_SAVED):

                $userResponse = $translator->toEnglishNumber($this->userResponse);

                if ( $validator->isHour($userResponse) )
                {
                    if ( $post->getTime($postData)['year'] == $time->getPresentYear()
                        && $post->getTime($postData)['month'] == $time->getPresentMonth()
                        && $post->getTime($postData)['date'] == $time->getPresentDate()
                    )
                    {

                        if ( $userResponse >= $time->getPresentHour() )
                        {
                            $post->updatePost($postData, "hour", $userResponse);
                            $user->updateState(States::HOUR_SAVED, $chatId);
                            $this->saveMinuteResponse($messageId, $chatId);
                        }
                        else
                        {
                            $this->chooseHourFailedResponse($chatId, $messageId);
                        }

                    }

                    else
                    {
                        $post->updatePost($postData, "hour", $userResponse);
                        $user->updateState(States::HOUR_SAVED, $chatId);
                        $this->saveMinuteResponse($messageId, $chatId);
                    }

                }
                else
                {
                    $this->chooseHourFailedResponse($chatId, $messageId);
                }
                break;

            /**
             * save post minute
             */
            case(States::HOUR_SAVED):

                $userResponse = $translator->toEnglishNumber($this->userResponse);

                if ( $validator->isMinute($userResponse) )
                {
                    if ( $post->getTime($postData)['year'] == $time->getPresentYear()
                        && $post->getTime($postData)['month'] == $time->getPresentMonth()
                        && $post->getTime($postData)['date'] == $time->getPresentDate()
                        && $post->getTime($postData)['hour'] == $time->getPresentHour()
                    )
                    {
                        if ( $userResponse >= $time->getPresentMinute() )
                        {
                            $post->updatePost($postData, "minute", $userResponse);
                            $user->updateState(States::MINUTE_SAVED, $chatId);

                            $this->contextPostPreviewResponse($chatId, $post->getPostContent($postData));
                            $this->confirmationResponse($chatId);
                        }
                        else
                        {
                            $this->chooseMinuteFailedResponse($chatId, $messageId);
                        }
                    }
                    else
                    {
                        $post->updatePost($postData, "minute", $userResponse);
                        $user->updateState(States::MINUTE_SAVED, $chatId);

                        $this->contextPostPreviewResponse($chatId, $post->getPostContent($postData));
                        $this->confirmationResponse($chatId);
                    }
                }
                else
                {
                    $this->chooseMinuteFailedResponse($chatId, $messageId);
                }


                break;

            /**
             * user confirmation
             */
            case(States::MINUTE_SAVED):


                if ( $this->userResponse == Buttons::DONE_AND_SEND )
                {
                    $post->updatePost($postData, "is_accepted", 'true');
                    $post->updatePost($postData, "sent", 'false');
                    $user->updateState(States::IS_ACCEPTED, $chatId);

                    $this->showTimeSendingResponse($chatId, $post->getTime($postData));

                    $this->runStart($user, $chatId);

                }
                else
                {
                    $this->confirmationResponseFailed($chatId, $messageId);
                }

                break;
        }
        if ( $this->getUpdate()->getMessage()->getText() != Buttons::BACK )
            $user->updatePervoisMessage($this->getUpdate()->getMessage()->getText(), $chatId);
        /**
         *  END Interact bot-user block
         */


    }

    public function getPreviousState($presentState)
    {

        switch ($presentState)
        {
            case (States::CHANNEL_SAVED):
                return States::START_SEND_CONTEXT;
                break;

            case (States::CONTEXT_SAVED):

                return States::CHANNEL_SAVED;
                break;

            case (States::YEAR_SAVED):

                return States::CONTEXT_SAVED;
                break;

            case (States::MONTH_SAVED):

                return States::YEAR_SAVED;
                break;

            case (States::DATE_SAVED):

                return States::MONTH_SAVED;
                break;

            case (States::HOUR_SAVED):

                return States::DATE_SAVED;
                break;

            case (States::MINUTE_SAVED):

                return States:: HOUR_SAVED;
                break;
        }


    }

    /**
     * @param \MohandesPlusBot\Model\User $user
     * @param                             $chatId
     *
     * @internal param $this ->state
     */
    public function execBack(User $user, $chatId)
    {
        $user->updateState(
//            $this->getDoublePrevious($this->state),
            $this->getPreviousState($this->state),
            $chatId
        );
        echo "Before State:" . $this->state . PHP_EOL;
        $this->state = $this->getDoublePrevious($this->state);
//        $this->state = $this->getPreviousState($this->state);
        $this->userResponse = $user->getPervoisMessage($chatId);
        echo "New State:" . $this->state . PHP_EOL;
//        $this->conversation->stop();
//        $user->updatePervoisMessage(Buttons::BACK,$chatId);
//        $this->telegram->executeCommand(Command::SEND_TEXT);
    }

    public function getDoublePrevious($state)
    {
        return $this->getPreviousState($this->getPreviousState($state));
    }
}