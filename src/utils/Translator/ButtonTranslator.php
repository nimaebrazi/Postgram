<?php

namespace MohandesPlusBot\utils\Translator;


use MohandesPlusBot\Enums\Buttons;

class ButtonTranslator
{
    public static function englishTranslate($input)
    {
        switch ($input)
        {
            case (Buttons::CONTEXT):
                return "context";
                break;

            case (Buttons::GIF_AND_CONTEXT):
                return "gif";
                break;

            case (Buttons::PICTURE_AND_CONTEXT):
                return "photo";
                break;

            case (Buttons::VIDEO_AND_CONTEXT):
                return "video";
                break;
            case (Buttons::FORWARD_POST):
                return "forward";
                break;
        }
    }
}