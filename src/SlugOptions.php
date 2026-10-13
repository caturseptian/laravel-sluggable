<?php

namespace Caturseptian\Sluggable;

use Caturseptian\Sluggable\Indonesian\IndonesianRules;

class SlugOptions
{
    /** @var array|callable */
    public $generateSlugFrom;

    /** @var string */
    public $slugField = '';

    /** @var bool */
    public $generateUniqueSlugs = true;

    /** @var int */
    public $maximumLength = 250;

    /** @var bool */
    public $generateSlugsOnCreate = true;

    /** @var bool */
    public $generateSlugsOnUpdate = true;

    /** @var bool */
    public $preventOverwrite = false;

    /** @var string */
    public $slugSeparator = '-';

    /** @var string */
    public $slugLanguage = 'en';

    /** @var callable|null */
    public $skipGenerate;

    /** @var callable|null */
    public $extraScope;

    /** @var int */
    public $startSlugSuffixFrom = 1;

    /** @var \Caturseptian\Sluggable\Indonesian\IndonesianRules|null */
    public $indonesianRules;

    public static function create(): self
    {
        return new static();
    }

    /**
     * @param string|array|callable $fieldName
     */
    public function generateSlugsFrom($fieldName): self
    {
        if (is_string($fieldName)) {
            $fieldName = [$fieldName];
        }

        $this->generateSlugFrom = $fieldName;

        return $this;
    }

    public function saveSlugsTo(string $fieldName): self
    {
        $this->slugField = $fieldName;

        return $this;
    }

    public function allowDuplicateSlugs(): self
    {
        $this->generateUniqueSlugs = false;

        return $this;
    }

    public function slugsShouldBeNoLongerThan(int $maximumLength): self
    {
        $this->maximumLength = $maximumLength;

        return $this;
    }

    public function doNotGenerateSlugsOnCreate(): self
    {
        $this->generateSlugsOnCreate = false;

        return $this;
    }

    public function doNotGenerateSlugsOnUpdate(): self
    {
        $this->generateSlugsOnUpdate = false;

        return $this;
    }

    public function preventOverwrite(): self
    {
        $this->preventOverwrite = true;

        return $this;
    }

    public function usingSeparator(string $separator): self
    {
        $this->slugSeparator = $separator;

        return $this;
    }

    public function usingLanguage(string $language): self
    {
        $this->slugLanguage = $language;

        return $this;
    }

    public function skipGenerateWhen(callable $callback): self
    {
        $this->skipGenerate = $callback;

        return $this;
    }

    public function extraScope(callable $callback): self
    {
        $this->extraScope = $callback;

        return $this;
    }

    public function startSlugSuffixFrom(int $start): self
    {
        $this->startSlugSuffixFrom = $start;

        return $this;
    }
}
