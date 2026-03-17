<?php

namespace Meco\Enums;

class CodeType
{
    const ZVVNMOD = 'zvvnmod';
    const DELEHI = 'delehi';
    const MENK_SHAPE = 'menkshape';
    const MENK_LETTER = 'menkletter';
    const OYUN = 'oyun';
    const Z52 = 'z52';

    private static $seriesMap = [
        self::ZVVNMOD => CodeSeries::SHAPE,
        self::DELEHI => CodeSeries::LETTER,
        self::MENK_SHAPE => CodeSeries::SHAPE,
        self::MENK_LETTER => CodeSeries::LETTER,
        self::OYUN => CodeSeries::LETTER,
        self::Z52 => CodeSeries::SHAPE,
    ];

    /**
     * @param string $type
     * @return string CodeSeries constant
     */
    public static function codeSeries($type)
    {
        return isset(self::$seriesMap[$type]) ? self::$seriesMap[$type] : CodeSeries::LETTER;
    }

    /**
     * @param string $value
     * @return string|null CodeType constant
     */
    public static function fromString($value)
    {
        $lower = strtolower(str_replace('_', '', $value));
        $all = [self::ZVVNMOD, self::DELEHI, self::MENK_SHAPE, self::MENK_LETTER, self::OYUN, self::Z52];
        foreach ($all as $ct) {
            if ($ct === $lower) {
                return $ct;
            }
        }
        return null;
    }
}
