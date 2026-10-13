<?php

namespace Caturseptian\Sluggable;

use Caturseptian\Sluggable\Indonesian\IndonesianRules;
use Illuminate\Support\Str;

class Slug
{
    /**
     * Buat slug; bila aturan Indonesia membuat hasilnya kosong, pakai teks asli.
     */
    public static function make(string $text, string $separator = '-', string $language = 'en', ?IndonesianRules $rules = null): string
    {
        if ($rules !== null) {
            $slug = Str::slug($rules->apply($text), $separator, $language);

            if ($slug !== '') {
                return $slug;
            }
        }

        return Str::slug($text, $separator, $language);
    }

    public static function indonesia(string $text, ?callable $configure = null, string $separator = '-', string $language = 'en'): string
    {
        return static::make($text, $separator, $language, IndonesianRules::configure($configure));
    }
}
