<?php

namespace Meco\Rules\Delehi;

use Meco\Rules\LetterTranslateRuleTo;
use Meco\Unicode\MglUnicode;
use Meco\Unicode\ZvvnmodUnicode;

class DelehiTranslateRuleTo implements LetterTranslateRuleTo
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
        foreach ($zvvnModWord->getWordFragments() as $wordFragment) {
            $s = $this->get($s, $wordFragment->getKey());
            if ($s === null) {
            }
        }
        if (mb_substr($s, 0, 1, 'UTF-8') == "\u{202f}" && ($builder !== null && $builder !== '') && mb_substr($builder, mb_strlen($builder, 'UTF-8') - 1, 1, 'UTF-8') == "\u{0020}") {
            $builder = mb_substr($builder, 0, -1, 'UTF-8');
        }
        $builder .= $s;
    
    }

    private function get($preLetterCodes, $s)
    {

        $result = $this->resolveUe00c($preLetterCodes, $s);
        if ($result[0]) {
            return $result[1];
        }
        return $this->concatAnd202f($preLetterCodes, (ToDelehiMapper::getMapper()[$s] ?? null));
    
    }

    public function contains($s)
    {

        return isset(ToDelehiMapper::getMapper()[$s]);
    
    }

    public function isTranslateCodePoint($c)
    {

        return ZvvnmodUnicode::isZvvnmodCode($c);
    
    }

    private function resolveUe00c($preLetterCodes, $s)
    {

        if ($s !== "\u{e00c}") {
            return [false, null];
        }
        if (($preLetterCodes === null || $preLetterCodes === '')) {
            return [true, $preLetterCodes . "\u{1820}"];
        }
        $c = mb_substr($preLetterCodes, mb_strlen($preLetterCodes, 'UTF-8') - 1, 1, 'UTF-8');
        if (MglUnicode::isTraditionalEhshig($c)) {
            return [true, $preLetterCodes . "\u{1828}"];
        }
        return [true, $preLetterCodes . "\u{1820}"];
    
    }

    private function concatAnd202f($s0, $s1)
    {

        if (mb_substr($s1, 0, 1, 'UTF-8') == "\u{202f}" && mb_strlen($s0, 'UTF-8') > 0 && mb_substr($s0, mb_strlen($s0, 'UTF-8') - 1, 1, 'UTF-8') == "\u{0020}") {
            return mb_substr($s0, 0, mb_strlen($s0, 'UTF-8') - 1 - 0, 'UTF-8') . $s1;
        }
        return $s0 . $s1;
    
    }
}
