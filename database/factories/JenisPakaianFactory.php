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
            ['nama' => 'Kemeja', 'keterangan' => 'Kemeja lengan panjang dan pendek', 'penanganan_khusus' => null, 'icon' => 'shirt'],
            ['nama' => 'Celana Panjang', 'keterangan' => 'Celana panjang reguler', 'penanganan_khusus' => null, 'icon' => 'pants'],
            ['nama' => 'Celana Pendek', 'keterangan' => 'Celana pendek dan training', 'penanganan_khusus' => null, 'icon' => 'shorts'],
            ['nama' => 'Kaos', 'keterangan' => 'Kaos oblong dan t-shirt', 'penanganan_khusus' => null, 'icon' => 'tshirt'],
            ['nama' => 'Rok', 'keterangan' => 'Rok panjang dan pendek', 'penanganan_khusus' => null, 'icon' => 'skirt'],
            ['nama' => 'Dress', 'keterangan' => 'Gaun dan terusan', 'penanganan_khusus' => 'Hati-hati dengan aksesoris', 'icon' => 'dress'],
            ['nama' => 'Jaket', 'keterangan' => 'Jaket dan hoodie', 'penanganan_khusus' => null, 'icon' => 'jacket'],
            ['nama' => 'Jas', 'keterangan' => 'Jas formal', 'penanganan_khusus' => 'Gunakan laundry khusus jas', 'icon' => 'suit'],
            ['nama' => 'Kebaya', 'keterangan' => 'Kebaya dan pakaian adat', 'penanganan_khusus' => 'Hati-hati dengan bordir dan payet', 'icon' => 'kebaya'],
            ['nama' => 'Handuk', 'keterangan' => 'Handuk mandi', 'penanganan_khusus' => null, 'icon' => 'towel'],
            ['nama' => 'Sprei', 'keterangan' => 'Sprei dan bed cover', 'penanganan_khusus' => null, 'icon' => 'bedsheet'],
            ['nama' => 'Selimut', 'keterangan' => 'Selimut dan blanket', 'penanganan_khusus' => null, 'icon' => 'blanket'],
            ['nama' => 'Sarung', 'keterangan' => 'Sarung dan sarung bantal', 'penanganan_khusus' => null, 'icon' => 'sarung'],
            ['nama' => 'Gordyn', 'keterangan' => 'Gorden jendela', 'penanganan_khusus' => null, 'icon' => 'curtain'],
            ['nama' => 'Mukena', 'keterangan' => 'Mukena dan perlengkapan sholat', 'penanganan_khusus' => 'Hati-hati dengan bordir', 'icon' => 'prayer-clothes'],
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
