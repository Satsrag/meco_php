<?php

namespace Meco\Unicode;

use Meco\Enums\Nature;

class MglUnicode
{
    const LETTER_A = 0x1820;
    const LETTER_E = 0x1821;
    const LETTER_I = 0x1822;
    const LETTER_O = 0x1823;
    const LETTER_U = 0x1824;
    const LETTER_OE = 0x1825;
    const LETTER_UE = 0x1826;
    const LETTER_EE = 0x1827;
    const LETTER_NA = 0x1828;
    const LETTER_ANG = 0x1829;
    const LETTER_BA = 0x182A;
    const LETTER_PA = 0x182B;
    const LETTER_QA = 0x182C;
    const LETTER_GA = 0x182D;
    const LETTER_MA = 0x182E;
    const LETTER_LA = 0x182F;
    const LETTER_SA = 0x1830;
    const LETTER_SHA = 0x1831;
    const LETTER_TA = 0x1832;
    const LETTER_DA = 0x1833;
    const LETTER_CHA = 0x1834;
    const LETTER_JA = 0x1835;
    const LETTER_YA = 0x1836;
    const LETTER_RA = 0x1837;
    const LETTER_WA = 0x1838;
    const LETTER_FA = 0x1839;
    const LETTER_KA = 0x183A;
    const LETTER_KHA = 0x183B;
    const LETTER_TSA = 0x183C;
    const LETTER_ZA = 0x183D;
    const LETTER_HAA = 0x183E;
    const LETTER_ZRA = 0x183F;

    const FVS1 = 0x180B;
    const FVS2 = 0x180C;
    const FVS3 = 0x180D;
    const MVS = 0x180E;
    const NIRUGU = 0x180A;
    const NNBSP = 0x202F;

    private static $chaghVowels = [0x1820, 0x1823, 0x1824];
    private static $hundiiVowels = [0x1821, 0x1825, 0x1826];
    private static $traditionalVowels = [0x1820, 0x1821, 0x1822, 0x1823, 0x1824, 0x1825, 0x1826];

    private static function toOrd($cp)
    {
        return is_string($cp) ? mb_ord($cp, 'UTF-8') : $cp;
    }

    /** @return bool */
    public static function isNormalLetter($cp)
    {
        $cp = self::toOrd($cp);
        return $cp >= self::LETTER_A && $cp <= self::LETTER_ZRA;
    }

    /** @return bool */
    public static function isFreeVariationSelector($cp)
    {
        $cp = self::toOrd($cp);
        return $cp >= self::FVS1 && $cp <= self::FVS3;
    }

    /** @return bool */
    public static function isVowelSeparator($cp)
    {
        $cp = self::toOrd($cp);
        return $cp === self::MVS;
    }

    /** @return bool */
    public static function isNirugu($cp)
    {
        $cp = self::toOrd($cp);
        return $cp === self::NIRUGU;
    }

    /** @return bool */
    public static function isTraditionalEhshig($cp)
    {
        $cp = self::toOrd($cp);
        return in_array($cp, self::$traditionalVowels, true);
    }

    /** @return bool */
    public static function isEhshig($cp)
    {
        $cp = self::toOrd($cp);
        return in_array($cp, self::$traditionalVowels, true) || $cp === self::LETTER_EE;
    }

    /** @return bool */
    public static function isGiiguulegch($cp)
    {
        $cp = self::toOrd($cp);
        return $cp >= self::LETTER_NA && $cp <= self::LETTER_ZRA;
    }

    /** @return bool */
    public static function otherMongolianCode($cp)
    {
        $cp = self::toOrd($cp);
        return $cp === self::NIRUGU;
    }

    /** @return string Nature constant */
    public static function getCodeNature($cp)
    {
        $cp = self::toOrd($cp);
        if (in_array($cp, self::$chaghVowels, true)) {
            return Nature::CHAGH;
        }
        if (in_array($cp, self::$hundiiVowels, true)) {
            return Nature::HUNDII;
        }
        return Nature::SAARMAG;
    }
}
