<?php

namespace Meco\Rules\Delehi;

class DelehiCodeBlock
{
    public static function isMongolianLetter(int $cp): bool
    {
        return $cp >= 0x1820 && $cp <= 0x1842;
    }

    public static function isFreeVariationSelector(int $cp): bool
    {
        return $cp >= 0x180B && $cp <= 0x180D;
    }

    public static function isVowelSeparator(int $cp): bool
    {
        return $cp === 0x180E;
    }

    public static function isNarrowNoBreakSpace(int $cp): bool
    {
        return $cp === 0x202F;
    }

    public static function isWordConnector(int $cp): bool
    {
        return $cp === 0x202F;
    }

    public static function isWordCodePoint(int $cp): bool
    {
        return self::isMongolianLetter($cp)
            || self::isFreeVariationSelector($cp)
            || self::isVowelSeparator($cp)
            || self::isNarrowNoBreakSpace($cp);
    }

    public static function isTranslatableCodePoint(int $cp): bool
    {
        return self::isMongolianLetter($cp)
            || self::isFreeVariationSelector($cp)
            || self::isVowelSeparator($cp)
            || self::isNarrowNoBreakSpace($cp)
            || $cp === 0x180A;
    }
}
