<?php


namespace Longman\TelegramBot\Commands\UserCommands;

use DateTime;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ReplyKeyboardHide;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\ChatAction;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\Messages;
use MohandesPlusBot\Enums\States;
use MohandesPlusBot\Handler\KeyboardHandler;
use MohandesPlusBot\Handler\MessageHandler;
use MohandesPlusBot\Model\Channel;
use MohandesPlusBot\Model\Post;
use MohandesPlusBot\Model\User;
use MohandesPlusBot\utils\Time;
use MohandesPlusBot\utils\Translator\ButtonTranslator;
use MohandesPlusBot\utils\Translator\NumberTranslator;
use MohandesPlusBot\utils\Validator\Validator;

class SendVideoCommand extends MessageHandler
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
        $this->name = Command::SEND_VIDEO;
        $this->description = Command::SEND_VIDEO_DESC;
        $this->usage = Command::SEND_VIDEO_USAGE;
        $this->version = Command::SEND_VIDEO_VERSION;
        $this->enabled = true;
        $this->public = true;
        $this->need_mysql = false;
    }

    public function execute()
    {
        /*
         * contact info
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

        if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        if ( States::START_SEND_VIDEO == $user->getStateByChatId($chatId) )
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

        switch ($user->getStateByChatId($chatId))
        {

            /**
             * save post type
             */
            case (States::START_SEND_VIDEO):

                $postType = $this->getUpdate()->getMessage()->getText();

                if ( $postType != null && $postType != "" )
                {
                    $post->updatePost($postData, "type", ButtonTranslator::englishTranslate($postType));
                    $user->updateState(States::TYPE_SAVED, $chatId);
                    $this->chooseChannelResponse($chatId, $messageId, $channels);
                    break;
                }

//            else
//            {
//            }

            /**
             * save post channels
             */
            case (States::TYPE_SAVED):

                $channelUserName = $this->getUpdate()->getMessage()->getText();

                if ( $channelUserName != null && $channelUserName != "" && in_array($channelUserName, $channels) )
                {

                    $post->updatePost($postData, "channel_id", $channel->findIdByUsername($channelUserName));
                    $post->updatePost($postData, "create_at", date("Y-m-d H:i:s"));
                    $user->updateState(States::CHANNEL_SAVED, $chatId);
                    $this->sendMediaPostCaptionResponse($chatId, $messageId);
                    break;
                }
                else
                {
                    $this->chooseChannelFailedResponse($chatId, $messageId);
                    break;
                }


            /**
             * save post content
             */
            case(States::CHANNEL_SAVED):

                /**
                 * TODO : BugFix => check string length (150 char)
                 */

                $content = $this->getUpdate()->getMessage()->getText();

                if ( (strlen($content) < 200) && ($content != null || $content != "") )
                {

                    if ( $content == Buttons::WITHOUT_CONTEXT )
                    {
                        $post->updatePost($postData, "content", "");
                        $user->updateState(States::CONTEXT_SAVED, $chatId);
                        $this->saveVideoResponse($messageId, $chatId);
                        break;

                    }

                    else
                    {
                        $post->updatePost($postData, "content", (string)$content);
                        $user->updateState(States::CONTEXT_SAVED, $chatId);
                        $this->saveVideoResponse($messageId, $chatId);
                        break;
                    }

                }
                else
                {
                    $botResponse['chat_id'] = $chatId;
                    $botResponse['text'] = 'کپشن شما بیش از ۲۰۰ کاراکتر است';
                    Request::sendMessage($botResponse);
                    break;
                }

            /**
             * save video
             */
            case (States::CONTEXT_SAVED):


                if ( $this->getUpdate()->getMessage()->getVideo() != null
                    || $this->getUpdate()->getMessage()->getVideo() != ""
                )
                {
                    $post->updatePost($postData, "file_id", $this->getUpdate()->getMessage()->getVideo()->getFileId());
                    $user->updateState(States::VIDEO_SAVED, $chatId);
                    $this->saveYearResponse($messageId, $chatId);
                    break;
                }

                else
                {
                    $this->saveVideoFailedResponse($messageId, $chatId);
                    break;
                }


            /**
             * save post year
             */
            case(States::VIDEO_SAVED):

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

                            $this->videoPostPreviewResponse($chatId, $post->getPostContent($postData));
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

                        $this->videoPostPreviewResponse($chatId, $post->getPostContent($postData));
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
        /**
         *  END Interact bot-user block
         */
    }


}