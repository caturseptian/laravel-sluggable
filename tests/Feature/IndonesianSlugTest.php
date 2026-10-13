<?php

namespace Caturseptian\Sluggable\Tests\Feature;

use Caturseptian\Sluggable\SlugOptions;
use Caturseptian\Sluggable\Tests\Models\TestModel;
use Caturseptian\Sluggable\Tests\TestCase;

class IndonesianSlugTest extends TestCase
{
    private function model(?callable $configure = null): TestModel
    {
        return (new TestModel())->setSlugOptions(
            SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('url')->indonesian($configure)
        );
    }

    public function test_model_uses_indonesian_rules()
    {
        $model = $this->model(function ($rules) {
            return $rules->dropLegalEntity()->expandAbbreviations();
        });
        $model->name = 'PT Kab. Bandung Sejahtera Tbk';
        $model->save();

        $this->assertEquals('kabupaten-bandung-sejahtera', $model->url);
    }

    public function test_indonesian_without_callback_expands_abbreviations()
    {
        $model = $this->model();
        $model->name = 'Kec. Coblong';
        $model->save();

        $this->assertEquals('kecamatan-coblong', $model->url);
    }

    public function test_model_falls_back_to_plain_slug_instead_of_suffix_only()
    {
        $model = $this->model(function ($rules) {
            return $rules->dropLegalEntity();
        });
        $model->name = 'PT';
        $model->save();

        $this->assertEquals('pt', $model->url);
    }

    public function test_indonesian_slugs_are_still_unique()
    {
        $configure = function ($rules) {
            return $rules->dropLegalEntity();
        };

        $first = $this->model($configure);
        $first->name = 'PT Maju';
        $first->save();

        $second = $this->model($configure);
        $second->name = 'CV Maju';
        $second->save();

        $this->assertEquals('maju', $first->url);
        $this->assertEquals('maju-1', $second->url);
    }
}
