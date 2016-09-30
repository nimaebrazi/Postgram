<?php


namespace MohandesPlusBot\Model;


use MohandesPlusBot\utils\DatabaseProvider\Database;

abstract class Model
{

    protected static $DB;
    protected static $table;

    public function __construct()
    {

        self::$DB = Database::makeInstance();
    }

}