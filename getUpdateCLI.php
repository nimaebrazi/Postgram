<?php


require __DIR__ . '/vendor/autoload.php';
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use MohandesPlusBot\utils\ConfigProvider\ConfigComponent;
use MohandesPlusBot\utils\DatabaseProvider\Database;

$config = new ConfigComponent();

$botConfig = $config->getBotConfig();
$config = $config->getDatabaseConfig();

$API_KEY = $botConfig['token'];
$BOT_NAME = $botConfig['name'];


$mysql_credentials = [
    'host' => "localhost",
    'user' => "XXX",
    'password' => "XXX",
    'database' => "postgram_bot_lib"
];

try
{
    // Create Telegram API object
    $telegram = new Telegram($API_KEY, $BOT_NAME);
    // Enable MySQL
    $telegram->enableMySQL($mysql_credentials);
    // Handle telegram getUpdate request
    $telegram->addCommandsPath('commands');
//    $telegram->setLogRequests(true);
//    $telegram->setLogPath($BOT_NAME . '.log');
//    $telegram->setLogVerbosity(3);
    $telegram->setDownloadPath('images');

    $postSender = new \MohandesPlusBot\Handler\PostSender();
    while (true)
    {

        $telegram->handleGetUpdates();
        $postSender->sendToChannel();
//        sleep(5);
    }

} catch (Longman\TelegramBot\Exception\TelegramException $e)
{
    echo $e->getMessage();
    echo $e->getTrace();
}