<?php

namespace MohandesPlusBot\Handler;

use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use MohandesPlusBot\Enums\Buttons;


/**
 * Class KeyboardHandler
 * @package MohandesPlusBot\Handler
 *
 */
class KeyboardHandler
{

    /**
     * All keyboard config buttons.
     *
     * @type array
     *
     */
    private $keyboards = [

        'main' => [
            [Buttons::ADD_POST],
            [Buttons::MANAGEMENT_TOOLS],
            [Buttons::HELP, Buttons::CONTACT_WITH_US]
        ],


        'managementTools' => [
            [Buttons::MANAGE_POST_QUEUE],
            [Buttons::CHANNEL_MANAGEMENT],
            [Buttons::BACK]
        ],

        'postType' => [
            [Buttons::PICTURE_AND_CONTEXT, Buttons::CONTEXT],
            [Buttons::GIF_AND_CONTEXT, Buttons::VIDEO_AND_CONTEXT],
            [Buttons::BACK, Buttons::FORWARD_POST]
        ],

        'manageAdmins' => [
            [Buttons::SHOW_ADMINS],
            [Buttons::REMOVE_ADMIN, Buttons::ADD_ADMIN],
            [Buttons::BACK, Buttons::CANCEL]
        ],

        'manageChannels' => [
            [Buttons::MY_CHANNELS],
            [Buttons::REMOVE_CHANNEL, Buttons::ADD_CHANNEL],
            [Buttons::CANCEL]
        ],

        'backAndCancel' => [
            [Buttons::BACK, Buttons::CANCEL]
        ],

        'yesAndNo' => [
            [Buttons::NO, Buttons::YES]
        ],

        'withoutContext' => [
            [Buttons::WITHOUT_CONTEXT],
            [Buttons::BACK, Buttons::CANCEL]
        ],


        'year' => [
            ['۱۳۹۵', '۱۳۹۶', '۱۳۹۷'],
            [Buttons::BACK, Buttons::CANCEL]
        ],

        'month' => [
            ['۱', '۲', '۳', '۴'],
            ['۵', '۶', '۷', '۸'],
            ['۹', '۱۰', '۱۱', '۱۲'],
            [Buttons::BACK, Buttons::CANCEL]
        ],

        'day30' => [
            ['1', '2', '3', '4', '5', '6', '7', '8'],
            ['9', '10', '11', '12', '13', '14', '15', '16'],
            ['17', '18', '19', '20', '21', '22', '23', '24'],
            ['25', '26', '27', '28', '29', '30', ' ', ' '],
            [Buttons::BACK, Buttons::CANCEL]
        ],

        'day31' => [
            ['۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸'],
            ['۹', '۱۰', '۱۱', '۱۲', '۱۳', '۱۴', '۱۵', '۱۶'],
            ['۱۷', '۱۸', '۱۹', '۲۰', '۲۱', '۲۲', '۲۳', '۲۴'],
            ['۲۵', '۲۶', '۲۷', '۲۸', '۲۹', '۳۰', '۳۱', ' '],
            [Buttons::BACK, Buttons::CANCEL]
        ],

        'hour' => [
            ['۰', '۱', '۲', '۳', '۴', '۵'],
            ['۶', '۷', '۸', '۹', '۱۰', '۱۱'],
            ['۱۲', '۱۳', '۱۴', '۱۵', '۱۶', '۱۷'],
            ['۱۸', '۱۹', '۲۰', '۲۱', '۲۲', '۲۳'],
            [Buttons::BACK, Buttons::CANCEL]
        ],

        'done' => [
            [Buttons::DONE_AND_SEND],
            [Buttons::BACK, Buttons::CANCEL]
        ],
    ];


    /**
     * Return a keyboard from $keyboards array.
     *
     * @param $name
     *
     * @return mixed
     *
     */
    public function getKeyboard($name)
    {
        return $this->keyboards[$name];
    }


    /**
     * Return an instance from Reply Keyboard Markup.
     *
     * @param $keyboard
     *
     * @return \Longman\TelegramBot\Entities\ReplyKeyboardMarkup
     */
    public function makeReplyKeyboardMarkupInstance($keyboard)
    {
        $config = [
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            'selective' => true
        ];

        $instance = new ReplyKeyboardMarkup($config);

        return $instance;

    }

}