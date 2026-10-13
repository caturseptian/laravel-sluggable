<?php

namespace Caturseptian\Sluggable\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class EjectCommand extends Command
{
    const SOURCE_NAMESPACE = 'Caturseptian\\Sluggable';

    const PACKAGE = 'caturseptian/laravel-sluggable';

    const FILES = [
        'HasSlug.php',
        'SlugOptions.php',
        'Slug.php',
        'Indonesian/Dictionary.php',
        'Indonesian/IndonesianRules.php',
        'Exceptions/InvalidOption.php',
    ];

    protected $signature = 'sluggable:eject
        {--path= : Folder tujuan (default app/Support/Sluggable)}
        {--namespace= : Namespace tujuan (default <App>\\Support\\Sluggable)}
        {--scan=* : Folder yang import-nya ikut diganti (default app)}
        {--dry-run : Tampilkan rencana tanpa menulis apa pun}
        {--force : Timpa folder tujuan yang sudah ada}';

    protected $description = 'Salin kode sluggable ke aplikasi agar paket bisa dihapus';

    public function handle(Filesystem $files)
    {
        $namespace = trim($this->option('namespace') ?: $this->laravel->getNamespace().'Support\\Sluggable', '\\');
        $target = $this->absolute($this->option('path') ?: 'app/Support/Sluggable');
        $scan = $this->option('scan') ?: ['app'];
        $dryRun = (bool) $this->option('dry-run');

        if ($files->exists($target) && ! $this->option('force')) {
            $this->error("Folder {$target} sudah ada. Pakai --force untuk menimpanya.");

            return 1;
        }

        $writes = [];
        $header = $this->header();
        $source = dirname(__DIR__);

        foreach (self::FILES as $file) {
            $contents = $this->rewrite($files->get($source.'/'.$file), $namespace);
            $writes[$target.'/'.$file] = preg_replace('/^<\?php\s*/', "<?php\n\n".$header."\n", $contents, 1);
        }

        $writes[$target.'/LICENSE-sluggable'] = $files->get(dirname($source).'/LICENSE.md');

        $changed = [];
        foreach ($scan as $directory) {
            $directory = $this->absolute($directory);
            if (! $files->isDirectory($directory)) {
                continue;
            }
            foreach ($files->allFiles($directory) as $file) {
                $path = $file->getPathname();
                if ($file->getExtension() !== 'php' || strpos($path, $target.DIRECTORY_SEPARATOR) === 0) {
                    continue;
                }
                $contents = $files->get($path);
                if (strpos($contents, self::SOURCE_NAMESPACE.'\\') !== false) {
                    $changed[$path] = $this->rewrite($contents, $namespace);
                }
            }
        }

        foreach (array_keys($writes) as $path) {
            $this->line(($dryRun ? '[dry-run] ' : '').'buat: '.$path);
        }
        foreach (array_keys($changed) as $path) {
            $this->line(($dryRun ? '[dry-run] ' : '').'ubah: '.$path);
        }

        if ($dryRun) {
            return 0;
        }

        foreach ($writes + $changed as $path => $contents) {
            if (! $files->isDirectory(dirname($path))) {
                $files->makeDirectory(dirname($path), 0755, true);
            }
            if ($files->put($path, $contents) === false) {
                $this->error("Gagal menulis {$path}");

                return 1;
            }
        }

        $this->info("Selesai. Kode sluggable sekarang ada di {$target} ({$namespace}).");
        $this->line('Langkah terakhir: composer remove '.self::PACKAGE);

        return 0;
    }

    private function rewrite(string $contents, string $namespace): string
    {
        return str_replace(self::SOURCE_NAMESPACE, $namespace, $contents);
    }

    private function absolute(string $path): string
    {
        $path = rtrim($path, '/\\');

        return preg_match('#^([a-zA-Z]:)?[/\\\\]#', $path) ? $path : $this->laravel->basePath($path);
    }

    private function header(): string
    {
        $version = 'dev';
        if (class_exists('Composer\\InstalledVersions') && \Composer\InstalledVersions::isInstalled(self::PACKAGE)) {
            $version = (string) \Composer\InstalledVersions::getPrettyVersion(self::PACKAGE);
        }

        return "/*\n * Berasal dari ".self::PACKAGE." {$version} (MIT).\n"
            ." * Hak cipta (c) Spatie bvba dan Catur Septian. Lihat LICENSE-sluggable.\n */\n";
    }
}
