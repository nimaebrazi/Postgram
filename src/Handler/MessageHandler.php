<?php


namespace MohandesPlusBot\Handler;


use Longman\TelegramBot\Commands\Entities;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use MohandesPlusBot\Enums\Buttons;
use MohandesPlusBot\Enums\ChatAction;
use MohandesPlusBot\Enums\Command;
use MohandesPlusBot\Enums\Messages;
use MohandesPlusBot\Enums\States;
use MohandesPlusBot\Model\User;
use MohandesPlusBot\utils\Time;

/**
 * Class MessageHandler
 * @package MohandesPlusBot\Handler
 */
abstract class MessageHandler extends UserCommand
{

    /*
   |--------------------------------------------------------------------------
   | Helper Method Area
   |--------------------------------------------------------------------------
   |
   | These method called with other methods
   |
   */

    /**
     * Generate a message from time.
     *
     * @param $postTime
     *
     * @return string
     */
    protected function timeMessageGenerator($postTime)
    {
        $message = "پست شما در تاریخ:" . "\t" .
            $postTime['year'] . "/" . $postTime['month'] . "/" . $postTime['date'] . "\n" . //output: 1396/6/6
            "ساعت :" . "\t" .
            $postTime['hour'] . ":" . $postTime['minute'] . "\n" . //output: 20:33
            "ارسال خواهد شد.";

        return $message;
    }

    /**
     * All response method call sendMessage.
     *
     * @param      $chatId
     * @param      $message
     * @param null $messageId
     * @param null $keyboard
     *
     * @return mixed
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function sendMessage($chatId, $message, $messageId = null, $keyboard = null)
    {
        $botResponse = [];
        $botResponse['chat_id'] = $chatId;
        $botResponse['text'] = $message;

        if ( $messageId != null )
        {
            $botResponse['reply_to_message_id'] = $messageId;

        }

        //generate keyboard
        if ( $keyboard != null )
        {
            $keyboardHandler = new KeyboardHandler();

            if ( ! is_array($keyboard) )
            {
                $keyboard = $keyboardHandler->getKeyboard($keyboard);
            }

            $botResponse['reply_markup'] = $keyboardHandler->makeReplyKeyboardMarkupInstance($keyboard);
        }
        Request::sendChatAction(['chat_id' => $chatId, 'action' => ChatAction::TEXT_MESSAGE]);
        $result = Request::sendMessage($botResponse);

        return $result;
    }

    /**
     * We first check user is start bot until now or no.(chat_id is unique for each user)
     * If started bot we update user info on DB. Else register his on our DB.
     *
     * @param \MohandesPlusBot\Model\User $user
     * @param                             $chatId
     * @param                             $telegramUserName
     * @param                             $telegramFirstName
     * @param                             $telegramLastName
     * @param null                        $addedBy
     *
     * @return array|bool|int
     */
    protected function studyUserInfo(User $user, $chatId, $telegramUserName, $telegramFirstName, $telegramLastName, $addedBy)
    {
        if ( empty($user->findByChatId($chatId)) )
        {
            $userId = $user->registerNewUser(
                $chatId,
                $telegramUserName,
                $telegramFirstName,
                $telegramLastName,
                States::JUST_STARTED,
                $addedBy
            );
        }

        else if ( ! empty($user->findByChatId($chatId)) )
        {
            $userId = $user->findIdByChatId($chatId);
            $user->updateState(States::JUST_STARTED, $chatId);
        }

        return $userId;
    }

    protected function removeEmoji($text) {

        $clean_text = "";

        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

        return $clean_text;
    }


    /*
     |--------------------------------------------------------------------------
     | Media Preview Area
     |--------------------------------------------------------------------------
     |
     | After get post data from user in every class, we show post preview. media post preview is here.
     |
     */

    /**
     * @param $chatId
     * @param $postData
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     *
     */
    public function videoPostPreviewResponse($chatId, $postData)
    {
        $this->sendMessage($chatId, Messages::POST_PREVIEW);

        $botResponse = [];
        $botResponse['chat_id'] = $chatId;
        $botResponse['video'] = $postData['file_id'];
        $botResponse['caption'] = $postData['content'];
        Request::sendChatAction(['chat_id' => $chatId, 'action' => ChatAction::UPLOAD_VIDEO]);
        Request::sendVideo($botResponse);
    }

