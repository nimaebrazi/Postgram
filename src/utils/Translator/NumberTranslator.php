<?php


namespace MohandesPlusBot\utils\Translator;


class NumberTranslator
{
    public function toPersianNumber($input)
    {
        $unicode = array('۰', '۱', '۲', '۳', '٤', '٥', '٦', '۷', '۸', '۹');
        $english = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

        $string = str_replace($unicode, $english , $input);

        return $string;
    }

    public function toEnglishNumber($persianNumber)
    {
        $persian_num = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishNumber = range(0, 9);

        $string = str_replace($persian_num, $englishNumber, $persianNumber);

        return $string;

    }
}