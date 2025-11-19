<?php

declare(strict_types=1);

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
            ['nama' => 'Kemeja', 'keterangan' => 'Kemeja lengan panjang dan pendek', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Celana Panjang', 'keterangan' => 'Celana panjang reguler', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Celana Pendek', 'keterangan' => 'Celana pendek dan training', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Kaos', 'keterangan' => 'Kaos oblong dan t-shirt', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Rok', 'keterangan' => 'Rok panjang dan pendek', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Dress', 'keterangan' => 'Gaun dan terusan', 'metadata' => ['penanganan_khusus' => true]],
            ['nama' => 'Jaket', 'keterangan' => 'Jaket dan hoodie', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Jas', 'keterangan' => 'Jas formal', 'metadata' => ['penanganan_khusus' => true]],
            ['nama' => 'Kebaya', 'keterangan' => 'Kebaya dan pakaian adat', 'metadata' => ['penanganan_khusus' => true]],
            ['nama' => 'Handuk', 'keterangan' => 'Handuk mandi', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Sprei', 'keterangan' => 'Sprei dan bed cover', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Selimut', 'keterangan' => 'Selimut dan blanket', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Sarung', 'keterangan' => 'Sarung dan sarung bantal', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Gordyn', 'keterangan' => 'Gorden jendela', 'metadata' => ['penanganan_khusus' => false]],
            ['nama' => 'Mukena', 'keterangan' => 'Mukena dan perlengkapan sholat', 'metadata' => ['penanganan_khusus' => true]],
        ];

        $index = ($counter - 1) % count($jenisPakaian);
        $item = $jenisPakaian[$index];

        return [
            'kode_jenis' => 'JNS' . str_pad((string) $counter++, 3, '0', STR_PAD_LEFT),
            'nama_jenis' => $item['nama'],
            'keterangan' => $item['keterangan'],
            'status' => 'Aktif',
            'metadata' => $item['metadata'],
        ];
    }
}
