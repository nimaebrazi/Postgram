<?php


namespace MohandesPlusBot\Model;


use MohandesPlusBot\utils\DatabaseProvider\Database;
use PDO;

class Channel
{
    protected static $DB;
    protected static $table;
    protected static $channelUser;

    public function __construct()
    {
        $db = new Database();
        self::$DB = $db->makeInstance();
        self::$table = 'channels';
        self::$channelUser = 'channel_user';
    }

    public function saveChannel($name, $userId)
    {
        $channelId = self::$DB->insert(self::$table, ["username" => $name]);

        self::$DB->insert(self::$channelUser, ["user_id" => $userId, "channel_id" => $channelId, "active" => "true"]);
    }

    public function removeChannel($username)
    {
        self::$DB->delete(self::$table, ["username" => $username]);
    }

    public static function getAllChannels()
    {
        $query = "SELECT `username` FROM " . self::$table . " WHERE 1";

        $sql = self::$DB->pdo->prepare($query);
        $sql->execute();

        $channels = $sql->fetchAll(PDO::FETCH_ASSOC);

        //assign all channels name to an array
        $all = [];
        foreach ($channels as $channel)
        {
            $all [] = $channel['username'];
        }

        return $all;
    }

    public static function findIdByUsername($username)
    {
        $query = "SELECT id FROM " . self::$table . " WHERE username = :username";

        $sql = self::$DB->pdo->prepare($query);
        $sql->bindParam(":username", $username);
        $sql->execute();

        $result = $sql->fetchAll(PDO::FETCH_ASSOC)[0];

        return (int)$result["id"];

    }

    public static function findUsernameById($id)
    {
        $query = "SELECT username FROM " . self::$table . " WHERE id = :id";

        $sql = self::$DB->pdo->prepare($query);
        $sql->bindParam(":id", $id);
        $sql->execute();

        $result = $sql->fetchAll(PDO::FETCH_ASSOC)[0];

        return $result['username'];

    }


    public static function hasUsers($channelName)
    {
        $query = "SELECT  u.username
                  FROM users u
                  JOIN channel_user cu ON u.id = cu.user_id
                  JOIN channels c ON cu.channel_id = c.id
                  WHERE c.username='" . $channelName . "'";

        $sql = self::$DB->pdo->prepare($query);
        $sql->execute();
        $rows = $sql->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row)
        {
            $users[] = $row['username'];
        }

        return $users;
    }


}