<?php

namespace Meco\Word;

use Meco\Enums\CharType;
use Meco\Enums\Nature;
use Meco\Unicode\MglUnicode;

class LetterWord
{
    private $nature;
    /** @var LetterWordFragment[] */
    private $fragments = [];

    public function __construct()
    {
        $this->nature = Nature::SAARMAG;
    }

    public function getNature()
    {
        return $this->nature;
    }

    /** @return LetterWordFragment[] */
    public function getFragments()
    {
        return $this->fragments;
    }

    public function add($fragment)
    {
        if ($this->nature === Nature::SAARMAG && $fragment->getNature() !== Nature::SAARMAG) {
            $this->nature = $fragment->getNature();
        }
        $this->fragments[] = $fragment;
    }

    public function isBlank()
    {
        return empty($this->fragments);
    }

    public function isNotBlank()
    {
        return !empty($this->fragments);
    }

    public function removeInvalidCodePointFromEnd()
    {
        if (empty($this->fragments)) {
            return;
        }
        for ($i = count($this->fragments) - 1; $i > 0; $i--) {
            $frag = $this->fragments[$i];
            if ($frag->size() !== 1) {
                return;
            }
            $ch = $frag->getContent()[0];
            if (!MglUnicode::isVowelSeparator($ch) && !MglUnicode::isFreeVariationSelector($ch)) {
                return;
            }
            $preFrag = $this->fragments[$i - 1];
            if ($preFrag->getLastCharacter() === $ch) {
                array_splice($this->fragments, $i, 1);
                $this->fragments[$i - 1]->tail = CharType::OTHER;
            }
        }
    }
}
