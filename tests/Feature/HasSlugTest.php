<?php

namespace Caturseptian\Sluggable\Tests\Feature;

use Caturseptian\Sluggable\SlugOptions;
use Caturseptian\Sluggable\Tests\Models\TestModel;
use Caturseptian\Sluggable\Tests\Models\TestModelSoftDeletes;
use Caturseptian\Sluggable\Tests\TestCase;
use Illuminate\Support\Str;

class HasSlugTest extends TestCase
{
    public function test_it_will_save_a_slug_when_saving_a_model()
    {
        $model = TestModel::create(['name' => 'this is a test']);

        $this->assertEquals('this-is-a-test', $model->url);
    }

    public function test_it_can_handle_null_values_when_creating_slugs()
    {
        $model = TestModel::create(['name' => null]);

        $this->assertEquals('-1', $model->url);
    }

    public function test_it_will_not_change_the_slug_when_the_source_field_is_not_changed()
    {
        $model = TestModel::create(['name' => 'this is a test']);

        $model->other_field = 'otherValue';
        $model->save();

        $this->assertEquals('this-is-a-test', $model->url);
    }

    public function test_it_will_use_the_source_field_if_the_slug_field_is_empty()
    {
        $model = TestModel::create(['name' => 'this is a test']);

        $model->url = null;
        $model->save();

        $this->assertEquals('this-is-a-test', $model->url);
    }

    public function test_it_will_update_the_slug_when_the_source_field_is_changed()
    {
        $model = TestModel::create(['name' => 'this is a test']);

        $model->name = 'this is another test';
        $model->save();

        $this->assertEquals('this-is-another-test', $model->url);
    }

    public function test_it_will_save_a_unique_slug_by_default()
    {
        TestModel::create(['name' => 'this is a test']);

        foreach (range(1, 10) as $i) {
            $model = TestModel::create(['name' => 'this is a test']);
            $this->assertEquals("this-is-a-test-{$i}", $model->url);
        }
    }

    public function test_it_can_handle_empty_source_fields()
    {
        foreach (range(1, 10) as $i) {
            $model = TestModel::create(['name' => '']);
            $this->assertEquals("-{$i}", $model->url);
        }
    }

    public function test_it_can_generate_slugs_from_multiple_source_fields()
    {
        $model = new class extends TestModel {
            public function getSlugOptions(): SlugOptions
            {
                return parent::getSlugOptions()->generateSlugsFrom(['name', 'other_field']);
            }
        };

        $model->name = 'this is a test';
        $model->other_field = 'this is another field';
        $model->save();

        $this->assertEquals('this-is-a-test-this-is-another-field', $model->url);
    }

    public function test_it_can_generate_slugs_from_a_callable()
    {
        $model = new class extends TestModel {
            public function getSlugOptions(): SlugOptions
            {
                return parent::getSlugOptions()->generateSlugsFrom(function (TestModel $model): string {
                    return 'foo-'.Str::slug($model->name);
                });
            }
        };

        $model->name = 'this is a test';
        $model->save();

        $this->assertEquals('foo-this-is-a-test', $model->url);
    }

    public function test_it_can_generate_duplicate_slugs()
    {
        foreach (range(1, 10) as $i) {
            $model = new class extends TestModel {
                public function getSlugOptions(): SlugOptions
                {
                    return parent::getSlugOptions()->allowDuplicateSlugs();
                }
            };

            $model->name = 'this is a test';
            $model->save();

            $this->assertEquals('this-is-a-test', $model->url);
        }
    }

    public function test_it_can_generate_slugs_with_a_maximum_length()
    {
        $model = new class extends TestModel {
            public function getSlugOptions(): SlugOptions
            {
                return parent::getSlugOptions()->slugsShouldBeNoLongerThan(5);
            }
        };

        $model->name = '123456789';
        $model->save();

        $this->assertEquals('12345', $model->url);
    }

