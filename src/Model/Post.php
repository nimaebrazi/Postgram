<?php

namespace MohandesPlusBot\Model;


use MohandesPlusBot\utils\DatabaseProvider\Database;
use MohandesPlusBot\utils\Time;
use PDO;

class Post extends Model
{
    protected static $DB;
    protected static $table;
    private static $shamsi;

    public function __construct()
    {
        $db = new Database();
        self::$shamsi = new Time();
        self::$DB = $db->makeInstance();
        self::$table = 'posts';
    }

    public static function getPostId($chatId)
    {
        $query = "SELECT * FROM " . self::$table . " WHERE id=" .
            "(SELECT MAX(id) FROM " . self::$table . " WHERE chat_id=:chat_id)";

        $stmt = self::$DB->pdo->prepare($query);
        $stmt->execute([":chat_id" => $chatId]);
        $post = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $post[0]['id'];
    }

    public static function updatePost($postId, $column, $value)
    {
        self::$DB->update(self::$table, [$column => $value], ["id" => $postId]);
    }

    public static function insertChatId($chatId)
    {
        $data = ["chat_id" => $chatId,];

        self::$DB->insert(self::$table, $data);

    }

    public static function getTime($postId)
    {
        $result = self::$DB->select(self::$table, ["year", "month", "date", "hour", "minute"], ["id" => $postId]);

        return $result[0];
    }

    public static function getPostContent($postId)
    {
        $result = self::$DB->select(self::$table, ['content', 'file_id', 'type'], ['id' => $postId]);

        return $result[0];
    }

    public static function getQueue($chatId, $channelId)
    {

        $query = "SELECT * FROM " . self::$table .
            " WHERE chat_id=:chat_id
            AND channel_id=:channel_id
            AND is_accepted=:is_accepted
            AND sent=:sent";

        $sql = self::$DB->pdo->prepare($query);

        $sql->execute([
            ":chat_id" => $chatId,
            ":channel_id" => $channelId,
            ":is_accepted" => "true",
            ":sent" => "false"
        ]);

        $posts = $sql->fetchAll(PDO::FETCH_ASSOC);

        return $posts;

    }

    public static function getQueueIds($chatId, $channelId)
    {

        $query = "SELECT * FROM " . self::$table .
            " WHERE chat_id=:chat_id
            AND channel_id=:channel_id
            AND is_accepted=:is_accepted
            AND sent=:sent";

        $sql = self::$DB->pdo->prepare($query);

        $sql->execute([
            ":chat_id" => $chatId,
            ":channel_id" => $channelId,
            ":is_accepted" => "true",
            ":sent" => "false"
        ]);

        $posts = $sql->fetchAll(PDO::FETCH_ASSOC);

        $all = [];
        foreach ($posts as $post)
        {
            $all[] = $post['id'];
        }

        return $all;

    }

    public static function remove($id)
    {
        $query = "UPDATE " . "`" . self::$table . "`" .
            " SET is_accepted=:is_accepted WHERE id=:id";

        $sql = self::$DB->pdo->prepare($query);

        $sql->execute(["id" => $id, ":is_accepted" => "false"]);

        $result = $sql->rowCount();
        return $result;

    }

    public static function fetchAll()
    {
        $query = "SELECT `id`, `channel_id`, `type`, `content`, `year`, `month`, `date`, `hour`, `minute`, `file_id` FROM "
            . self::$table .
            " WHERE sent=:sent" .
            " AND is_accepted=:is_accepted" .
            " AND `year`=:year";

        $sql = self::$DB->pdo->prepare($query);
        $sql->execute([
            ":sent" => "false",
            ":is_accepted" => "true",
            ":year" => self::$shamsi->getPresentYear()
        ]);

        $posts = $sql->fetchAll(PDO::FETCH_ASSOC);

        return $posts;

    }

    public static function setSent($id)
    {
        $query = "UPDATE " . "`" . self::$table . "`" .
            " SET sent=:sent WHERE id=:id";

        $sql = self::$DB->pdo->prepare($query);

        $sql->execute(["id" => $id, ":sent" => "true"]);

        $result = $sql->rowCount();
        return $result;
    }

}