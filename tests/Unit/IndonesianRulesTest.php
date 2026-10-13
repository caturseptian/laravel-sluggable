<?php

namespace Caturseptian\Sluggable\Tests\Unit;

use Caturseptian\Sluggable\Indonesian\IndonesianRules;
use PHPUnit\Framework\TestCase;

class IndonesianRulesTest extends TestCase
{
    public function test_expand_abbreviations()
    {
        $rules = (new IndonesianRules())->expandAbbreviations();

        $cases = [
            'Kab. Bandung Barat' => 'kabupaten Bandung Barat',
            'Kab Bandung' => 'kabupaten Bandung',
            'Kec. Coblong' => 'kecamatan Coblong',
            'Kel. Dago' => 'kelurahan Dago',
            'Prov. Jawa Barat' => 'provinsi Jawa Barat',
            'Jl. Gatot Subroto No. 10' => 'jalan Gatot Subroto nomor 10',
            'Jln. Merdeka' => 'jalan Merdeka',
            'Jl.Sudirman' => 'jalan Sudirman',
            'Gg. Mawar' => 'gang Mawar',
            'Ds. Sukamaju' => 'desa Sukamaju',
            'Kota Adm. Jakarta Pusat' => 'Kota administrasi Jakarta Pusat',
            'Dr. Sutomo' => 'dokter Sutomo',
            'Ir. Soekarno' => 'insinyur Soekarno',
            'H. Agus Salim' => 'haji Agus Salim',
            'Hj. Siti' => 'hajah Siti',
            'Univ. Indonesia' => 'universitas Indonesia',
            'KAB. BOGOR' => 'kabupaten BOGOR',
        ];

        foreach ($cases as $input => $expected) {
            $this->assertSame($expected, $rules->apply($input), "Masukan: {$input}");
        }
    }

    public function test_expand_abbreviations_leaves_full_words_alone()
    {
        $rules = (new IndonesianRules())->expandAbbreviations();

        $cases = [
            'Kecamatan Kabupaten' => 'Kecamatan Kabupaten',
            'Notaris Haji' => 'Notaris Haji',
            'Nomor Drama' => 'Nomor Drama',
            'No Name' => 'No Name',
            'H Agus' => 'H Agus',
        ];

        foreach ($cases as $input => $expected) {
            $this->assertSame($expected, $rules->apply($input), "Masukan: {$input}");
        }
    }

    public function test_drop_legal_entity()
    {
        $rules = (new IndonesianRules())->dropLegalEntity();

        $cases = [
            'PT Bank Rakyat Indonesia (Persero) Tbk' => 'Bank Rakyat Indonesia',
            'PT. Maju Mundur' => 'Maju Mundur',
            'PT.Maju' => '.Maju',
            'CV Maju Jaya' => 'Maju Jaya',
            'CV. Maju Jaya' => 'Maju Jaya',
            'UD Sumber Rejeki' => 'Sumber Rejeki',
            'Perum Bulog' => 'Bulog',
            'Koperasi Sejahtera' => 'Sejahtera',
            'Kop. Sejahtera' => 'Sejahtera',
            'Yayasan Pendidikan Nusantara' => 'Pendidikan Nusantara',
            'Fa. Sinar' => 'Sinar',
            'Bank Mandiri (Persero) Tbk.' => 'Bank Mandiri',
            'PT' => '',
        ];

        foreach ($cases as $input => $expected) {
            $this->assertSame($expected, $rules->apply($input), "Masukan: {$input}");
        }
    }

    public function test_drop_legal_entity_leaves_words_containing_entities_alone()
    {
        $rules = (new IndonesianRules())->dropLegalEntity();

        $this->assertSame('Ptolemy Cvetkovic Fakultas', $rules->apply('Ptolemy Cvetkovic Fakultas'));
    }

    public function test_drop_stopwords()
    {
        $rules = (new IndonesianRules())->dropStopwords();

        $this->assertSame('Toko Buku Alat Tulis', $rules->apply('Toko Buku dan Alat Tulis'));
        $this->assertSame('Pergi pasar', $rules->apply('Pergi ke pasar'));
        $this->assertSame('Dandang Diam', $rules->apply('Dandang Diam'));
    }

    public function test_drop_stopwords_accepts_extra_words()
    {
        $rules = (new IndonesianRules())->dropStopwords(['para']);

        $this->assertSame('Kumpulan Pemuda', $rules->apply('Kumpulan para Pemuda'));
    }

    public function test_rules_run_in_fixed_order_regardless_of_call_order()
    {
        $rules = (new IndonesianRules())->dropStopwords()->dropLegalEntity()->expandAbbreviations();

        $this->assertSame(
            'kabupaten Bandung Sumber Air',
            $rules->apply('PT Kab. Bandung dan Sumber Air')
        );
    }

    public function test_no_rule_returns_text_with_normalised_spaces()
    {
        $this->assertSame('Apa Adanya', (new IndonesianRules())->apply("  Apa   Adanya \n"));
    }

    public function test_configure_without_callback_expands_abbreviations_only()
    {
        $this->assertSame('kabupaten Bandung PT', IndonesianRules::configure()->apply('Kab. Bandung PT'));
    }

    public function test_configure_uses_callback_result_or_the_passed_instance()
    {
        $returned = IndonesianRules::configure(function ($rules) {
            return $rules->dropLegalEntity();
        });
        $notReturned = IndonesianRules::configure(function ($rules) {
            $rules->dropLegalEntity();
        });

        $this->assertSame('Maju', $returned->apply('PT Maju'));
        $this->assertSame('Maju', $notReturned->apply('PT Maju'));
    }
}
