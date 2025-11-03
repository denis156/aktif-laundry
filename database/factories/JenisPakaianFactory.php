<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JenisPakaian>
 */
class JenisPakaianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        $jenisPakaian = [
            ['nama' => 'Kemeja', 'keterangan' => 'Kemeja lengan panjang dan pendek'],
            ['nama' => 'Celana Panjang', 'keterangan' => 'Celana panjang reguler'],
            ['nama' => 'Celana Pendek', 'keterangan' => 'Celana pendek dan training'],
            ['nama' => 'Kaos', 'keterangan' => 'Kaos oblong dan t-shirt'],
            ['nama' => 'Rok', 'keterangan' => 'Rok panjang dan pendek'],
            ['nama' => 'Dress', 'keterangan' => 'Gaun dan terusan'],
            ['nama' => 'Jaket', 'keterangan' => 'Jaket dan hoodie'],
            ['nama' => 'Jas', 'keterangan' => 'Jas formal'],
            ['nama' => 'Kebaya', 'keterangan' => 'Kebaya dan pakaian adat'],
            ['nama' => 'Handuk', 'keterangan' => 'Handuk mandi'],
            ['nama' => 'Sprei', 'keterangan' => 'Sprei dan bed cover'],
            ['nama' => 'Selimut', 'keterangan' => 'Selimut dan blanket'],
            ['nama' => 'Sarung', 'keterangan' => 'Sarung dan sarung bantal'],
            ['nama' => 'Gordyn', 'keterangan' => 'Gorden jendela'],
            ['nama' => 'Mukena', 'keterangan' => 'Mukena dan perlengkapan sholat'],
        ];

        $index = ($counter - 1) % count($jenisPakaian);
        $item = $jenisPakaian[$index];

        return [
            'kode_jenis' => 'JNS' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
            'nama_jenis' => $item['nama'],
            'keterangan' => $item['keterangan'],
            'status' => 'Aktif',
        ];
    }
}
