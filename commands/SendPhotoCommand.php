<?php


namespace Longman\TelegramBot\Commands\UserCommands;

use DateTime;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ReplyKeyboardHide;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
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

class SendPhotoCommand extends MessageHandler
{
    /*
      *
      * case 8:
      * if ( empty($text) || ! ($text == Buttons::DONE_AND_SEND) )
      * {
      *
      * $time = $this->conversation->notes['year'] . '-' .
      * $this->conversation->notes['month'] . '-' .
      * $this->conversation->notes['day'] . '-' .
      * $this->conversation->notes['hour'] . '-' .
      * $this->conversation->notes['minute'];
      *
      * $keyboard = [
      * [Buttons::DONE_AND_SEND],
      * [Buttons::BACK, Buttons::CANCEL]
      * ];
      *
      * $data = [];
      * $data['chat_id'] = $chat_id;
      * $data['text'] = Messages::POST_PREVIEW;
      *
      * Request::sendMessage($data);
      *
      * $tData = [];
      * $tData['chat_id'] = $chat_id;
      * if ( strlen($this->conversation->notes['messageText']) > 200 )
      * {
      * $serverResponse = Request::getFile(['file_id' => $this->conversation->notes['photo']]);
      * if ( $serverResponse->isOk() )
      * {
      * $file_name = $serverResponse->getResult()->getFilePath();
      * Request::downloadFile($serverResponse->getResult());
      * $tData['parse_mode'] = 'Markdown';
      *
      * //TODO : HARD CODE => SET CONFIG FOR SERVER PATH
      * $path = 'http://scixnet.com/api/mohandesplusbot/images/' . str_replace('_', '', $file_name);
      *
      * $tData['text'] = $this->conversation->notes['messageText'] .
      * '[ ](' . $path . ')';
      * $this->conversation->notes['photo'] = $path;
      * $this->conversation->update();
      * Request::sendMessage($tData);
      * }
      * else
      * {
      * $tData['text'] = 'Server response not ok :(' . "\n" . @$serverResponse;
      * Request::sendMessage($tData);
      * }
      * }
      * else
      * {
      * $tData['photo'] = $this->conversation->notes['photo'];
      * $tData['caption'] = $this->conversation->notes['messageText'];
      * Request::sendPhoto($tData);
      * }
      *
      * if ( \PersianTimeGenerator::getTimeInMilliseconds($time) < round(microtime(true)) )
      * {
      * $data['text'] = Messages::ENTER_TIME_FAILED;
      * Request::sendMessage($data);
      * }
      *
      * $data['reply_markup'] = $keyboardHandler->makeReplyKeyboardMarkupInstance($keyboard);
      *
      * $data['text'] = 'برای ارسال پست بالا در تاریخ و زمان ' .
      * \PersianDateFormatter::format($this->conversation->notes) . ' دکمه‌ی ارسال را کلیک کنید. ';
      * $result = Request::sendMessage($data);
      * break;
      * }
      *
      * $databaser->addMessageToDatabase(
      * $this->conversation->notes['messageText'] . "\n" . '@mohandes_plus',
      * $this->conversation->notes['photo'],
      * '@' . $this->conversation->notes['channelName'],
      * $chat_id,
      * $this->conversation->notes['year'] . '-' .
      * $this->conversation->notes['month'] . '-' .
      * $this->conversation->notes['day'] . '-' .
      * $this->conversation->notes['hour'] . '-' .
      * $this->conversation->notes['minute']
      * );
      * $data = [];
      * $data['chat_id'] = $chat_id;
      *
      * $data['text'] = Messages::WILL_SEND_POST;
      * $data['reply_markup'] = new ReplyKeyboardHide(['selective' => true]);
      *
      * $result = Request::sendMessage($data);
      * $this->conversation->stop();
      * $this->telegram->executeCommand("start");
      * break;
      * }
      *
      *
      * }
      */

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
        $this->name = Command::SEND_PHOTO;
        $this->description = Command::SEND_PHOTO_DESC;
        $this->usage = Command::SEND_PHOTO_USAGE;
        $this->version = Command::SEND_PHOTO_VERSION;
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


