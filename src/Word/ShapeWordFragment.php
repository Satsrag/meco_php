<?php

namespace Meco\Word;

use Meco\Enums\CharType;
use Meco\Exception\MecoException;
use Meco\Helper\UnicodeHelper;

class ShapeWordFragment
{
    /** @var string|null */
    public $head = null;
    /** @var string|null */
    public $tail = null;
    /** @var int[] */
    private $content = [];

    /** @return int[] */
    public function getContent()
    {
        return $this->content;
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

    public function getKey()
    {
        if (empty($this->content)) {
            return '';
        }
        return UnicodeHelper::fromCodePoints($this->content);
    }

    public function getLocateKey()
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
}
