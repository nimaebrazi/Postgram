<?php


namespace MohandesPlusBot\utils\Validator;


use jDateTime;

class Validator
{
    private $year;
    private $month;
    private $day;
    private $hour;
    private $minute;

    public function __construct()
    {
        $date = new jDateTime(true, true, 'Asia/Tehran');

        $this->year = (int)$date->date("Y", false, false);
        $this->month = (int)$date->date("m", false, false);
        $this->day = (int)$date->date("d", false, false);
        $this->hour = (int)$date->date("H", false, false);
        $this->minute = (int)$date->date("i", false, false);

    }


    public function isNumber($input)
    {
        if ( ! preg_match('/^[0-9]+$/', $input) )
        {
            return false;
        }

        return true;
    }


    public function isYear($input)
    {
        if ( ! $this->isNumber($input) )
        {
            return false;
        }

        if ( ($input == null) || ($input < $this->year) || ($input < 1) )
        {
            return false;
        }

        return true;
    }

    public function isMonth($input)
    {
        if ( ! $this->isNumber($input) )
        {
            return false;
        }

        if ( ($input == null) || ($input > 12) || ($input < 1) )
        {
            return false;
        }

        return true;
    }

    public function isDate($input)
    {
        if ( ! $this->isNumber($input) )
        {
            return false;
        }

        if ( ($input == null) || ($input > 31) || ($input < 1) )
        {
            return false;
        }

        return true;
    }

    public function isHour($input)
    {
        if ( ! $this->isNumber($input) )
        {
            return false;
        }

        if ( ($input == null) || ($input > 23) || ($input < 0) )
        {
            return false;
        }

        return true;

    }

    public function isMinute($input)
    {
        if ( ! $this->isNumber($input) )
        {
            return false;
        }

        if ( ($input == null) || ($input > 59) || ($input < 0) )
        {
            return false;
        }

        return true;

    }

    public function isGif($input)
    {

    }

    public function isVideo($input)
    {

    }


    public function isPicture($input)
    {

    }


}