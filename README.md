# Postgram
## Send post on telegram, certain time 

### Libraries:

1. [Telegram bot api](https://github.com/akalongman/php-telegram-bot) 
2. [Database](https://github.com/catfan/Medoo/)
3. [Jalali Date](https://github.com/sallar/jdatetime)

### Instalation:

1. library install:

 ```bash
 $ composer install
 ```
2. DataBase Install

 ```bash
 $ mysql -u <username> -p
 ```
  ```mysql
 mysql> create database postgram_bot;
 ```
  ```mysql
 mysql> create database postgram_bot_fake;
 ```
3. Import database:

 ```bash
 $ mysql -u <username> -p postgram_bot < db_sql/postgram_bot.sql
 ```
 
 ```bash
 $ mysql -u <username> -p postgram_bot_lib < db_sql/postgram_bot_lib.sql
 ```
4. Remove sample library Commands:

  when run bot, probably command not run or run a response in loop. check this path and remove similar command:
  
  ```
  PROJECT_PATH/vendor/longman/telegram-bot/src/Commands
  ```
  
### Config:

  Set all configs in:
  ```
  PROJECT_PATH/src/config/config.json
  ```
  
  JUST THIS :)
  
### Set lib db config:
  
  in __PROJECT_PATH/getUpdateCLI.php__ from line 19 set library db config. it's just fake for prevent lib exeption. :-(
 
  ```php
  $mysql_credentials = [
    'host' => "localhost",
    'user' => "yourUsername",
    'password' => "yourPassword",
    'database' => "postgram_bot_lib"
];
  ```
  
### Run:
  
  ```bash
  $ php getUpdateCLI.php
  ```

### FAQ:

1. [why two database? :(](https://github.com/akalongman/php-telegram-bot#choose-how-to-retrieve-telegram-updates)
2. Which databases uses bot?
  ```postgram_bot.sql```
