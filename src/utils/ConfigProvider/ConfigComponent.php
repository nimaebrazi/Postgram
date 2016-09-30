<?php

namespace MohandesPlusBot\utils\ConfigProvider;

/**
 * Class ConfigComponent return project config an array with one method for each entity.
 *
 * @package MohandesPlusBot\utils\ConfigProvider
 *
 */
class ConfigComponent extends ConfigReader
{

    /**
     * Get database config
     *
     * @return array
     */
    public static function getDatabaseConfig()
    {

        $config = self::getConfig('database');

        //return array config
        //TODO : merge get_object_vars() function with getConfig.
        return get_object_vars($config);

    }


    /**
     * Get Bot config.
     *
     * @return array
     *
     */
    public static function getBotConfig()
    {

        $config = self::getConfig('bot');

        //return array config
        //TODO : merge get_object_vars() function with getConfig.
        return get_object_vars($config);

    }


    /**
     * Get Path config.
     *
     * @return array
     *
     */
    public static function getPathConfig()
    {

        $config = self::getConfig('path');

        //return array config
        //TODO : merge get_object_vars() function with getConfig.
        return get_object_vars($config);

    }

    /**
     * Get Doctrine config.
     *
     * @return array
     *
     */
    public static function getDoctrineConfig()
    {

        $config = self::getConfig('doctrine');

        //return array config
        //TODO : merge get_object_vars() function with getConfig.
        return get_object_vars($config);

    }



}