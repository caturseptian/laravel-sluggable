<?php

namespace Caturseptian\Sluggable\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [\Caturseptian\Sluggable\SluggableServiceProvider::class];
    }

    /**
     * Pengganti assertFileDoesNotExist (baru ada di PHPUnit 9.1).
     */
    protected function assertPathMissing(string $path, string $message = '')
    {
        $this->assertFalse(file_exists($path), $message ?: "{$path} seharusnya tidak ada");
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();

        $schema->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('other_field')->nullable();
            $table->string('url')->nullable();
            $table->integer('tenant_id')->nullable();
        });

        $schema->create('test_model_soft_deletes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('other_field')->nullable();
            $table->string('url')->nullable();
            $table->softDeletes();
        });

        $schema->create('test_uuid_models', function (Blueprint $table) {
            $table->string('id')->nullable();
            $table->string('name')->nullable();
            $table->string('url')->nullable();
        });
    }
}
