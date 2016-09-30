<?php
/**
 * Created by PhpStorm.
 * User: nima
 * Date: 7/2/16
 * Time: 7:14 PM
 */

namespace MohandesPlusBot\Enums;


class Command
{
    //Start Command
    const START = 'start';
    const START_DESC = 'دستور شروع';
    const START_USAGE = '/start';
    const START_VERSION = '1.0.0';

    //Post Menu Command
    const POST_MENU = 'postmenu';
    const POST_MENU_DESC = 'postmenu';
    const POST_MENU_USAGE = '/postmenu';
    const POST_MENU_VERSION = '1.0.0';

    //Management Tools Menu Command
    const MANAGEMENT_TOOLS_MENU = 'managementtoolsmenu';
    const MANAGEMENT_TOOLS_MENU_DESC = 'managementtoolsmenu';
    const MANAGEMENT_TOOLS_MENU_USAGE = '/managementtoolsmenu';
    const MANAGEMENT_TOOLS_MENU_VERSION = '1.0.0';

    //Channel Management Menu Command
    const CHANNEL_MANAGEMENT_MENU = 'channelmanagementmenu';
    const CHANNEL_MANAGEMENT_MENU_DESC = 'channelmanagementmenu';
    const CHANNEL_MANAGEMENT_MENU_USAGE = '/channelmanagementmenu';
    const CHANNEL_MANAGEMENT_MENU_VERSION = '1.0.0';


    //Send Text Command
    const SEND_TEXT = 'sendtext';
    const SEND_TEXT_DESC = 'ارسال متن';
    const SEND_TEXT_USAGE = '/sendtext';
    const SEND_TEXT_VERSION = '1.0.0';

    //Send Photo Command
    const SEND_PHOTO = 'sendphoto';
    const SEND_PHOTO_DESC = 'ارسال عکس';
    const SEND_PHOTO_USAGE = '/sendphoto';
    const SEND_PHOTO_VERSION = '1.0.0';

    //Send Video Command
    const SEND_VIDEO = 'sendvideo';
    const SEND_VIDEO_DESC = 'ارسال فیلم';
    const SEND_VIDEO_USAGE = '/sendvideo';
    const SEND_VIDEO_VERSION = '1.0.0';

    //Send Gif Command
    const SEND_GIF = 'sendgif';
    const SEND_GIF_DESC = 'ارسال گیف';
    const SEND_GIF_USAGE = '/sendgif';
    const SEND_GIF_VERSION = '1.0.0';

    //Forward Post Command
    const FORWARD_POST = 'forwardmessage';
    const FORWARD_POST_DESC = 'فوروارد پست';
    const FORWARD_POST_USAGE = '/forwardmessage';
    const FORWARD_POST_VERSION = '1.0.0';

    //Manage Admins Command
    const MANAGE_ADMINS = 'manageadmins';
    const MANAGE_ADMINS_DESC = 'مدیریت ادمین‌ها';
    const MANAGE_ADMINS_USAGE = '/manageadmins';
    const MANAGE_ADMINS_VERSION = '1.0.0';

    //Add Channel Command
    const ADD_CHANNEL = 'addchannel';
    const ADD_CHANNEL_DESC = 'اضافه کردن کانال';
    const ADD_CHANNEL_USAGE = '/addchannel';
    const ADD_CHANNEL_VERSION = '1.0.0';

    //Remove Channel Command
    const REMOVE_CHANNEL = 'removechannel';
    const REMOVE_CHANNEL_DESC = 'حذف کانال';
    const REMOVE_CHANNEL_USAGE = '/removechannel';
    const REMOVE_CHANNEL_VERSION = '1.0.0';


    //Show Admin Command
    const SHOW_ADMIN = 'showadmin';
    const SHOW_ADMIN_DESC = 'showadmin';
    const SHOW_ADMIN_USAGE = '/showadmin';
    const SHOW_ADMIN_VERSION = '1.0.0';

    //Add Admin Command
    const ADD_ADMIN = 'addadmin';
    const ADD_ADMIN_DESC = 'addadmin';
    const ADD_ADMIN_USAGE = '/addadmin';
    const ADD_ADMIN_VERSION = '1.0.0';

    //Remove Admin Command
    const REMOVE_ADMIN = 'removeadmin';
    const REMOVE_ADMIN_DESC = 'removeadmin';
    const REMOVE_ADMIN_USAGE = '/removeadmin';
    const REMOVE_ADMIN_VERSION = '1.0.0';

    //Remove Admin Command
    const POST_MANAGEMENT = 'postmanagement';
    const POST_MANAGEMENT_DESC = 'postmanagement';
    const POST_MANAGEMENT_USAGE = '/postmanagement';
    const POST_MANAGEMENT_VERSION = '1.0.0';

    //Cancel Command
    const CHANCEL = 'cancel';
    const CHANCEL_DESC = 'Cancel the currently active conversation';
    const CHANCEL_USAGE = '/cancel';
    const CHANCEL_VERSION = '0.1.1';

    //Help Command
    const HELP = 'help';
    const HELP_DESC = 'help';
    const HELP_USAGE = '/help';
    const HELP_VERSION = '0.1.1';

    //Contact Command
    const CONTACT = 'contact';
    const CONTACT_DESC = 'contact';
    const CONTACT_USAGE = '/contact';
    const CONTACT_VERSION = '0.1.1';


}