    /**
     * @param $chatId
     * @param $postData
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function photoPostPreviewResponse($chatId, $postData)
    {
        $this->sendMessage($chatId, Messages::POST_PREVIEW);

        $botResponse = [];
        $botResponse['chat_id'] = $chatId;
        $botResponse['photo'] = $postData['file_id'];
        $botResponse['caption'] = $postData['content'];
        Request::sendChatAction(['chat_id' => $chatId, 'action' => ChatAction::PHOTO]);
        Request::sendPhoto($botResponse);

    }

    /**
     * @param $chatId
     * @param $postData
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function gifPostPreviewResponse($chatId, $postData)
    {
        $this->sendMessage($chatId, Messages::POST_PREVIEW);

        $botResponse = [];
        $botResponse['chat_id'] = $chatId;
        $botResponse['document'] = $postData['file_id'];
        $botResponse['caption'] = $postData['content'];
        Request::sendChatAction(['chat_id' => $chatId, 'action' => ChatAction::GENERAL_FILE]);
        Request::sendDocument($botResponse);
    }


    /*
      |--------------------------------------------------------------------------
      | Response Area
      |--------------------------------------------------------------------------
      |
      | All response call sendMessage for interact with user.
      |
      */

    /**
     * Main menu keyboard.
     *
     * @param $chatId
     */
    public function mainMenuResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::CHOOSE_OPTION, null, 'main');
    }

    /*
     * Help response
     *
     * @param $chatId
     */
    public function helpResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::HELP);
    }

    /**
     * Contact us response
     *
     * @param $chatId
     */
    public function contactUsResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::CONTACT_US);
    }

    /**
     * Management tools keyboard.
     *
     * @param $chatId
     */
    public function managementToolsMenuResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::CHOOSE_OPTION, null, 'managementTools');
    }

    /**
     * Channel management tools keyboard.
     *
     * @param $chatId
     */
    public function channelsManagementMenuResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::CHOOSE_OPTION, null, 'manageChannels');
    }

    /**
     * Admin management tools keyboard.
     *
     * @param $chatId
     */
    public function adminsManagementMenuResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::CHOOSE_OPTION, null, 'manageAdmins');
    }

    /**
     * Choose channel message.
     *
     * @param $chatId
     * @param $messageId
     * @param $channels
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function chooseChannelResponse($chatId, $messageId, $channels)
    {
        $keyboard = [$channels, [Buttons::CANCEL]];
        $this->sendMessage($chatId, Messages::CHOOSE_CHANNEL, $messageId, $keyboard);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function sendPostContextResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::ENTER_POST, $messageId, "backAndCancel");
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function sendMediaPostCaptionResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::ENTER_MEDIA_CAPTION, $messageId, 'withoutContext');
    }

    /**
     * @param $messageId
     * @param $chatId
     *
     */
    public function saveYearResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::ENTER_YEAR, $messageId, "year");
    }

    /**
     * @param $messageId
     * @param $chatId
     *
     */
    public function saveMonthResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::ENTER_MONTH, $messageId, "month");
    }

    /**
     * @param $messageId
     * @param $chatId
     *
     */
    public function saveDateResponse($messageId, $chatId)
    {
        $time = new Time();
//        $keyboard = "";

        if ( $time->getPresentMonth() > 6 )
        {
            $keyboard = 'day30';
        }
        elseif ( $time->getPresentMonth() <= 6 )
        {
            $keyboard = 'day31';
        }
        $this->sendMessage($chatId, Messages::ENTER_DAY, $messageId, $keyboard);
    }

    /**
     * @param $messageId
     * @param $chatId
     * @param $keyboardHandler
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function saveHourResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::ENTER_HOUR, $messageId, "hour");
    }

    /**
     * @param $messageId
     * @param $chatId
     * @param $keyboardHandler
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function saveMinuteResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::ENTER_MINUTE, $messageId, "backAndCancel");
    }

    /**
     * @param $chatId
     * @param $keyboardHandler
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function confirmationResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::ACCEPT_BY_USER, null, "done");
    }

    /**
     * TODO: need to clean
     *
     * @param $chatId
     * @param $channelName
     *
     */
    public function removeChannelConfirmationResponse($chatId, $channelName)
    {
        $message = "این کانال را آیا میخواهید حذف کنید؟" . "\n" . '@' . $channelName;
        $this->sendMessage($chatId, $message, null, [[Buttons::DONE]]);
    }

    /**
     * TODO: need to clean
     *
     * @param $chatId
     * @param $channelName
     *
     */
    public function addChannelConfirmationResponse($chatId, $channelName)
    {
        $message = "این کانال اضافه شده است،اگر مورد تایید است دکمه تایید و ارسال را بزنید." . "\n" . '@' . $channelName;
        $this->sendMessage($chatId, $message, null, [[Buttons::DONE]]);
    }

    /**
     * @param $chatId
     * @param $postTime
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function showTimeSendingResponse($chatId, $postTime)
    {
        $message = $this->timeMessageGenerator($postTime) . "\n" . "\n" . Messages::END_LEVELS;
        $this->sendMessage($chatId, $message);
    }

    /**
     * @param $chatId
     * @param $postData
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function contextPostPreviewResponse($chatId, $postData)
    {
        $this->sendMessage($chatId, Messages::POST_PREVIEW);
        $this->sendMessage($chatId, $postData['content']);
    }

    /**
     * @param $messageId
     * @param $chatId
     *
     */
    public function saveVideoResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::SEND_VIDEO, $messageId, 'backAndCancel');
    }

    /**
     * @param $messageId
     * @param $chatId
     *
     */
    public function saveGifResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::SEND_GIF, $messageId, 'backAndCancel');
    }

    /**
     * @param $messageId
     * @param $chatId
     *
     */
    public function savePhotoResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::SEND_PHOTO, $messageId, 'backAndCancel');
    }

    /**
     * @param $channelName
     *
     * @return array
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function sendTestMessageToChannel($channelName)
    {
        $chatId = '@' . $channelName;
        $result = $this->sendMessage($chatId, Messages::TEST_TO_CHANNEL);

        return $result;
    }

    /**
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function addChannelSuccessResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::ADD_CHANNEL_SUCCESS);
    }

    /**
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function adminAddedSuccessResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::ADMIN_ADDED_SUCCESS);
    }

    /**
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function removeChannelSuccessResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::REMOVE_CHANNEL_SUCCESS);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     */
    public function addChannelResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::ADD_CHANNEL_HELP, $messageId, [[Buttons::CANCEL]]);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     */
    public function addAdminResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::FORWARD_MESSAGE_FROM_ADMIN, $messageId, [[Buttons::CANCEL]]);
    }

    /**
     * @param $chatId
     * @param $messageId
     * @param $admins
     */
    public function removeAdminResponse($chatId, $messageId, array $admins)
    {
        $keyboard = [$admins, [Buttons::CANCEL]];
        $this->sendMessage($chatId, "ادمین مورد نظر را انتخاب کنید", $messageId, $keyboard);
    }

    /**
     * @param $chatId
     * @param $postIds
     *
     */
    public function removePostResponse($chatId, $postIds)
    {
        $this->sendMessage($chatId, "برای حذف پست شماره آن را انتخاب کنید.", null, [$postIds, [Buttons::CANCEL]]);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function addUserNameResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::ADD_USERNAME_TO_ACCCOUNT, $messageId);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function channelExistsResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::REPORT_CHANNEL_EXIST_ADDED_ANOTHER_HUMAN, $messageId);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function botNotAdminChannelResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::ADD_CHANNEL_TO_BOT_FAILED, $messageId);
    }

    /**
     * @param $chatId
     * @param $messageId
     * @param $adminUsername
     */
    public function areYouSureAddAdminResponse($chatId, $messageId, $adminUsername)
    {
        $this->sendMessage($chatId, Messages::ARE_YOU_SURE_ADD_ADMIN . '@' . $adminUsername, $messageId, [[Buttons::YES]]);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function adminMustHaveUsernameResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::ADMIN_MUST_HAVE_USERNAME, $messageId);
    }

    //-------------------------------------------------------------------------------------


    /*
       |--------------------------------------------------------------------------
       | Failed Message Area
       |--------------------------------------------------------------------------
       |
       | All response have a failed Response. so all it is here.
       |
       */

    /**
     * @param $chatId
     * @param $messageId
     */
    public function notForwardPostResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::NOT_FORWARD_POST, $messageId);
    }

    /**
     * @param $chatId
     * @param $messageId
     */
    public function notFoundChannelResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::HAVE_NOT_CHANNEL_FOR_DELETE, $messageId);
    }

    /**
     * @param $messageId
     * @param $chatId
     *
     */
    public function saveVideoFailedResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::NOT_VIDEO, $messageId, 'backAndCancel');
    }

    /**
     * @param $messageId
     * @param $chatId
     *
     */
    public function savePhotoFailedResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::NOT_PHOTO, $messageId, 'backAndCancel');
    }

    /**
     * @param $messageId
     * @param $chatId
     *
     */
    public function saveGifFailedResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::NOT_GIF, $messageId, 'backAndCancel');
    }

    /**
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function removeChannelFailedResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::REMOVE_CHANNEL_FAILED);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function chooseYearFailedResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::CHOOSE_YEAR_FAILED, $messageId);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function chooseMonthFailedResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::CHOOSE_MONTH_FAILED, $messageId);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function chooseDateFailedResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::CHOOSE_DATE_FAILED, $messageId);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function chooseHourFailedResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::CHOOSE_HOUR_FAILED, $messageId);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function chooseMinuteFailedResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::CHOOSE_MINUTE_FAILED, $messageId);
    }

    /**
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function confirmationResponseFailed($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::NOT_ACCEPT_POST, $messageId);
    }

    /**
     * Choose channel failed message
     *
     * @param $chatId
     * @param $messageId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function chooseChannelFailedResponse($chatId, $messageId)
    {
        $this->sendMessage($chatId, Messages::CHOOSE_CHANNEL_FAILED, $messageId);
    }

    /**
     * @param $messageId
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function sendPostContextFailedResponse($messageId, $chatId)
    {
        $this->sendMessage($chatId, Messages::ENTER_CONTEXT_FAILED, $messageId);
    }

    /**
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function notConfirmedResponse($chatId)
    {
        $this->sendMessage($chatId, Messages::NOT_CONFIRME);
    }

    //-------------------------------------------------------------------------------------


    /*
      |--------------------------------------------------------------------------
      | Run Command Area
      |--------------------------------------------------------------------------
      |
      | In each depth we must be run a command. All run commands is here.
      |
      */

    /**
     * First update user state then run command.
     *
     * @param $user
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function runSendText(User $user, $chatId)
    {
        $user->updateState(States::START_SEND_CONTEXT, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::SEND_TEXT);
    }

    /**
     * First update user state then run command.
     *
     * @param $user
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function runSendPhoto(User $user, $chatId)
    {
        $user->updateState(States::START_SEND_PHOTO, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::SEND_PHOTO);
    }

    /**
     * First update user state then run command.
     *
     * @param $user
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function runSendVideo(User $user, $chatId)
    {
        $user->updateState(States::START_SEND_VIDEO, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::SEND_VIDEO);
    }

    /**
     * First update user state then run command.
     *
     * @param $user
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function runSendGif(User $user, $chatId)
    {
        $user->updateState(States::START_SEND_GIF, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::SEND_GIF);
    }

    /**
     * First update user state then run command.
     *
     * @param $user
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function runForwardPost(User $user, $chatId)
    {
        $user->updateState(States::START_FORWARD_POST, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::FORWARD_POST);
    }

    /**
     * First update user state then run command.
     *
     * @param $user
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function runChannelManagement(User $user, $chatId)
    {
        $user->updateState(States::CHANNEL_MANAGEMENT, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::CHANNEL_MANAGEMENT_MENU);
    }

    /**
     * First update user state then run command.
     *
     * @param $user
     * @param $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function runPostManagement(User $user, $chatId)
    {
        $user->updateState(States::START_POST_MANAGEMENT, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::POST_MANAGEMENT);
    }

    /**
     * @param \MohandesPlusBot\Model\User $user
     * @param                             $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function runAddChannel(User $user, $chatId)
    {
        $user->updateState(States::START_ADD_CHANNEL, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::ADD_CHANNEL);
    }

    /**
     * @param \MohandesPlusBot\Model\User $user
     * @param                             $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function runRemoveChannel(User $user, $chatId)
    {
        $user->updateState(States::START_REMOVE_CHANNEL, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::REMOVE_CHANNEL);
    }

    /**
     * @param \MohandesPlusBot\Model\User $user
     *
     * @param                             $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function runStart(User $user, $chatId)
    {

        $user->updateState(States::JUST_STARTED, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::START);
    }

    /**
     * @param \MohandesPlusBot\Model\User $user
     *
     * @param                             $chatId
     *
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function runRemoveAdminCommand(User $user, $chatId)
    {

        $user->updateState(States::START_REMOVE_ADMIN, $chatId);
        $this->conversation->stop();
        $this->telegram->executeCommand(Command::REMOVE_ADMIN);
    }


}