    public function test_it_can_handle_weird_characters_when_generating_the_slug()
    {
        $cases = [
            'é' => 'e',
            'è' => 'e',
            'à' => 'a',
            'ß' => 'ss',
            'a/ ' => 'a',
        ];

        foreach ($cases as $weird => $normal) {
            TestModel::query()->delete();

            $model = TestModel::create(['name' => $weird]);

            $this->assertEquals($normal, $model->url, "Masukan: {$weird}");
        }
    }

    public function test_it_transliterates_currency_symbols()
    {
        // Transliterasi € milik Str::slug berbeda antarversi Laravel: "a" (6), "a-euro" (7–8), "aeur" (versi baru).
        $model = TestModel::create(['name' => 'a€']);

        $this->assertSame(1, preg_match('/^a(-?euro?)?$/', $model->url), "Slug: {$model->url}");
    }

    public function test_it_can_handle_overwrites_when_updating_a_model()
    {
        $model = TestModel::create(['name' => 'this is a test']);

        $model->url = 'this-is-an-url';
        $model->save();

        $this->assertEquals('this-is-an-url', $model->url);
    }

    public function test_it_can_handle_duplicates_when_overwriting_a_slug()
    {
        $model = TestModel::create(['name' => 'this is a test']);
        $otherModel = TestModel::create(['name' => 'this is an other']);

        $model->url = 'this-is-an-other';
        $model->save();

        $this->assertEquals('this-is-an-other-1', $model->url);
    }

    public function test_it_has_an_method_that_prevents_a_slug_being_generated_on_creation()
    {
        $model = new class extends TestModel {
            public function getSlugOptions(): SlugOptions
            {
                return parent::getSlugOptions()->doNotGenerateSlugsOnCreate();
            }
        };

        $model->name = 'this is a test';
        $model->save();

        $this->assertEquals(null, $model->url);
    }

    public function test_it_has_an_method_that_prevents_a_slug_being_generated_on_update()
    {
        $model = new class extends TestModel {
            public function getSlugOptions(): SlugOptions
            {
                return parent::getSlugOptions()->doNotGenerateSlugsOnUpdate();
            }
        };

        $model->name = 'this is a test';
        $model->save();

        $model->name = 'this is another test';
        $model->save();

        $this->assertEquals('this-is-a-test', $model->url);
    }

    public function test_it_will_use_separator_option_for_slug_generation()
    {
        $model = new class extends TestModel {
            public function getSlugOptions(): SlugOptions
            {
                return parent::getSlugOptions()->usingSeparator('_');
            }
        };

        $model->name = 'this is a separator test';
        $model->save();

        $this->assertEquals('this_is_a_separator_test', $model->url);
    }

    public function test_it_will_use_language_option_for_slug_generation()
    {
        $model = new class extends TestModel {
            public function getSlugOptions(): SlugOptions
            {
                return parent::getSlugOptions()->usingLanguage('nl');
            }
        };

        $this->assertEquals('nl', $model->getSlugOptions()->slugLanguage);
    }

    public function test_it_can_generate_language_specific_slugs()
    {
        $model = new class extends TestModel {
            public function getSlugOptions(): SlugOptions
            {
                return parent::getSlugOptions()->usingLanguage('en');
            }
        };

        $model->name = 'Güte nacht';
        $model->save();
        $this->assertEquals('gute-nacht', $model->url);

        $model = new class extends TestModel {
            public function getSlugOptions(): SlugOptions
            {
                return parent::getSlugOptions()->usingLanguage('de');
            }
        };

        $model->name = 'Güte nacht';
        $model->save();
        $this->assertEquals('guete-nacht', $model->url);
    }

    public function test_it_will_save_a_unique_slug_by_default_even_when_soft_deletes_are_on()
    {
        TestModelSoftDeletes::create(['name' => 'this is a test', 'deleted_at' => date('Y-m-d h:i:s')]);

        foreach (range(1, 10) as $i) {
            $model = TestModelSoftDeletes::create(['name' => 'this is a test']);
            $this->assertEquals("this-is-a-test-{$i}", $model->url);
        }
    }
}
