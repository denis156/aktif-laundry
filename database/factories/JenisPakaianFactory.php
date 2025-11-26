<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\Database\JenisPakaianHelper;
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
        static $jenisPakaian = [
            ['nama' => 'Kemeja', 'keterangan' => 'Kemeja lengan panjang dan pendek', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Celana Panjang', 'keterangan' => 'Celana panjang reguler', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Celana Pendek', 'keterangan' => 'Celana pendek dan training', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Kaos', 'keterangan' => 'Kaos oblong dan t-shirt', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Rok', 'keterangan' => 'Rok panjang dan pendek', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Dress', 'keterangan' => 'Gaun dan terusan', 'penanganan_khusus' => 'Hati-hati dengan aksesoris', 'icon' => null],
            ['nama' => 'Jaket', 'keterangan' => 'Jaket dan hoodie', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Jas', 'keterangan' => 'Jas formal', 'penanganan_khusus' => 'Gunakan laundry khusus jas', 'icon' => null],
            ['nama' => 'Kebaya', 'keterangan' => 'Kebaya dan pakaian adat', 'penanganan_khusus' => 'Hati-hati dengan bordir dan payet', 'icon' => null],
            ['nama' => 'Handuk', 'keterangan' => 'Handuk mandi', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Sprei', 'keterangan' => 'Sprei dan bed cover', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Selimut', 'keterangan' => 'Selimut dan blanket', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Sarung', 'keterangan' => 'Sarung dan sarung bantal', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Gordyn', 'keterangan' => 'Gorden jendela', 'penanganan_khusus' => null, 'icon' => null],
            ['nama' => 'Mukena', 'keterangan' => 'Mukena dan perlengkapan sholat', 'penanganan_khusus' => 'Hati-hati dengan bordir', 'icon' => null],
        ];

        static $index = 0;

        $item = $jenisPakaian[$index % count($jenisPakaian)];
        $index++;

        return [
            'kode_jenis' => JenisPakaianHelper::generateKodeJenis(),
            'nama_jenis' => $item['nama'],
            'keterangan' => $item['keterangan'],
            'status' => 'Aktif',
            'penanganan_khusus' => $item['penanganan_khusus'],
            'icon' => $item['icon'],
        ];
    }
}
