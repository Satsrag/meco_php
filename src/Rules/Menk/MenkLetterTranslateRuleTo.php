<?php

namespace Meco\Rules\Menk;

use Meco\Enums\Nature;
use Meco\Rules\LetterTranslateRuleTo;
use Meco\Unicode\MglUnicode;
use Meco\Unicode\ZvvnmodUnicode;

class MenkLetterTranslateRuleTo implements LetterTranslateRuleTo
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getMapperCode(&$builder, $zvvnModWord)
    {

        $s = "";
        $ue031 = false;
        foreach ($zvvnModWord->getWordFragments() as $wordFragment) {
            $s1 = $this->get($s, $wordFragment->getKey(), $zvvnModWord->getNature());

            if ($wordFragment->getKey() === "\u{e031}") {
                $ue031 = true;
                if (($s !== null && $s !== '') && mb_substr($s, -mb_strlen("\u{180d}\u{1822}", 'UTF-8'), null, 'UTF-8') === "\u{180d}\u{1822}") {
                    $s = mb_substr($s, 0, mb_strlen($s, 'UTF-8') - 2 - 0, 'UTF-8') . "\u{1822}";
                }
            }
            if ($ue031 && mb_strpos($s1, "\u{180d}\u{1822}") !== false) {
                $ue031 = false;
                $s1 = str_replace("\u{180d}\u{1822}", "\u{1822}", $s1);
            }

            if ($s1 === null) {
            }
            $s = $this->concatAnd202f($s, $s1);
        }
        if (mb_substr($s, 0, 1, 'UTF-8') == "\u{202f}" && ($builder !== null && $builder !== '') && mb_substr($builder, mb_strlen($builder, 'UTF-8') - 1, 1, 'UTF-8') == "\u{0020}") {
            $builder = mb_substr($builder, 0, -1, 'UTF-8');
        }
        $builder .= $s;
    
    }

    private function concatAnd202f($s0, $s1)
    {

        if (mb_substr($s1, 0, 1, 'UTF-8') == "\u{202f}" && mb_strlen($s0, 'UTF-8') > 0 && mb_substr($s0, mb_strlen($s0, 'UTF-8') - 1, 1, 'UTF-8') == "\u{0020}") {
            return mb_substr($s0, 0, mb_strlen($s0, 'UTF-8') - 1 - 0, 'UTF-8') . $s1;
        }
        return $s0 . $s1;
    
    }

    private function get($preLetterCodes, $s, $nature)
    {

        $mapperResult = $this->resolveUe00c($preLetterCodes, $s, $nature);
        if ($mapperResult[0]) {
            return $mapperResult[1];
        }
        $mapperResult = $this->resolveSingleGiiAndUe011($preLetterCodes, $s, $nature);
        if ($mapperResult[0]) {
            return $mapperResult[1];
        }

        $mapperString = null;
        if ($nature === Nature::CHAGH) {
            $mapperString = (ToMenkLetterMapper::getChaghMapper()[$s] ?? null);
        } else {
            $mapperString = (ToMenkLetterMapper::getHundiiMapper()[$s] ?? null);
        }
        if (($mapperString === null || $mapperString === '')) {
            $mapperString = (ToMenkLetterMapper::getMapper()[$s] ?? null);
        }
        return $mapperString;
    
    }

    public function contains($s)
    {

        if (isset(ToMenkLetterMapper::getChaghMapper()[$s])) {
            return true;
        }
        return isset(ToMenkLetterMapper::getMapper()[$s]);
    
    }

    public function isTranslateCodePoint($c)
    {

        return ZvvnmodUnicode::isZvvnmodCode($c);
    
    }

    private function resolveUe00c($preLetterCodes, $s, $nature)
    {

        if ($s !== "\u{e00c}") {
            return [false, null];
        }
        if (($preLetterCodes === null || $preLetterCodes === '')) {
            if ($nature === Nature::CHAGH) {
                return [true, "\u{1820}"];
            }
            return [true, "\u{1821}"];
        }
        $c = mb_substr($preLetterCodes, mb_strlen($preLetterCodes, 'UTF-8') - 1, 1, 'UTF-8');
        if (MglUnicode::isTraditionalEhshig($c)) {
            return [true, "\u{1828}"];
        }
        if ($nature === Nature::CHAGH) {
            return [true, "\u{1820}"];
        }
        return [true, "\u{1821}"];
    
    }

    private function resolveSingleGiiAndUe011($preLetterCodes, $s, $nature)
    {

        if ($s !== "\u{e011}" || ($preLetterCodes === null || $preLetterCodes === '')) {
            return [false, null];
        }

        if (mb_strlen($preLetterCodes, 'UTF-8') == 1) {
            $ch = mb_substr($preLetterCodes, 0, 1, 'UTF-8');
            if (MglUnicode::isGiiguulegch($ch)) {
                $result = null;
                if ($ch >= "\u{1832}" && $ch <= "\u{1834}") {
                    $result = "\u{1824}";
                } else {
                    $result = "\u{1824}\u{180b}";
                }
                return [true, $result];
            }
        }
        return [false, null];
    
    }
}
