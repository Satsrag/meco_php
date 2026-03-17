<?php

namespace Meco\Rules;

interface LetterTranslateRuleFrom
{
    /** @return string|null */
    public function getMapperCode($pre, $suf, $s, $nature);

    /** @return bool */
    public function contains($s);

    /** @return string Nature constant */
    public function getCodeNature($codePoint);

    /** @return bool */
    public function isTranslateCodePoint($codePoint);

    /** @return bool */
    public function isWordCodePoint($codePoint);
}
