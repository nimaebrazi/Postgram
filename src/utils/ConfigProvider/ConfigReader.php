<?php

/**
 * Config Reader Util.
 *
 * This class load json config from 'config' directory in root project.
 *
 * Developer: @nimebrazi
 */

namespace MohandesPlusBot\utils\ConfigProvider;


use Exception;

abstract class ConfigReader
{
    protected static $configDir;
    protected static $configFile;

    public function __construct()
    {
        $rootDir = dirname(dirname(dirname(__FILE__)));
        self::$configDir = $rootDir . "/config";
        self::$configFile = self::$configDir . "/config.json";
    }


    protected static function configLoader()
    {
        if (!file_exists(self::$configFile))
        {
            throw new Exception("Config File in: " . self::$configDir . " not exists");
        }

        //after load file content do json decode
        $content = json_decode(file_get_contents(self::$configFile));

        return $content->config;
    }


    /**
     * @param $entity
     *
     * @return mixed
     * @throws Exception
     */
    protected static function getConfig($entity)
    {
        $entityConfig = self::configLoader()->$entity;

        if (!$entityConfig)
        {
            throw new Exception ($entity . " config not found in config file.");
        }

        return $entityConfig;
    }


    public function toArray()
    {
        //TODO : implement array config returner
    }

    public function toJson()
    {
        //TODO : implement json config returner
    }


}
