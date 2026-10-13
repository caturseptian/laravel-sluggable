<?php

namespace Caturseptian\Sluggable;

use Caturseptian\Sluggable\Exceptions\InvalidOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSlug
{
    /** @var \Caturseptian\Sluggable\SlugOptions */
    protected $slugOptions;

    abstract public function getSlugOptions(): SlugOptions;

    protected static function bootHasSlug()
    {
        static::creating(function (Model $model) {
            $model->generateSlugOnCreate();
        });

        static::updating(function (Model $model) {
            $model->generateSlugOnUpdate();
        });
    }

    protected function generateSlugOnCreate()
    {
        $this->slugOptions = $this->getSlugOptions();

        if (! $this->slugOptions->generateSlugsOnCreate || $this->shouldSkipSlug()) {
            return;
        }

        $this->addSlug();
    }

    protected function generateSlugOnUpdate()
    {
        $this->slugOptions = $this->getSlugOptions();

        if (! $this->slugOptions->generateSlugsOnUpdate || $this->shouldSkipSlug()) {
            return;
        }

        $this->addSlug();
    }

    public function generateSlug()
    {
        $this->slugOptions = $this->getSlugOptions();

        $this->addSlug();
    }

    protected function shouldSkipSlug(): bool
    {
        $slugField = $this->slugOptions->slugField;

        if ($this->slugOptions->preventOverwrite && (string) $this->$slugField !== '') {
            return true;
        }

        return $this->slugOptions->skipGenerate !== null
            && (bool) call_user_func($this->slugOptions->skipGenerate, $this);
    }

    protected function addSlug()
    {
        $this->ensureValidSlugOptions();

        $slug = $this->generateNonUniqueSlug();

        if ($this->slugOptions->generateUniqueSlugs) {
            $slug = $this->makeSlugUnique($slug);
        }

        $slugField = $this->slugOptions->slugField;

        $this->$slugField = $slug;
    }

    protected function generateNonUniqueSlug(): string
    {
        $slugField = $this->slugOptions->slugField;

        if ($this->hasCustomSlugBeenUsed() && ! empty($this->$slugField)) {
            return $this->$slugField;
        }

        return $this->truncateSlug(
            $this->buildSlug($this->getSlugSourceString()),
            $this->slugOptions->maximumLength
        );
    }

    protected function buildSlug(string $source): string
    {
        return Str::slug($source, $this->slugOptions->slugSeparator, $this->slugOptions->slugLanguage);
    }

    protected function truncateSlug(string $slug, int $length): string
    {
        if (strlen($slug) <= $length) {
            return $slug;
        }

        return rtrim(substr($slug, 0, max(0, $length)), $this->slugOptions->slugSeparator);
    }

    protected function hasCustomSlugBeenUsed(): bool
    {
        $slugField = $this->slugOptions->slugField;

        return $this->getOriginal($slugField) != $this->$slugField;
    }

    protected function getSlugSourceString(): string
    {
        if (is_callable($this->slugOptions->generateSlugFrom)) {
            return (string) call_user_func($this->slugOptions->generateSlugFrom, $this);
        }

        return collect($this->slugOptions->generateSlugFrom)
            ->map(function (string $fieldName): string {
                return (string) data_get($this, $fieldName, '');
            })
            ->implode($this->slugOptions->slugSeparator);
    }

    protected function makeSlugUnique(string $slug): string
    {
        $base = $slug;
        $i = $this->slugOptions->startSlugSuffixFrom;

        while ($slug === '' || $this->otherRecordExistsWithSlug($slug)) {
            $suffix = $this->slugOptions->slugSeparator.$i++;
            $slug = $this->truncateSlug($base, $this->slugOptions->maximumLength - strlen($suffix)).$suffix;
        }

        return $slug;
    }

    protected function otherRecordExistsWithSlug(string $slug): bool
    {
        $query = static::where($this->slugOptions->slugField, $slug)
            ->withoutGlobalScopes();

        $key = $this->getKey();

        if ($key !== null) {
            $query->where($this->getKeyName(), '!=', $key);
        }

        if ($this->slugOptions->extraScope !== null) {
            call_user_func($this->slugOptions->extraScope, $query);
        }

        if ($this->usesSoftDeletes()) {
            $query->withTrashed();
        }

        return $query->exists();
    }

    protected function usesSoftDeletes(): bool
    {
        return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($this));
    }

    protected function ensureValidSlugOptions()
    {
        if (is_array($this->slugOptions->generateSlugFrom) && ! count($this->slugOptions->generateSlugFrom)) {
            throw InvalidOption::missingFromField();
        }

        if (! strlen((string) $this->slugOptions->slugField)) {
            throw InvalidOption::missingSlugField();
        }

        if ($this->slugOptions->maximumLength <= 0) {
            throw InvalidOption::invalidMaximumLength();
        }

        if ($this->slugOptions->startSlugSuffixFrom < 1) {
            throw InvalidOption::invalidSuffixStart();
        }
    }
}
