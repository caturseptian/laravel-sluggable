<?php

namespace Caturseptian\Sluggable\Indonesian;

class Dictionary
{
    /** Singkatan yang dikenali dengan atau tanpa titik. */
    const ABBREVIATIONS = [
        'kab' => 'kabupaten',
        'kec' => 'kecamatan',
        'kel' => 'kelurahan',
        'prov' => 'provinsi',
        'jl' => 'jalan',
        'jln' => 'jalan',
        'gg' => 'gang',
    ];

    /** Singkatan yang hanya dikenali bila diikuti titik (rawan bentrok dengan kata biasa). */
    const DOTTED_ABBREVIATIONS = [
        'no' => 'nomor',
        'ds' => 'desa',
        'adm' => 'administrasi',
        'dr' => 'dokter',
        'ir' => 'insinyur',
        'h' => 'haji',
        'hj' => 'hajah',
        'univ' => 'universitas',
    ];

    /** Penanda badan usaha, dengan atau tanpa titik. */
    const LEGAL_ENTITIES = [
        'pt', 'cv', 'ud', 'tbk', 'persero', 'perum', 'koperasi', 'yayasan', 'firma',
    ];

    /** Penanda badan usaha yang hanya dikenali bila diikuti titik. */
    const DOTTED_LEGAL_ENTITIES = [
        'kop', 'fa',
    ];

    const STOPWORDS = [
        'dan', 'di', 'ke', 'dari', 'yang', 'untuk', 'pada', 'atau', 'dengan', 'serta',
    ];
}
