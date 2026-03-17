<?php

namespace Meco\Translator;

use Meco\Enums\CharType;
use Meco\Enums\Nature;
use Meco\Exception\MecoException;
use Meco\Helper\UnicodeHelper;
use Meco\Rules\LetterTranslateRuleFrom;
use Meco\Word\LetterWord;
use Meco\Word\LetterWordFragment;

class LetterFromTranslator
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

        $codePoints = UnicodeHelper::toCodePoints($text);
        $codePoints[] = 0xE666;
        $builder = '';

        $letterWordFragment = new LetterWordFragment();
        $letterWordFragment->head = CharType::OTHER;
        $letterWord = new LetterWord();

        for ($i = 0; $i < count($codePoints); $i++) {
            $c = $codePoints[$i];
            if ($this->rule->isWordCodePoint($c)) {
                $letterWordFragment->push($c);
                $letterWordFragment->tail = $this->getUnicodeType($codePoints[$i + 1]);
                if ($this->rule->contains($letterWordFragment->getKey())) {
                    $letterWordFragment->setNature($this->rule->getCodeNature($c));
                    continue;
                }
                $letterWordFragment->pop();
                $letterWordFragment->tail = $this->getUnicodeType($c);
                if ($letterWordFragment->isBlank()) {
                    throw new MecoException('Not fount the string [' . mb_chr($c, 'UTF-8') . '] in mapper rule');
                }
                $letterWord->add($letterWordFragment);
                $letterWordFragment = new LetterWordFragment();
                $letterWordFragment->head = $this->getUnicodeType($codePoints[$i - 1]);
                $i--;
            } else {
                if ($letterWordFragment->isNotBlank()) {
                    $letterWordFragment->tail = CharType::OTHER;
                    $letterWord->add($letterWordFragment);
                    $letterWordFragment = new LetterWordFragment();
                    $letterWordFragment->head = CharType::OTHER;
                }
                if ($letterWord->isNotBlank()) {
                    $builder .= $this->translateWord($letterWord);
                    $letterWord = new LetterWord();
                }
                if ($this->rule->isTranslateCodePoint($c)) {
                    $letterWordFragment->push($c);
                } else {
                    $builder .= mb_chr($c, 'UTF-8');
                }
            }
        }

        return mb_substr($builder, 0, -1, 'UTF-8');
    }

    private function translateWord($word)
    {
        $result = '';
        $fragments = $word->getFragments();
        $pre = [];

        for ($i = 0; $i < count($fragments); $i++) {
            $fragment = $fragments[$i];
            $nature = $fragment->getNature() === Nature::SAARMAG
                ? $word->getNature() : $fragment->getNature();

            $sufInts = ($i + 1 < count($fragments))
                ? $fragments[$i + 1]->getContent() : [];
            $suf = array_map('mb_chr', $sufInts);

            $key = $fragment->getKey();
            $preChars = array_map('mb_chr', $pre);
            $mappedCode = $this->rule->getMapperCode($preChars, $suf, $key, $nature);

            if ($mappedCode === null) {
                throw new MecoException(
                    'Not fount the string ' . UnicodeHelper::fromCodePoints($fragment->getContent()) . ' in mapper rule'
                );
            }
            $result .= $mappedCode;
            $pre = array_merge($pre, $fragment->getContent());
        }

        return $result;
    }

    private function getUnicodeType($codePoint)
    {
        return $this->rule->isWordCodePoint($codePoint) ? CharType::MONGOLIAN : CharType::OTHER;
    }
}
