<?php

namespace Meco\Translator;

use Meco\Enums\CharType;
use Meco\Helper\UnicodeHelper;
use Meco\Rules\ShapeTranslateRule;
use Meco\Word\ShapeWord;
use Meco\Word\ShapeWordFragment;

class ShapeTranslator
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
        $len = count($codePoints);

        $word = new ShapeWord();
        $wordFragment = new ShapeWordFragment();
        $wordFragment->head = CharType::OTHER;

        for ($i = 0; $i < $len; $i++) {
            $codePoint = $codePoints[$i];

            if ($this->rule->isTranslateCodePoint($codePoint)) {
                $wordFragment->push($codePoint);
                $nextCp = ($i + 1 < $len) ? $codePoints[$i + 1] : 0;
                $wordFragment->tail = $this->rule->getCharType($nextCp);

                if ($this->rule->contains($wordFragment)) {
                    continue;
                }

                $wordFragment->pop();
                $wordFragment->tail = $this->rule->getCharType($codePoint);

                if ($wordFragment->isBlank()) {
                    continue;
                }

                $word->add($wordFragment);
                $wordFragment = new ShapeWordFragment();
                $prevCp = ($i > 0) ? $codePoints[$i - 1] : 0;
                $wordFragment->head = $this->rule->getCharType($prevCp);
                $i--;
            } else {
                if ($wordFragment->isNotBlank()) {
                    $wordFragment->tail = CharType::OTHER;
                    $word->add($wordFragment);
                    $wordFragment = new ShapeWordFragment();
                    $wordFragment->head = CharType::OTHER;
                }
                if ($word->isNotBlank()) {
                    $this->translateWord($result, $word);
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

    private function translateWord(&$builder, $word)
    {
        $preFragmentContent = [];
        foreach ($word->getWordFragments() as $fragment) {
            $mapped = $this->rule->getMapperCode($preFragmentContent, $fragment);
            if ($mapped !== null) {
                $builder .= $mapped;
            }
            $preFragmentContent = array_merge($preFragmentContent, $fragment->getContent());
        }
    }
}
