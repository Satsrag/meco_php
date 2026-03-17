<?php

namespace Meco\Rules\Menk;

use Meco\Enums\CharType;
use Meco\Rules\ShapeTranslateRule;
use Meco\Unicode\ZvvnmodUnicode;

class MenkShapeTranslateRuleTo implements ShapeTranslateRule
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function isTranslateCodePoint($c)
    {

        return ZvvnmodUnicode::isZvvnmodCode($c) ||
                ZvvnmodUnicode::isZvvnmodPunctuation($c);
    
    }

    public function contains($wordFragment)
    {

        return isset(ToMenkShapeMapper::getMapper()[$wordFragment->getKey()]);
    
    }

    public function getMapperCode($preFragmentContent, $wordFragment)
    {

        $result = $this->resloveTsatslaga($preFragmentContent, $wordFragment->getKey());
        if ($result !== null) {
            return $result;
        }
        return (ToMenkShapeMapper::getMapper()[$wordFragment->getKey()] ?? null);
    
    }

    public function getCharType($ch)
    {

        return null;
    
    }

    private function resloveTsatslaga($preFragmentContent, $s)
    {

        if ($s !== "\u{e00d}" || empty($preFragmentContent)) {
            return null;
        }
        $pre = $preFragmentContent[count($preFragmentContent) - 1];
        if (ZvvnmodUnicode::isZvvnmodTailCode($pre)) {
            return "\u{e26a}";
        }
        return null;
    
    }
}
