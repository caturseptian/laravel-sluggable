<?php

namespace Caturseptian\Sluggable\Indonesian;

class IndonesianRules
{
    const NOT_WORD_BEFORE = '(?<![\p{L}\p{N}])';

    const NOT_WORD_AFTER = '(?![\p{L}\p{N}])';

    /** @var bool */
    private $expandAbbreviations = false;

    /** @var bool */
    private $dropLegalEntity = false;

    /** @var string[]|null */
    private $stopwords;

    public static function configure(?callable $configure = null): self
    {
        $rules = new static();

        if ($configure === null) {
            return $rules->expandAbbreviations();
        }

        $result = call_user_func($configure, $rules);

        return $result instanceof self ? $result : $rules;
    }

    public function expandAbbreviations(): self
    {
        $this->expandAbbreviations = true;

        return $this;
    }

    public function dropLegalEntity(): self
    {
        $this->dropLegalEntity = true;

        return $this;
    }

    public function dropStopwords(array $extra = []): self
    {
        $this->stopwords = array_merge(Dictionary::STOPWORDS, array_map('mb_strtolower', $extra));

        return $this;
    }

    public function apply(string $text): string
    {
        if (! preg_match('//u', $text)) {
            return $text;
        }

        if ($this->expandAbbreviations) {
            $text = $this->replaceWords(Dictionary::ABBREVIATIONS, self::NOT_WORD_BEFORE, '(?:\.|'.self::NOT_WORD_AFTER.')', $text);
            $text = $this->replaceWords(Dictionary::DOTTED_ABBREVIATIONS, '(?<![\p{L}\p{N}.])', '\.', $text);
        }

        if ($this->dropLegalEntity) {
            $text = preg_replace('/\(\s*(?:'.$this->alternation(Dictionary::LEGAL_ENTITIES).')\s*\.?\s*\)/iu', ' ', $text);
            $text = $this->trimEntities($text);
        }

        if ($this->stopwords !== null) {
            $pattern = '/'.self::NOT_WORD_BEFORE.'(?:'.$this->alternation($this->stopwords).')'.self::NOT_WORD_AFTER.'/iu';
            $text = preg_replace($pattern, ' ', $text);
        }

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private function replaceWords(array $map, string $before, string $ending, string $text): string
    {
        $pattern = '/'.$before.'('.$this->alternation(array_keys($map)).')'.$ending.'/iu';

        return preg_replace_callback($pattern, function (array $match) use ($map) {
            return ' '.$map[mb_strtolower($match[1])].' ';
        }, $text);
    }

    /**
     * Buang satu penanda badan usaha di awal dan satu di akhir nama ("PT ... Tbk").
     */
    private function trimEntities(string $text): string
    {
        $entity = '(?:(?:'.$this->alternation(Dictionary::LEGAL_ENTITIES).')\.?'
            .'|(?:'.$this->alternation(Dictionary::DOTTED_LEGAL_ENTITIES).')\.)';
        $leading = '/^\s*'.$entity.self::NOT_WORD_AFTER.'/iu';
        $trailing = '/'.self::NOT_WORD_BEFORE.$entity.'\s*$/iu';

        return preg_replace([$leading, $trailing], ' ', $text);
    }

    /**
     * Kata terpanjang lebih dulu agar "jln" tidak kalah oleh "jl".
     */
    private function alternation(array $words): string
    {
        usort($words, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        return implode('|', array_map(function ($word) {
            return preg_quote($word, '/');
        }, $words));
    }
}
