<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\Database\LayananHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Layanan>
 */
class LayananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $layanan = [
            [
                'nama' => 'Cuci Kering',
                'tipe' => 'per_kg',
                'harga_kg' => 5000,
                'harga_satuan' => null,
                'satuan' => 'kg',
                'durasi' => 24,
                'deskripsi' => 'Cuci dan kering saja',
                'popular' => true,
                'icon' => null,
                'include' => ['Cuci', 'Kering'],
                'exclude' => ['Setrika'],
            ],
            [
                'nama' => 'Cuci Setrika',
                'tipe' => 'per_kg',
                'harga_kg' => 7000,
                'harga_satuan' => null,
                'satuan' => 'kg',
                'durasi' => 48,
                'deskripsi' => 'Cuci, kering, dan setrika rapi',
                'popular' => true,
                'icon' => null,
                'include' => ['Cuci', 'Kering', 'Setrika', 'Lipat'],
                'exclude' => [],
            ],
            [
                'nama' => 'Setrika Saja',
                'tipe' => 'per_kg',
                'harga_kg' => 4000,
                'harga_satuan' => null,
                'satuan' => 'kg',
                'durasi' => 12,
                'deskripsi' => 'Setrika saja tanpa cuci',
                'popular' => false,
                'icon' => null,
                'include' => ['Setrika', 'Lipat'],
                'exclude' => [],
            ],
            [
                'nama' => 'Express 6 Jam',
                'tipe' => 'per_kg',
                'harga_kg' => 12000,
                'harga_satuan' => null,
                'satuan' => 'kg',
                'durasi' => 6,
                'deskripsi' => 'Layanan kilat 6 jam jadi',
                'popular' => true,
                'icon' => null,
                'include' => ['Cuci', 'Kering', 'Setrika', 'Lipat'],
                'exclude' => [],
            ],
            [
                'nama' => 'Express 12 Jam',
                'tipe' => 'per_kg',
                'harga_kg' => 10000,
                'harga_satuan' => null,
                'satuan' => 'kg',
                'durasi' => 12,
                'deskripsi' => 'Layanan kilat 12 jam jadi',
                'popular' => false,
                'icon' => null,
                'include' => ['Cuci', 'Kering', 'Setrika', 'Lipat'],
                'exclude' => [],
            ],
            [
                'nama' => 'Cuci Karpet',
                'tipe' => 'per_satuan',
                'harga_kg' => null,
                'harga_satuan' => 150000,
                'satuan' => 'pcs',
                'durasi' => 72,
                'deskripsi' => 'Cuci karpet dan permadani per keping',
                'popular' => false,
                'icon' => null,
                'min_order' => 1,
                'include' => [],
                'exclude' => [],
            ],
            [
                'nama' => 'Cuci Sepatu',
                'tipe' => 'per_satuan',
                'harga_kg' => null,
                'harga_satuan' => 25000,
                'satuan' => 'pasang',
                'durasi' => 48,
                'deskripsi' => 'Cuci sepatu sneakers per pasang',
                'popular' => true,
                'icon' => null,
                'min_order' => 1,
                'include' => [],
                'exclude' => [],
            ],
            [
                'nama' => 'Cuci Boneka',
                'tipe' => 'per_satuan',
                'harga_kg' => null,
                'harga_satuan' => 20000,
                'satuan' => 'pcs',
                'durasi' => 24,
                'deskripsi' => 'Cuci boneka dan mainan per item',
                'popular' => false,
                'icon' => null,
                'max_order' => 10,
                'include' => [],
                'exclude' => [],
            ],
        ];

        static $index = 0;

        $item = $layanan[$index % count($layanan)];
        $index++;

        return [
            'kode_layanan' => LayananHelper::generateKodeLayanan(),
            'nama_layanan' => $item['nama'],
            'tipe_layanan' => $item['tipe'],
            'satuan' => $item['satuan'],
            'harga_per_kg' => $item['harga_kg'] ?? 0,
            'harga_per_satuan' => $item['harga_satuan'],
            'durasi_jam' => $item['durasi'],
            'deskripsi' => $item['deskripsi'],
            'status' => 'Aktif',
            'min_order' => $item['min_order'] ?? null,
            'max_order' => $item['max_order'] ?? null,
            'is_popular' => $item['popular'],
            'icon' => $item['icon'],
            'include' => $item['include'],
            'exclude' => $item['exclude'],
        ];
    }
}
