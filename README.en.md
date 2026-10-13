# Laravel Sluggable

[![tests](https://github.com/caturseptian/laravel-sluggable/actions/workflows/tests.yml/badge.svg)](https://github.com/caturseptian/laravel-sluggable/actions/workflows/tests.yml)
[![Packagist](https://img.shields.io/packagist/v/caturseptian/laravel-sluggable.svg)](https://packagist.org/packages/caturseptian/laravel-sluggable)

[Bahasa Indonesia](README.md)

Generates slugs automatically when an Eloquent model is saved, with rules that understand Indonesian.

- **Understands Indonesian** — `PT Bank Rakyat Indonesia (Persero) Tbk` → `bank-rakyat-indonesia`, `Kab. Bandung Barat` → `kabupaten-bandung-barat`.
- **Laravel 6 through 13** in one package (PHP 7.2–8.5).
- **Can run dependency-free** — install it during development, run `php artisan sluggable:eject`, then remove the package.

## Installation

### Dependent mode (package stays installed)

```bash
composer require caturseptian/laravel-sluggable
```

> Install it under `require`, **not** `require-dev`, unless you are going to eject. Without ejecting,
> `composer install --no-dev` in production makes the app fail with *class not found*.

### Eject mode (no dependency)

```bash
composer require --dev caturseptian/laravel-sluggable
php artisan sluggable:eject --dry-run   # list the files that will be created/changed
php artisan sluggable:eject
composer remove caturseptian/laravel-sluggable
```

Eject copies the code to `app/Support/Sluggable` (namespace `App\Support\Sluggable`), rewrites
`Caturseptian\Sluggable\...` imports in the `app/` folder, and includes `LICENSE-sluggable`.
Options: `--path=`, `--namespace=`, `--scan=` (repeatable, e.g. `--scan=app --scan=database`),
`--dry-run`, `--force`.

## Usage

```php
use Caturseptian\Sluggable\HasSlug;
use Caturseptian\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('nama')
            ->saveSlugsTo('slug');
    }
}
```

Migration: `$table->string('slug')->unique();`

### Options

| Method | Description |
|---|---|
| `generateSlugsFrom('judul')` / `(['judul', 'kota'])` / `(function ($model) { ... })` | slug source |
| `saveSlugsTo('slug')` | target column |
| `allowDuplicateSlugs()` | no uniqueness suffix |
| `slugsShouldBeNoLongerThan(100)` | maximum length (default 250, suffix included) |
| `usingSeparator('_')` | separator (default `-`) |
| `usingLanguage('de')` | `Str::slug` transliteration language (default `en`) |
| `doNotGenerateSlugsOnCreate()` / `doNotGenerateSlugsOnUpdate()` | disable on a given event |
| `preventOverwrite()` | never overwrite a slug that is already filled |
| `skipGenerateWhen(function ($model) { return ...; })` | skip under a condition |
| `extraScope(function ($query) { $query->where('tenant_id', 1); })` | scoped uniqueness |
| `startSlugSuffixFrom(2)` | first suffix (default 1) |
| `indonesian()` / `indonesian(function ($rules) { ... })` | Indonesian rules |

### Indonesian rules

```php
SlugOptions::create()
    ->generateSlugsFrom('nama')
    ->saveSlugsTo('slug')
    ->indonesian(function ($rules) {
        return $rules
            ->expandAbbreviations()   // Kab. → kabupaten, Jl. → jalan, No. → nomor, ...
            ->dropLegalEntity()       // PT, CV, UD, Tbk, (Persero), Perum, Koperasi, Yayasan, ...
            ->dropStopwords();        // dan, di, ke, dari, yang, untuk, ...
    });
```

`indonesian()` without an argument only expands abbreviations. When the rules remove the whole text
(e.g. a name that is just `PT`), the slug is built from the original text (`pt`).

| Input | Rule | Slug |
|---|---|---|
| PT Bank Rakyat Indonesia (Persero) Tbk | dropLegalEntity | `bank-rakyat-indonesia` |
| Kab. Bandung Barat | expandAbbreviations | `kabupaten-bandung-barat` |
| Jl. Gatot Subroto No. 10 | expandAbbreviations | `jalan-gatot-subroto-nomor-10` |
| Toko Buku dan Alat Tulis | dropStopwords | `toko-buku-alat-tulis` |

The full list lives in `src/Indonesian/Dictionary.php`.

### Outside a model

```php
use Caturseptian\Sluggable\Slug;

Slug::make('Judul Artikel');            // judul-artikel
Slug::indonesia('Kab. Bandung Barat');  // kabupaten-bandung-barat
```

`Slug` does not query the database, so it does not guarantee uniqueness.

## Compatibility

| Laravel | PHP |
|---|---|
| 6, 7 | 7.2 / 7.3 – 8.0 |
| 8 | 7.3 – 8.1 |
| 9 | 8.0 – 8.2 |
| 10 | 8.1 – 8.3 |
| 11, 12 | 8.2 – 8.4 |
| 13 | 8.3 – 8.5 |

Every row is tested in CI.

## Moving from spatie/laravel-sluggable

`SlugOptions` method names are the same. Change `use Spatie\Sluggable\...` to
`use Caturseptian\Sluggable\...`. Spatie v4 features such as *self-healing* URLs, translatable
slugs (`HasTranslatableSlug`), and the `#[Sluggable]` attribute are not available here —
if you need them, use [spatie/laravel-sluggable](https://github.com/spatie/laravel-sluggable).

## Testing

```bash
composer test
```

## Credits

Started from [spatie/laravel-sluggable](https://github.com/spatie/laravel-sluggable) by
Freek Van der Herten and Spatie. Developed and maintained by [Tian](https://github.com/caturseptian).

## License

MIT. See [LICENSE.md](LICENSE.md).
