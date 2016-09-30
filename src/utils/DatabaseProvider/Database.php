<?php

namespace MohandesPlusBot\utils\DatabaseProvider;


use medoo;
use MohandesPlusBot\utils\ConfigProvider\ConfigComponent;

/**
 * Class Database.
 *
 * @package MohandesPlusBot\utils\DatabaseProvider
 * @author  nimaebrazi
 *
 */
class Database
{
    /**
     * Assign all database array config to @var $databaseConfig
     *
     * @var
     *
     */
    private static $databaseConfig;


    /**
     * Database constructor @call loadDatabaseConfig method.
     *
     */
    public function __construct()
    {
        $this->loadDatabaseConfig();

    }


    /**
     *Load config and assign @var $config to @var $databaseConfig
     *
     */
    private function loadDatabaseConfig()
    {
        $configComponent = new ConfigComponent();

        self::$databaseConfig = $configComponent->getDatabaseConfig();
    }


    /**
     * Set medoo config and return an instance from it.
     *
     * @return medoo
     *
     */
    public function makeInstance()
    {
        $config = [
            'database_type' => self::$databaseConfig['driver'],
            'database_name' => self::$databaseConfig['name'],
            'server' => self::$databaseConfig['host'],
            'username' => self::$databaseConfig['username'],
            'password' => self::$databaseConfig['password'],
            'charset' => self::$databaseConfig['charset']
        ];

        $instance = new medoo($config);

        return $instance;

    }
}