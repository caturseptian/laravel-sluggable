# Changelog

## 1.0.0 - 2026-10-13

Rilis pertama sebagai paket mandiri.

- Namespace baru `Caturseptian\Sluggable`.
- Dukungan Laravel 6–13 dan PHP 7.2–8.5 dalam satu basis kode.
- Aturan slug Indonesia: `expandAbbreviations`, `dropLegalEntity`, `dropStopwords`; helper `Slug::make` dan `Slug::indonesia`.
- Opsi baru: `preventOverwrite`, `skipGenerateWhen`, `extraScope`, `startSlugSuffixFrom`.
- Perintah `php artisan sluggable:eject` untuk melepas aplikasi dari paket.
- Perbaikan: slug ber-suffix tidak lagi melebihi panjang maksimum; model non-incrementing tanpa key tetap mendapat slug unik.

## Sebelum 1.0.0

`dev-master` (2020) adalah fork `spatie/laravel-sluggable` 2.x yang disesuaikan untuk PHP 7.2 dan Laravel 7.
