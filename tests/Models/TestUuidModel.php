<?php

namespace Caturseptian\Sluggable\Tests\Models;

use Caturseptian\Sluggable\HasSlug;
use Caturseptian\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;

class TestUuidModel extends Model
{
    use HasSlug;

    protected $table = 'test_uuid_models';

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('url');
    }
}
