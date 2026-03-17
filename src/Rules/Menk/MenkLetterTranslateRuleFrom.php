<?php

namespace Meco\Rules\Menk;

use Meco\Enums\Nature;
use Meco\Rules\Delehi\DelehiCodeBlock;
use Meco\Rules\LetterTranslateRuleFrom;
use Meco\Unicode\MglUnicode;

class MenkLetterTranslateRuleFrom implements LetterTranslateRuleFrom
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getMapperCode($pre, $suf, $s, $nature)
    {

        $result = $this->resolveDevsgerI($pre, $s);
        if ($result !== null) {
            return $result;
        }
        $result = $this->resoloveW($pre, $s);
        if ($result !== null) {
            return $result;
        }
        $result = $this->resoloveT($suf, $s);
        if ($result !== null) {
            return $result;
        }
        $result = $this->resoloveG($suf, $s, $nature);
        if ($result !== null) {
            return $result;
        }
        $result = (FromMenkLetterMapper::getMapper()[$s] ?? null);
        if ($result !== null) {
            return $result;
        }
        if ($nature === Nature::CHAGH) {
            return (FromMenkLetterMapper::getChaghMapper()[$s] ?? null);
        } else if ($nature === Nature::HUNDII) {
            return (FromMenkLetterMapper::getHundiiMapper()[$s] ?? null);
        } else {
            return (FromMenkLetterMapper::getSaarmagMapper()[$s] ?? null);
        }
    
    }

    private function resolveDevsgerI($pre, $s)
    {

        if ($s !== "\u{1836}" && $s !== "\u{1822}") {
            return null;
        }
        if (empty($pre)) {
            return null;
        }
        $c = $pre[count($pre) - 1];
        if ($s === "\u{1822}" && ($c === "\u{1822}" || $c === "\u{1836}") && count($pre) > 2) {
            $pre2 = $pre[count($pre) - 2];
            foreach (FromMenkLetterMapper::$doubleIEhishig as $c1) {
                if ($c1 == $pre2) {
                    return "";
                }
            }
        }
        foreach (FromMenkLetterMapper::$doubleIEhishig as $c1) {
            if ($c1 == $c) {
                return "\u{e006}\u{e006}";
            }
        }
        return null;
    
    }

    private function resoloveW($pre, $s)
    {

        if (mb_strpos($s, "\u{1838}") === false) {
            return null;
        }
        if (empty($pre)) {
            return null;
        }
        $c = $pre[count($pre) - 1];
        if (MglUnicode::isEhshig($c)) {
            return (FromMenkLetterMapper::getWWithEhshig()[$s] ?? null);
        }
        return null;
    
    }

    private function resoloveT($suf, $s)
    {

        if ($s === "\u{1832}" && !empty($suf)) {
            $sufFirst = $suf[0];
            if (MglUnicode::isGiiguulegch($sufFirst)) {
                return "\u{e043}";
            }
        }
        return null;
    
    }

    private function resoloveG($suf, $s, $nature)
    {

        if ($s === "\u{182d}" && !empty($suf)) {
            $sufFirst = $suf[0];
            if (MglUnicode::isGiiguulegch($sufFirst)) {
                return $nature === Nature::CHAGH ? "\u{e005}\u{e005}" : "\u{e031}";
            }
        }
        return null;
    
    }

    public function contains($s)
    {

        if (isset(FromMenkLetterMapper::getMapper()[$s])) {
            return true;
        }
        return isset(FromMenkLetterMapper::getChaghMapper()[$s]);
    
    }

    public function getCodeNature($c)
    {

        return MglUnicode::getCodeNature($c);
    
    }

    public function isTranslateCodePoint($c)
    {

        return MglUnicode::isNormalLetter($c) || MglUnicode::isFreeVariationSelector($c) ||
                MglUnicode::isVowelSeparator($c) || DelehiCodeBlock::isWordConnector($c) ||
                MglUnicode::otherMongolianCode($c);
    
    }

    public function isWordCodePoint($c)
    {

        return MglUnicode::isNormalLetter($c) || MglUnicode::isFreeVariationSelector($c) ||
                MglUnicode::isVowelSeparator($c) || MglUnicode::otherMongolianCode($c);
    
    }
}
