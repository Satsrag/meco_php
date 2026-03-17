<?php

namespace Meco\Rules\Menk;

use Meco\Unicode\MglUnicode;

class MenkCodeBlock
{
    public static function isTranslateCodePoint(int $cp): bool
    {
        return MglUnicode::isNormalLetter($cp)
            || MglUnicode::isFreeVariationSelector($cp)
            || MglUnicode::isVowelSeparator($cp)
            || MglUnicode::isNirugu($cp);
    }

    public static function isWordCodePoint(int $cp): bool
    {
        return MglUnicode::isNormalLetter($cp)
            || MglUnicode::isFreeVariationSelector($cp)
            || MglUnicode::isVowelSeparator($cp)
            || MglUnicode::isNirugu($cp);
    }
}
