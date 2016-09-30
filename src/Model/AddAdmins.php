<?php


namespace MohandesPlusBot\Model;


use MohandesPlusBot\utils\DatabaseProvider\Database;
use PDO;

class AddAdmins
{
    protected static $DB;
    protected static $table;

    public function __construct()
    {
        $db = new Database();
        self::$DB = $db->makeInstance();
        self::$table = 'add_admins';
    }

    public static function create($creatorChatId, $adminChatId, $channelId)
    {
        $id = self::$DB->insert(self::$table, [

            "creator_chat_id" => $creatorChatId,
            "admin_chat_id" => $adminChatId,
            "channel_id" => $channelId

        ]);

        return $id;
    }

    public static function updateLastId($lastId, $column, $value)
    {
        self::$DB->update(self::$table, [$column => $value], ["id" => $lastId]);
    }

    public static function insertChatId($creatorChatId)
    {
        $data = ["creator_chat_id" => $creatorChatId,];

        self::$DB->insert(self::$table, $data);
    }

    public static function getLastId($creatorChatId)
    {
        $query = "SELECT * FROM " . self::$table . " WHERE id=" .
            "(SELECT MAX(id) FROM " . self::$table . " WHERE creator_chat_id=:creator_chat_id)";

        $sql = self::$DB->pdo->prepare($query);
        $sql->execute([":creator_chat_id" => $creatorChatId]);
        $admin = $sql->fetchAll(PDO::FETCH_ASSOC);

        return $admin[0]['id'];
    }

    public static function getLastRowInfo($lastId)
    {
        $query = "SELECT * FROM " . self::$table . " WHERE id=:id";

        $sql = self::$DB->pdo->prepare($query);
        $sql->execute([":id" => $lastId]);
        $result = $sql->fetchAll(PDO::FETCH_ASSOC);

        return $result[0];
    }

    public function getAdminsId($creatorChatId)
    {
        $query = "SELECT `id` FROM " . self::$table . " WHERE creator_chat_id=:creator_chat_id";

        $sql = self::$DB->pdo->prepare($query);
        $sql->execute([":creator_chat_id" => $creatorChatId]);
        $admins = $sql->fetchAll(PDO::FETCH_ASSOC);

        $all = [];
        foreach ($admins as $admin)
        {
            $all [] = $admin['id'];
        }

        return $all;

    }


}