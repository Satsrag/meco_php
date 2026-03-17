<?php

namespace Meco\Helper;

class UnicodeHelper
{
    /**
     * Convert a UTF-8 string to an array of Unicode code points.
     * @param string $str
     * @return int[]
     */
    public static function toCodePoints($str)
    {
        $points = [];
        $len = mb_strlen($str, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($str, $i, 1, 'UTF-8');
            $points[] = mb_ord($ch, 'UTF-8');
        }
        return $points;
    }

    /**
     * Convert an array of code points to a UTF-8 string.
     * @param int[] $points
     * @return string
     */
    public static function fromCodePoints($points)
    {
        $s = '';
        foreach ($points as $cp) {
            $s .= mb_chr($cp, 'UTF-8');
        }
        return $s;
    }
}
