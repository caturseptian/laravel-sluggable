<?php

namespace Caturseptian\Sluggable\Tests\Unit;

use Caturseptian\Sluggable\Slug;
use Caturseptian\Sluggable\Tests\TestCase;

class SlugTest extends TestCase
{
    public function test_make_is_a_plain_slug()
    {
        $this->assertSame('judul-artikel', Slug::make('Judul Artikel'));
        $this->assertSame('judul_artikel', Slug::make('Judul Artikel', '_'));
    }

    public function test_indonesia_contract_table()
    {
        $dropEntity = function ($rules) {
            return $rules->dropLegalEntity();
        };
        $dropStopwords = function ($rules) {
            return $rules->dropStopwords();
        };

        $this->assertSame('bank-rakyat-indonesia', Slug::indonesia('PT Bank Rakyat Indonesia (Persero) Tbk', $dropEntity));
        $this->assertSame('kabupaten-bandung-barat', Slug::indonesia('Kab. Bandung Barat'));
        $this->assertSame('jalan-gatot-subroto-nomor-10', Slug::indonesia('Jl. Gatot Subroto No. 10'));
        $this->assertSame('maju-jaya', Slug::indonesia('CV Maju Jaya', $dropEntity));
        $this->assertSame('maju', Slug::indonesia('PT.Maju', $dropEntity));
        $this->assertSame('toko-buku-alat-tulis', Slug::indonesia('Toko Buku dan Alat Tulis', $dropStopwords));
        $this->assertSame('kecamatan-kabupaten', Slug::indonesia('Kecamatan Kabupaten'));
    }

    public function test_indonesia_falls_back_when_rules_remove_everything()
    {
        $this->assertSame('pt', Slug::indonesia('PT', function ($rules) {
            return $rules->dropLegalEntity();
        }));
        $this->assertSame('persero', Slug::indonesia('(Persero)', function ($rules) {
            return $rules->dropLegalEntity();
        }));
    }
}
