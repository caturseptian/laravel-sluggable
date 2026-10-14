# Laravel Sluggable

[![tests](https://github.com/caturseptian/laravel-sluggable/actions/workflows/tests.yml/badge.svg)](https://github.com/caturseptian/laravel-sluggable/actions/workflows/tests.yml)
[![Packagist Version](https://img.shields.io/packagist/v/caturseptian/laravel-sluggable)](https://packagist.org/packages/caturseptian/laravel-sluggable)
[![Packagist Downloads](https://img.shields.io/packagist/dt/caturseptian/laravel-sluggable)](https://packagist.org/packages/caturseptian/laravel-sluggable)
![PHP](https://img.shields.io/badge/PHP-7.2%20to%208.5-777bb4)
![Laravel](https://img.shields.io/badge/Laravel-6%20to%2013-ff2d20)
![License](https://img.shields.io/badge/license-MIT-blue)
[![Ko-fi](https://img.shields.io/badge/Ko--fi-support-ff5e5b?logo=ko-fi&logoColor=white)](https://ko-fi.com/caturseptian)
[![Trakteer](https://img.shields.io/badge/Trakteer-dukung-c0392b)](https://trakteer.id/caturseptian)

[English](README.en.md)

Membuat slug otomatis saat model Eloquent disimpan, dengan aturan yang paham bahasa Indonesia.

- **Paham Indonesia** — `PT Bank Rakyat Indonesia (Persero) Tbk` → `bank-rakyat-indonesia`, `Kab. Bandung Barat` → `kabupaten-bandung-barat`.
- **Laravel 6 sampai 13** dalam satu paket (PHP 7.2–8.5).
- **Bisa tanpa ketergantungan** — pasang saat pengembangan, `php artisan sluggable:eject`, lalu hapus paketnya.

## Instalasi

### Mode dependent (paket tetap dipasang)

```bash
composer require caturseptian/laravel-sluggable
```

> Pasang di `require`, **bukan** `require-dev`, kecuali Anda akan melakukan eject. Tanpa eject,
> `composer install --no-dev` di production membuat aplikasi galat *class not found*.

### Mode eject (tanpa ketergantungan)

```bash
composer require --dev caturseptian/laravel-sluggable
php artisan sluggable:eject --dry-run   # lihat file yang akan dibuat/diubah
php artisan sluggable:eject
composer remove caturseptian/laravel-sluggable
```

Eject menyalin kode ke `app/Support/Sluggable` (namespace `App\Support\Sluggable`), mengganti
rujukan `Caturseptian\Sluggable` di folder `app`, `config`, `database`, `routes`, dan `tests`, lalu
menyertakan `LICENSE-sluggable`. Rujukan di luar folder itu tidak diubah, tetapi ditampilkan sebagai
peringatan. Opsi: `--path=`, `--namespace=` (beri tanda kutip, mis. `--namespace="Domain\Slug"`),
`--scan=` (bisa berulang), `--dry-run`, `--force`.

## Pemakaian

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

Migrasi: `$table->string('slug')->unique();`

### Opsi

| Metode | Keterangan |
|---|---|
| `generateSlugsFrom('judul')` / `(['judul', 'kota'])` / `(function ($model) { ... })` | sumber slug |
| `saveSlugsTo('slug')` | kolom tujuan |
| `allowDuplicateSlugs()` | tanpa suffix keunikan |
| `slugsShouldBeNoLongerThan(100)` | panjang maksimum (default 250, termasuk suffix) |
| `usingSeparator('_')` | pemisah (default `-`) |
| `usingLanguage('de')` | bahasa transliterasi `Str::slug` (default `en`) |
| `doNotGenerateSlugsOnCreate()` / `doNotGenerateSlugsOnUpdate()` | matikan pada event tertentu |
| `preventOverwrite()` | jangan pernah membuat ulang slug yang sudah terisi (slug manual tidak dicek keunikannya) |
| `skipGenerateWhen(function ($model) { return ...; })` | lewati kondisi tertentu |
| `extraScope(function ($query) { $query->where('tenant_id', 1); })` | keunikan per lingkup |
| `startSlugSuffixFrom(2)` | suffix pertama (default 1) |
| `indonesian()` / `indonesian(function ($rules) { ... })` | aturan Indonesia |

### Aturan Indonesia

```php
SlugOptions::create()
    ->generateSlugsFrom('nama')
    ->saveSlugsTo('slug')
    ->indonesian(function ($rules) {
        return $rules
            ->expandAbbreviations()   // Kab. → kabupaten, Jl. → jalan, No. → nomor, ...
            ->dropLegalEntity()       // PT, CV, UD, Tbk, (Persero), Koperasi, Yayasan, ...
            ->dropStopwords();        // dan, di, ke, dari, yang, untuk, ...
    });
```

`indonesian()` tanpa argumen hanya memperluas singkatan. Bila aturan menghapus seluruh teks
(mis. nama hanya `PT`), slug dibuat dari teks asli (`pt`).

| Masukan | Aturan | Slug |
|---|---|---|
| PT Bank Rakyat Indonesia (Persero) Tbk | dropLegalEntity | `bank-rakyat-indonesia` |
| Kab. Bandung Barat | expandAbbreviations | `kabupaten-bandung-barat` |
| Jl. Gatot Subroto No. 10 | expandAbbreviations | `jalan-gatot-subroto-nomor-10` |
| Toko Buku dan Alat Tulis | dropStopwords | `toko-buku-alat-tulis` |

Daftar lengkap ada di `src/Indonesian/Dictionary.php`.

### Di luar model

```php
use Caturseptian\Sluggable\Slug;

Slug::make('Judul Artikel');            // judul-artikel
Slug::indonesia('Kab. Bandung Barat');  // kabupaten-bandung-barat
```

`Slug` tidak memeriksa database, jadi tidak menjamin keunikan.

## Kompatibilitas

| Laravel | PHP |
|---|---|
| 6, 7 | 7.2 / 7.3 – 8.0 |
| 8 | 7.3 – 8.1 |
| 9 | 8.0 – 8.2 |
| 10 | 8.1 – 8.3 |
| 11, 12 | 8.2 – 8.4 |
| 13 | 8.3 – 8.5 |

Setiap baris diuji di CI.

## Pindah dari spatie/laravel-sluggable

Nama metode `SlugOptions` sama. Ganti `use Spatie\Sluggable\...` menjadi
`use Caturseptian\Sluggable\...`. Fitur Spatie v4 seperti URL *self-healing*, slug
multibahasa (`HasTranslatableSlug`), dan atribut `#[Sluggable]` tidak tersedia di sini —
bila Anda membutuhkannya, pakai [spatie/laravel-sluggable](https://github.com/spatie/laravel-sluggable).

## Pengujian

```bash
composer test
```

## Kredit

Berawal dari [spatie/laravel-sluggable](https://github.com/spatie/laravel-sluggable) karya
Freek Van der Herten dan Spatie. Dikembangkan dan dirawat oleh [Tian](https://github.com/caturseptian).

## Lisensi

MIT. Lihat [LICENSE.md](LICENSE.md).
