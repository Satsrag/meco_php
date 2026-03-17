<?php

namespace Meco\Word;

use Meco\Enums\Nature;

class ShapeWord
{
    private $nature;

    /** @var ShapeWordFragment[] */
    private $wordFragments = [];

    public function __construct()
    {
        $this->nature = Nature::CHAGH;
    }

    public function getNature()
    {
        return $this->nature;
    }

    /** @return ShapeWordFragment[] */
    public function getWordFragments()
    {
        return $this->wordFragments;
    }

    public function add($fragment)
    {
        $key = $fragment->getKey();
        if ($key === "\u{E006}\u{E00D}" || $key === "\u{E031}" || $key === "\u{E006}\u{E006}\u{E00D}") {
            $this->nature = Nature::HUNDII;
        }
        $this->wordFragments[] = $fragment;
    }

    public function isBlank()
    {
        return empty($this->wordFragments);
    }

    public function isNotBlank()
    {
        return !empty($this->wordFragments);
    }
}
