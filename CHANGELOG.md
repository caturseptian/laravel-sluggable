# Changelog

## 1.0.2 - 2026-10-14

- Slug unik tidak lagi diam-diam melebihi `slugsShouldBeNoLongerThan` saat batasnya lebih pendek dari suffix (mis. batas 1 dengan suffix `-1`); kini dilempar `InvalidOption`.
- `sluggable:eject` atomik: semua file ditulis ke lokasi sementara dulu dan baru dipindahkan bila semuanya berhasil, sehingga galat tulis tidak meninggalkan aplikasi setengah ter-eject.

## 1.0.1 - 2026-10-14

- `dropStopwords` tidak lagi membuang bentuk bertanda hubung: "HUT RI Ke-80" tetap `hut-ri-ke-80`.
- `dropLegalEntity` tidak lagi membuang "Perum" karena juga berarti perumahan ("Perum Griya Asri").
- `sluggable:eject` menolak `--namespace` yang tidak valid (mis. memakai `/` atau backslash yang hilang karena tidak dikutip) tanpa menulis apa pun.
- `README.en.md` ikut terpasang sehingga tautan bahasa di README berfungsi di `vendor/`.

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
