<?php


namespace MohandesPlusBot\Model;


use MohandesPlusBot\utils\DatabaseProvider\Database;
use PDO;

class User extends Model
{
    protected static $DB;
    protected static $table;

    public function __construct()
    {
        $db = new Database();
        self::$DB = $db->makeInstance();
        self::$table = 'users';
    }

    public static function findByChatId($chatId)
    {
        $result = self::$DB->select(self::$table, '*', ["chat_id" => $chatId]);

        return $result[0];
    }

    public function findByUsername($username)
    {
        $query = "SELECT * FROM " . self::$table . " WHERE username=:username";

        $sql = self::$DB->pdo->prepare($query);
        $sql->execute([":username" => $username]);

        $result = $sql->fetchAll(PDO::FETCH_ASSOC)[0];

        return $result;
    }

    public static function findIdByChatId($chatId)
    {
        $query = "SELECT id FROM " . self::$table . " WHERE chat_id = :chat_id";

        $sql = self::$DB->pdo->prepare($query);
        $sql->bindParam(":chat_id", $chatId);
        $sql->execute();

        $result = $sql->fetchAll(PDO::FETCH_ASSOC)[0];

        return (int)$result["id"];
    }

    public static function updateState($state, $chatId)
    {
        return self::$DB->update(self::$table, ["state" => $state], ["chat_id" => $chatId]);
    }

    public static function registerNewUser($chat_id, $username, $firstName, $lastName, $state, $addedBy = null)
    {
        $data = [
            "chat_id" => $chat_id,
            "username" => $username,
            "first_name" => $firstName,
            "last_name" => $lastName,
            "state" => $state,
            "added_by" => $addedBy
        ];

        $id = self::$DB->insert(self::$table, $data);

        return $id;
    }

    public static function hasChannels($chatId)
    {
        $query = "SELECT u.username, c.username AS channel_name
                  FROM users u
                  JOIN channel_user c_u ON u.id = c_u.user_id
                  JOIN channels c ON c_u.channel_id = c.id
                  WHERE u.chat_id=" . $chatId;

        $sql = self::$DB->pdo->prepare($query);
        $sql->execute();

        $rows = $sql->fetchAll(PDO::FETCH_ASSOC);

        $channels = [];
        foreach ($rows as $row)
        {
            $channels[] = $row['channel_name'];
        }

        return $channels;

    }

    public static function getStateByChatId($chatId)
    {

        $result = self::$DB->select(self::$table, "state", ["chat_id" => $chatId]);
        return $result[0];
    }

    public function getLastUserId($addedBy)
    {
        $query = "SELECT * FROM " . self::$table . " WHERE id=" .
            "(SELECT MAX(id) FROM " . self::$table . " WHERE added_by=:added_by)";

        $stmt = self::$DB->pdo->prepare($query);
        $stmt->execute([":added_by" => $addedBy]);
        $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $user[0]['id'];
    }

    public function updatePervoisMessage($msg, $chatid)
    {
        $query = "UPDATE " . self::$table . " SET prev_msg=:msg where chat_id=:chat_id";

        $stmt = self::$DB->pdo->prepare($query);
        $stmt->execute([":msg" => $msg, ":chat_id" => $chatid]);
    }

    public function getPervoisMessage($chatid)
    {
        $query = "SELECT prev_msg FROM " . self::$table . " WHERE chat_id=:chat_id";
        $stmt = self::$DB->pdo->prepare($query);
        $stmt->execute([":chat_id" => $chatid]);
        $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $user[0]['prev_msg'];
    }

    public static function getAdmins($creatorChatId)
    {
        $addAdmins = new AddAdmins();
        $adminIds = $addAdmins->getAdminsId($creatorChatId);

        $query = "SELECT `username` FROM " . self::$table . " WHERE added_by=:added_by";
        $sql = self::$DB->pdo->prepare($query);


        $admins = [];
        for ($index = 0; $index < count($adminIds); $index++)
        {
            $sql->execute([":added_by" => $adminIds[$index]]);
            $admins[] = $sql->fetchAll(PDO::FETCH_ASSOC);
        }


        foreach ($admins as $admin)
        {
            //TODO: PHP Notice:  Undefined offset: 0 in /var/www/html/projects/MohandesPlusBotNew/src/Model/User.php on line 152
            if ( $admin[0]['username'] != null )
            {
                $all[] = $admin[0]['username'];
            }
        }

        return $all;


    }

    public static function findAdminsByChannel($creatorChatId, $channelName)
    {
        $addAdmins = new AddAdmins();
        $channel = new Channel();
        $channelId = $channel->findIdByUsername($channelName);
        $adminIds = $addAdmins->getAdminsId($creatorChatId);

        $query = "SELECT `id` FROM " . self::$table . " WHERE added_by=:added_by";
        $sql = self::$DB->pdo->prepare($query);


        $admins = [];
        for ($index = 0; $index < count($adminIds); $index++)
        {
            $sql->execute([":added_by" => $adminIds[$index]]);

            //TODO: PHP Notice:  Undefined offset: 0 in /var/www/html/projects/MohandesPlusBotNew/src/Model/User.php on line 178
            $admin = $sql->fetchAll(PDO::FETCH_ASSOC)[0]['id'];

            if ( $admin != null )
            {
                $admins[] = $admin;
            }
        }

        $query = "SELECT user_id FROM " . "channel_user" . " WHERE channel_id=:channel_id";
        $sql = self::$DB->pdo->prepare($query);
        $sql->execute(["channel_id" => $channelId]);
        $userIds = $sql->fetchAll(PDO::FETCH_ASSOC);

        $allAdminsIds = [];
        foreach ($userIds as $userId)
        {
            $allAdminsIds[] = $userId['user_id'];
        }

        $result = array_intersect($allAdminsIds, $admins);

        return $result;

    }

    public static function findUsernameById($id)
    {
        $query = "SELECT username FROM " . self::$table . " WHERE id=:id";
        $sql = self::$DB->pdo->prepare($query);
        $sql->execute([":id" => $id]);
        $result = $sql->fetchAll(PDO::FETCH_ASSOC)[0]['username'];
        return $result;
    }

    public static function remove($addedById, $channelId)
    {
        $query = "UPDATE `channel_user` SET user_id=:user_id WHERE add_admins_id=:add_admins_id AND channel_id=:channel_id";

        $sql = self::$DB->pdo->prepare($query);
        $sql->execute([
            ":user_id" => null,
            ":add_admins_id" => $addedById,
            ":channel_id" => $channelId
        ]);

        $result = $sql->rowCount();
        return $result;
    }


}