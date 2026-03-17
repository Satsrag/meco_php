<?php

namespace Meco\Rules\Menk;

class MenkShapeUnicodeBlock
{
    private static $notSupport = null;

    public static function isTranslateCodePoint($cp)
    {
        return ($cp >= 0xE234 && $cp <= 0xE34A) && !self::isNotSupported($cp);
    }

    public static function isWordCodePoint($cp)
    {
        return $cp >= 0xE269 && $cp <= 0xE34A && !self::isNotSupported($cp);
    }

    private static function isNotSupported($cp)
    {
        if (self::$notSupport === null) {
            self::$notSupport = array_flip([
                0xE26E, 0xE26F, 0xE270, 0xE272, 0xE273, 0xE274, 0xE275, 0xE276,
                0xE278, 0xE279, 0xE27A, 0xE27D, 0xE280, 0xE281, 0xE283, 0xE284,
                0xE289, 0xE28B, 0xE28C, 0xE28D, 0xE28E, 0xE28F, 0xE290,
                0xE292, 0xE293, 0xE294, 0xE295, 0xE299, 0xE29A, 0xE29B, 0xE29C,
                0xE29E, 0xE29F, 0xE2A0, 0xE2A1, 0xE2A2, 0xE2A3, 0xE2A4, 0xE2A5,
                0xE2A6, 0xE2A7, 0xE2A8, 0xE2A9, 0xE2AA, 0xE2AB, 0xE2AC, 0xE2AD,
                0xE2AE, 0xE2AF, 0xE2B0, 0xE2B2, 0xE2B3, 0xE2B4,
            ]);
        }
        return isset(self::$notSupport[$cp]);
    }
}
