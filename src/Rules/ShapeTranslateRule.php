<?php

namespace Meco\Rules;

interface ShapeTranslateRule
{
    public function isTranslateCodePoint($codePoint);

    public function contains($wordFragment);

    /** @param int[] $preFragmentContent */
    public function getMapperCode($preFragmentContent, $wordFragment);

    public function getCharType($codePoint);
}
