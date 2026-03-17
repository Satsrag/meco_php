<?php

namespace Meco\Rules\Z52;

use Meco\Enums\CharType;
use Meco\Rules\ShapeTranslateRule;
use Meco\Unicode\ZvvnmodUnicode;

class Z52TranslateRuleTo implements ShapeTranslateRule
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
                ZvvnmodUnicode::isToZ52Punctuation($c);
    
    }

    public function contains($wordFragment)
    {

        return isset(ToZ52Mapper::getMapper()[$wordFragment->getKey()]);
    
    }

    public function getMapperCode($preFragmentContent, $wordFragment)
    {

        return (ToZ52Mapper::getMapper()[$wordFragment->getKey()] ?? null);
    
    }

    public function getCharType($ch)
    {

        return null;
    
    }
}
