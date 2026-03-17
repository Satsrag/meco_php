<?php

namespace Meco\Word;

use Meco\Enums\CharType;
use Meco\Enums\Nature;
use Meco\Exception\MecoException;
use Meco\Helper\UnicodeHelper;

class LetterWordFragment
{
    /** @var string|null CharType constant */
    public $head = null;
    /** @var string|null CharType constant */
    public $tail = null;
    /** @var int[] */
    private $content = [];
    /** @var string Nature constant */
    private $nature;

    public function __construct()
    {
        $this->nature = Nature::SAARMAG;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getNature()
    {
        return $this->nature;
    }

    public function setNature($value)
    {
        if ($this->nature === Nature::SAARMAG && $value !== Nature::SAARMAG) {
            $this->nature = $value;
        }
    }

    public function getKey()
    {
        if (empty($this->content)) {
            return '';
        }
        if ($this->head === null || $this->tail === null) {
            throw new MecoException('CharType cannot be null');
        }
        $s = '';
        if ($this->head !== CharType::MONGOLIAN) {
            $s .= ' ';
        }
        $s .= UnicodeHelper::fromCodePoints($this->content);
        if ($this->tail !== CharType::MONGOLIAN) {
            $s .= ' ';
        }
        return $s;
    }

    public function push($codePoint)
    {
        $this->content[] = $codePoint;
    }

    public function pop()
    {
        if (empty($this->content)) {
            throw new MecoException('Nothing to pop');
        }
        array_pop($this->content);
    }

    public function isBlank()
    {
        return empty($this->content);
    }

    public function isNotBlank()
    {
        return !empty($this->content);
    }

    public function size()
    {
        return count($this->content);
    }

    public function getLastCharacter()
    {
        if (empty($this->content)) {
            return null;
        }
        return $this->content[count($this->content) - 1];
    }
}
