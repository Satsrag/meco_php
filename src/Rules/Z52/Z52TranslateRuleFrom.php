<?php

namespace Meco\Rules\Z52;

use Meco\Enums\CharType;
use Meco\Rules\ShapeTranslateRule;
use Meco\Unicode\Z52Unicode;

class Z52TranslateRuleFrom implements ShapeTranslateRule
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

        return Z52Unicode::isZ52Code($c) || Z52Unicode::isZ52Punctuation($c);
    
    }

    public function contains($wordFragment)
    {

        return isset(FromZ52Mapper::getMapper()[$wordFragment->getLocateKey()]);
    
    }

    public function getMapperCode($preFragmentContent, $wordFragment)
    {

        return (FromZ52Mapper::getMapper()[$wordFragment->getLocateKey()] ?? null);
    
    }

    public function getCharType($ch)
    {

        return Z52Unicode::isZ52Code($ch) ? CharType::MONGOLIAN : CharType::OTHER;
    
    }
}
