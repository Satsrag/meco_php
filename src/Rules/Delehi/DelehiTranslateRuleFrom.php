<?php

namespace Meco\Rules\Delehi;

use Meco\Enums\Nature;
use Meco\Rules\Delehi\DelehiCodeBlock;
use Meco\Rules\LetterTranslateRuleFrom;
use Meco\Unicode\MglUnicode;

class DelehiTranslateRuleFrom implements LetterTranslateRuleFrom
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getMapperCode($pre, $stuf, $s, $nature)
    {

        $result = $this->resolveDevsgerI($pre, $s);
        if ($result !== null) {
            return $result;
        }
        $result = (FromDelehiMapper::getMapper()[$s] ?? null);
        if ($result !== null) {
            return $result;
        }
        if ($nature === Nature::CHAGH) {
            return (FromDelehiMapper::getChaghMapper()[$s] ?? null);
        } else if ($nature === Nature::HUNDII) {
            return (FromDelehiMapper::getHundiiMapper()[$s] ?? null);
        } else {
            return (FromDelehiMapper::getSaarmagMapper()[$s] ?? null);
        }
    
    }

    private function resolveDevsgerI($pre, $s)
    {

        if ($s !== "\u{1822}") {
            return null;
        }
        if (empty($pre)) {
            return null;
        }
        $c = $pre[count($pre) - 1];
        foreach (FromDelehiMapper::$doubleIEhishig as $c1) {
            if ($c1 == $c) {
                return "\u{e006}\u{e006}";
            }
        }
        return null;
    
    }

    public function contains($s)
    {

        if (isset(FromDelehiMapper::getMapper()[$s])) {
            return true;
        }
        return isset(FromDelehiMapper::getChaghMapper()[$s]);
    
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