        if ( $this->getUpdate()->getMessage()->getText() == Buttons::CANCEL )
        {
            $this->runStart($user, $chatId);
        }


        if ( States::START_SEND_PHOTO == $user->getStateByChatId($chatId) )
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
            case (States::START_SEND_PHOTO):

                $postType = $this->getUpdate()->getMessage()->getText();


                if ( $postType != null && $postType != "" )
                {
                    $post->updatePost($postData, "type", ButtonTranslator::englishTranslate($postType));
                    $post->updatePost($postData, "create_at", date("Y-m-d H:i:s"));
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

                $content = $this->getUpdate()->getMessage()->getText();

                if ( $content != null || $content != "" )
                {

                    if ( $content == Buttons::WITHOUT_CONTEXT )
                    {
                        $post->updatePost($postData, "content", "");
                        $user->updateState(States::CONTEXT_SAVED, $chatId);

                        $this->savePhotoResponse($messageId, $chatId);
                        break;

                    }

                    else
                    {
                        $post->updatePost($postData, "content", (string)$content);
                        $user->updateState(States::CONTEXT_SAVED, $chatId);

                        $this->savePhotoResponse($messageId, $chatId);
                        break;
                    }

                }

            /**
             * save photo
             */
            case (States::CONTEXT_SAVED):


                if ( $photoId = $this->getUpdate()->getMessage()->getPhoto() != null ||
                    $photoId = $this->getUpdate()->getMessage()->getPhoto() != ""
                )
                {
                    $smallPhotoSize = (count($this->getUpdate()->getMessage()->getPhoto())) - 2;
                    $mediumPhotoSize = (count($this->getUpdate()->getMessage()->getPhoto())) - 1;
                    $largePhotoSize = (count($this->getUpdate()->getMessage()->getPhoto()));

                    $post->updatePost($postData, "file_id", $this->getUpdate()->getMessage()->getPhoto()[$mediumPhotoSize]->getFileId());
                    $user->updateState(States::PHOTO_SAVED, $chatId);
                    $this->saveYearResponse($messageId, $chatId);
                }

                elseif ( $this->getUpdate()->getMessage()->getEntities()->getUrl() != null
                    || $this->getUpdate()->getMessage()->getEntities()->getUrl() == null
                )
                {
                    $this->savePhotoFailedResponse($messageId, $chatId);
                }

                else
                {
                    $this->savePhotoFailedResponse($messageId, $chatId);
                }

                break;


            /**
             * save post year
             */
            case(States::PHOTO_SAVED):

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


//                          $this->photoPostPreviewResponse($chatId, $post->getPostContent($postData));
                            //check str length in if
                            if ( false )
                            {
                                $serverResponse = Request::getFile(['file_id' => $post->getPostContent($postData)['file_id']]);

                                echo 'server response ---> ' . var_dump($serverResponse) . PHP_EOL;

                                if ( $serverResponse->isOk() )
                                {
                                    $fileName = $serverResponse->getResult()->getFilePath();
                                    Request::downloadFile($serverResponse->getResult());

                                    echo "server resp result ----> " . var_dump($serverResponse->getResult()) . PHP_EOL;

                                    $path = 'http://scixnet.com/api/mohandesplusbot/images/' . str_replace('_', '', $fileName);

                                    echo "path ----> " . $path . PHP_EOL;

                                    $botResponse = [];
                                    $botResponse['chat_id'] = $chatId;
                                    $botResponse['parse_mode'] = 'Markdown';
                                    $botResponse['text'] = $post->getPostContent($postData)['content'] . '[ ](' . $path . ')';
                                    $post->updatePost($postData, "path", $path);
                                    Request::sendMessage($botResponse);

                                    $botResponse['photo'] = $post->getPostContent($postData)['file_id'];
                                    $botResponse['caption'] = $post->getPostContent($postData)['content'];
                                    Request::sendPhoto($botResponse);


                                }
                            }
                            else
                            {
                                $this->photoPostPreviewResponse($chatId, $post->getPostContent($postData));
                            }
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

                        $this->photoPostPreviewResponse($chatId, $post->getPostContent($postData));
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