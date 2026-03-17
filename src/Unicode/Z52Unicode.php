<?php

namespace Meco\Unicode;

use Meco\Rules\Z52\Z52UnicodeBlock;

class Z52Unicode
{
    public static function isZ52Code($cp)
    {
        return Z52UnicodeBlock::isZ52Code($cp);
    }

    public static function isZ52Punctuation($cp)
    {
        return Z52UnicodeBlock::isZ52Punctuation($cp);
    }
}
