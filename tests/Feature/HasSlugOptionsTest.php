<?php

namespace Caturseptian\Sluggable\Tests\Feature;

use Caturseptian\Sluggable\Exceptions\InvalidOption;
use Caturseptian\Sluggable\SlugOptions;
use Caturseptian\Sluggable\Tests\Models\TestModel;
use Caturseptian\Sluggable\Tests\Models\TestUuidModel;
use Caturseptian\Sluggable\Tests\TestCase;

class HasSlugOptionsTest extends TestCase
{
    public function test_prevent_overwrite_keeps_a_manually_filled_slug()
    {
        $model = (new TestModel())->setSlugOptions(
            SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('url')->preventOverwrite()
        );
        $model->name = 'judul baru';
        $model->url = 'slug-manual';
        $model->save();

        $this->assertEquals('slug-manual', $model->url);

        $model->name = 'judul lain';
        $model->save();

        $this->assertEquals('slug-manual', $model->url);
    }

    public function test_skip_generate_when_receives_the_model()
    {
        $model = (new TestModel())->setSlugOptions(
            SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('url')
                ->skipGenerateWhen(function ($model) {
                    return $model->other_field === 'draft';
                })
        );
        $model->name = 'judul';
        $model->other_field = 'draft';
        $model->save();

        $this->assertNull($model->url);
    }

    public function test_extra_scope_limits_the_uniqueness_check()
    {
        $options = function () {
            return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('url')
                ->extraScope(function ($query) {
                    $query->where('tenant_id', 1);
                });
        };

        TestModel::create(['name' => 'sama', 'tenant_id' => 2]);

        $model = (new TestModel())->setSlugOptions($options());
        $model->name = 'sama';
        $model->tenant_id = 1;
        $model->save();

        $this->assertEquals('sama', $model->url);
    }

    public function test_suffix_can_start_from_a_custom_number()
    {
        TestModel::create(['name' => 'judul']);

        $model = (new TestModel())->setSlugOptions(
            SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('url')->startSlugSuffixFrom(2)
        );
        $model->name = 'judul';
        $model->save();

        $this->assertEquals('judul-2', $model->url);
    }

    public function test_suffix_start_below_one_is_rejected()
    {
        $this->expectException(InvalidOption::class);

        $model = (new TestModel())->setSlugOptions(
            SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('url')->startSlugSuffixFrom(0)
        );
        $model->name = 'judul';
        $model->save();
    }

    public function test_slug_with_suffix_never_exceeds_maximum_length()
    {
        $options = function () {
            return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('url')
                ->slugsShouldBeNoLongerThan(10);
        };

        $first = (new TestModel())->setSlugOptions($options());
        $first->name = 'abcdefghijkl';
        $first->save();

        $second = (new TestModel())->setSlugOptions($options());
        $second->name = 'abcdefghijkl';
        $second->save();

        $this->assertEquals('abcdefghij', $first->url);
        $this->assertEquals('abcdefgh-1', $second->url);
    }

    public function test_truncation_does_not_leave_a_trailing_separator()
    {
        $model = (new TestModel())->setSlugOptions(
            SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('url')->slugsShouldBeNoLongerThan(6)
        );
        $model->name = 'abcde fgh';
        $model->save();

        $this->assertEquals('abcde', $model->url);
    }

    public function test_non_incrementing_model_without_key_still_gets_unique_slug()
    {
        TestUuidModel::create(['name' => 'judul']);
        $second = TestUuidModel::create(['name' => 'judul']);

        $this->assertEquals('judul-1', $second->url);
    }
}
