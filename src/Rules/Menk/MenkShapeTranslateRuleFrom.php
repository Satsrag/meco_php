<?php

namespace Meco\Rules\Menk;

use Meco\Enums\CharType;
use Meco\Rules\ShapeTranslateRule;

class MenkShapeTranslateRuleFrom implements ShapeTranslateRule
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

        return MenkShapeUnicodeBlock::isTranslateCodePoint($c);
    
    }

    public function contains($wordFragment)
    {

        return isset(FromMenkShapeMapper::getMapper()[$wordFragment->getLocateKey()]);
    
    }

    public function getMapperCode($preFragmentContent, $wordFragment)
    {

        return (FromMenkShapeMapper::getMapper()[$wordFragment->getLocateKey()] ?? null);
    
    }

    private function isWordCodePoint($c)
    {

        return MenkShapeUnicodeBlock::isWordCodePoint($c);
    
    }

    public function getCharType($ch)
    {

        return $this->isWordCodePoint($ch) ? CharType::MONGOLIAN : CharType::OTHER;
    
    }
}
