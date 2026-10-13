<?php

namespace Caturseptian\Sluggable\Exceptions;

use Exception;

class InvalidOption extends Exception
{
    public static function missingFromField()
    {
        return new static('Could not determine which fields should be sluggified');
    }

    public static function missingSlugField()
    {
        return new static('Could not determine in which field the slug should be saved');
    }

    public static function invalidMaximumLength()
    {
        return new static('Maximum length should be greater than zero');
    }

    public static function invalidSuffixStart()
    {
        return new static('Slug suffix should start from one or higher');
    }
}
