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
    ];

    /**
     * Singkatan yang hanya dikenali bila diikuti titik dan tidak menempel ke singkatan lain
     * (rawan bentrok dengan kata biasa atau gelar seperti S.H./M.H.). "Dr." sengaja tidak
     * dipanjangkan karena bisa berarti doktor maupun dokter.
     */
    const DOTTED_ABBREVIATIONS = [
        'no' => 'nomor',
        'gg' => 'gang',
        'ds' => 'desa',
        'adm' => 'administrasi',
        'ir' => 'insinyur',
        'h' => 'haji',
        'hj' => 'hajah',
        'univ' => 'universitas',
    ];

    /** Penanda badan usaha di awal/akhir nama, dengan atau tanpa titik. */
    const LEGAL_ENTITIES = [
        'pt', 'cv', 'ud', 'tbk', 'persero', 'perum', 'koperasi', 'yayasan', 'firma',
    ];

    /** Penanda badan usaha di awal/akhir nama yang hanya dikenali bila diikuti titik. */
    const DOTTED_LEGAL_ENTITIES = [
        'kop', 'fa',
    ];

    const STOPWORDS = [
        'dan', 'di', 'ke', 'dari', 'yang', 'untuk', 'pada', 'atau', 'dengan', 'serta',
    ];
}
