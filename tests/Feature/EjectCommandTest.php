<?php

namespace Caturseptian\Sluggable\Tests\Feature;

use Caturseptian\Sluggable\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class EjectCommandTest extends TestCase
{
    /** @var string */
    private $root;

    /** @var string */
    private $namespace;

    protected function setUp(): void
    {
        parent::setUp();

        $id = str_replace('.', '', uniqid('', true));
        $this->root = sys_get_temp_dir().'/sluggable-eject-'.$id;
        $this->namespace = 'EjectTest'.$id.'\\Sluggable';

        (new Filesystem())->makeDirectory($this->root.'/app/Models', 0755, true);
        file_put_contents($this->root.'/app/Models/Post.php', $this->fixtureModel('EjectTest'.$id.'\\Models'));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->root);

        parent::tearDown();
    }

    private function fixtureModel(string $namespace): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Caturseptian\Sluggable\{HasSlug, SlugOptions};
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasSlug;

    protected \$table = 'test_models';

    protected \$guarded = [];

    public \$timestamps = false;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('url')
            ->indonesian(function (\$rules) {
                return \$rules->dropLegalEntity();
            });
    }
}
PHP;
    }

    private function eject(array $extra = []): int
    {
        return Artisan::call('sluggable:eject', array_merge([
            '--path' => $this->root.'/app/Support/Sluggable',
            '--namespace' => $this->namespace,
            '--scan' => [$this->root.'/app'],
        ], $extra));
    }

    public function test_it_copies_the_library_with_the_new_namespace_and_header()
    {
        $this->assertSame(0, $this->eject());

        $target = $this->root.'/app/Support/Sluggable';
        foreach (['HasSlug.php', 'SlugOptions.php', 'Slug.php', 'Indonesian/Dictionary.php', 'Indonesian/IndonesianRules.php', 'Exceptions/InvalidOption.php'] as $file) {
            $this->assertFileExists($target.'/'.$file);
            $contents = file_get_contents($target.'/'.$file);
            $this->assertStringNotContainsString('Caturseptian\\Sluggable', $contents, $file);
            $this->assertStringContainsString('Berasal dari caturseptian/laravel-sluggable', $contents, $file);
        }

        $this->assertStringContainsString('namespace '.$this->namespace.';', file_get_contents($target.'/HasSlug.php'));
        $this->assertFileExists($target.'/LICENSE-sluggable');
        $this->assertPathMissing($target.'/SluggableServiceProvider.php');
        $this->assertPathMissing($target.'/Console/EjectCommand.php');
    }

    public function test_it_rewrites_grouped_imports_in_scanned_code()
    {
        $this->eject();

        $model = file_get_contents($this->root.'/app/Models/Post.php');

        $this->assertStringContainsString('use '.$this->namespace.'\\{HasSlug, SlugOptions};', $model);
        $this->assertStringNotContainsString('Caturseptian\\Sluggable', $model);
    }

    public function test_dry_run_writes_nothing()
    {
        $before = file_get_contents($this->root.'/app/Models/Post.php');

        $this->assertSame(0, $this->eject(['--dry-run' => true]));

        $this->assertPathMissing($this->root.'/app/Support/Sluggable');
        $this->assertSame($before, file_get_contents($this->root.'/app/Models/Post.php'));
        $this->assertStringContainsString('app/Models/Post.php', Artisan::output());
    }

    public function test_second_run_is_refused_without_force_and_writes_nothing()
    {
        $this->eject();
        file_put_contents($this->root.'/app/Support/Sluggable/Slug.php', 'local edit');

        $this->assertSame(1, $this->eject());
        $this->assertSame('local edit', file_get_contents($this->root.'/app/Support/Sluggable/Slug.php'));

        $this->assertSame(0, $this->eject(['--force' => true]));
        $this->assertNotSame('local edit', file_get_contents($this->root.'/app/Support/Sluggable/Slug.php'));
    }

    public function test_ejected_code_works_without_the_package_namespace()
    {
        $this->eject();

        $prefix = $this->namespace.'\\';
        $base = $this->root.'/app/Support/Sluggable/';
        spl_autoload_register(function ($class) use ($prefix, $base) {
            if (strpos($class, $prefix) === 0) {
                require $base.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
            }
        });
        require $this->root.'/app/Models/Post.php';

        $modelClass = str_replace('\\Sluggable', '\\Models\\Post', $this->namespace);
        $first = $modelClass::create(['name' => 'PT Maju Jaya']);
        $second = $modelClass::create(['name' => 'CV Maju Jaya']);

        $this->assertSame('maju-jaya', $first->url);
        $this->assertSame('maju-jaya-1', $second->url);
        $this->assertContains($this->namespace.'\\HasSlug', class_uses($first));
    }
}
