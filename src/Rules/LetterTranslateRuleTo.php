<?php

namespace Meco\Rules;

interface LetterTranslateRuleTo
{
    public function getMapperCode(&$builder, $zvvnModWord);

    public function contains($key);

    public function isTranslateCodePoint($codePoint);
}
