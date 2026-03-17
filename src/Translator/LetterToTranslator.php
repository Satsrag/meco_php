<?php

namespace Meco\Translator;

use Meco\Helper\UnicodeHelper;
use Meco\Rules\LetterTranslateRuleTo;
use Meco\Word\ShapeWord;
use Meco\Word\ShapeWordFragment;

class LetterToTranslator
{
    private $rule;

    public function __construct($rule)
    {
        $this->rule = $rule;
    }

    public function translate($text)
    {
        if ($text === '') {
            return '';
        }

        $result = '';
        $codePoints = UnicodeHelper::toCodePoints($text);
        $codePoints[] = 0xE666;

        $wordFragment = new ShapeWordFragment();
        $word = new ShapeWord();

        foreach ($codePoints as $codePoint) {
            if ($this->rule->isTranslateCodePoint($codePoint)) {
                $wordFragment->push($codePoint);
                if ($this->rule->contains($wordFragment->getKey())) {
                    continue;
                }
                $wordFragment->pop();
                if ($wordFragment->isBlank()) {
                    continue;
                }
                $word->add($wordFragment);
                $wordFragment = new ShapeWordFragment();
                $wordFragment->push($codePoint);
            } else {
                if ($wordFragment->isNotBlank()) {
                    $word->add($wordFragment);
                    $wordFragment = new ShapeWordFragment();
                }
                if ($word->isNotBlank()) {
                    $this->rule->getMapperCode($result, $word);
                    $word = new ShapeWord();
                }
                $result .= mb_chr($codePoint);
            }
        }

        if ($result !== '') {
            return mb_substr($result, 0, mb_strlen($result) - 1);
        }
        return $result;
    }
}
