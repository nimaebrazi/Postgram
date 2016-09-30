<?php

namespace MohandesPlusBot\Model;


use MohandesPlusBot\utils\DatabaseProvider\Database;

class ChannelUser
{
    protected static $DB;
    protected static $table;

    public function __construct()
    {
        $db = new Database();
        self::$DB = $db->makeInstance();
        self::$table = 'channel_user';
    }

    public function insert($userId, $channelId, $addAdminId)
    {
        self::$DB->insert(self::$table, ["user_id" => $userId, "channel_id" => $channelId, "add_admins_id" => $addAdminId]);
    }


}