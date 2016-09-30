<?php


namespace MohandesPlusBot\utils;


use jDateTime;

class Time
{
    private $shamsi;

    public function __construct()
    {
        $this->shamsi = new jDateTime(true, true, 'Asia/Tehran');
    }

    public function getPresentYear()
    {
        return (int)$this->shamsi->date("Y", false, false);
    }

    public function getPresentMonth()
    {
        return (int)$this->shamsi->date("m", false, false);

    }

    public function getPresentDate()
    {
        return (int)$this->shamsi->date("d", false, false);

    }

    public function getPresentHour()
    {
        return (int)$this->shamsi->date("H", false, false);

    }

    public function getPresentMinute()
    {
        return (int)$this->shamsi->date("i", false, false);

    }


    public function isExpired(array $input)
    {
        $now = $this->shamsi->toJalali(date('Y'), date('m'), date('d'));

        $timestampNow = mktime($this->getPresentHour(), $this->getPresentMinute(), 0, $now[1], $now[2], $now[3]);

        $timeInput = mktime($input['hour'], $input['minute'], 0, $input['month'], $input['date'], $input['year']);

        if ($timeInput < $timestampNow)
        {
            return false;
        }

        return true;

    }
}