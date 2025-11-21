<?php

declare(strict_types=1);

namespace Database\Factories;

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
        static $counter = 1;

        $layanan = [
            [
                'nama' => 'Cuci Kering',
                'tipe' => 'per_kg',
                'harga_kg' => 5000,
                'harga_satuan' => 0,
                'satuan' => 'kg',
                'durasi' => 24,
                'deskripsi' => 'Cuci dan kering saja',
                'metadata' => ['popular' => true, 'icon' => 'washing-machine'],
            ],
            [
                'nama' => 'Cuci Setrika',
                'tipe' => 'per_kg',
                'harga_kg' => 7000,
                'harga_satuan' => 0,
                'satuan' => 'kg',
                'durasi' => 48,
                'deskripsi' => 'Cuci, kering, dan setrika rapi',
                'metadata' => ['popular' => true, 'icon' => 'iron'],
            ],
            [
                'nama' => 'Setrika Saja',
                'tipe' => 'per_kg',
                'harga_kg' => 4000,
                'harga_satuan' => 0,
                'satuan' => 'kg',
                'durasi' => 12,
                'deskripsi' => 'Setrika saja tanpa cuci',
                'metadata' => ['popular' => false, 'icon' => 'iron'],
            ],
            [
                'nama' => 'Express 6 Jam',
                'tipe' => 'per_kg',
                'harga_kg' => 12000,
                'harga_satuan' => 0,
                'satuan' => 'kg',
                'durasi' => 6,
                'deskripsi' => 'Layanan kilat 6 jam jadi',
                'metadata' => ['popular' => true, 'icon' => 'fast-forward'],
            ],
            [
                'nama' => 'Express 12 Jam',
                'tipe' => 'per_kg',
                'harga_kg' => 10000,
                'harga_satuan' => 0,
                'satuan' => 'kg',
                'durasi' => 12,
                'deskripsi' => 'Layanan kilat 12 jam jadi',
                'metadata' => ['popular' => false, 'icon' => 'clock'],
            ],
            [
                'nama' => 'Cuci Karpet',
                'tipe' => 'per_satuan',
                'harga_kg' => 0,
                'harga_satuan' => 150000,
                'satuan' => 'pcs',
                'durasi' => 72,
                'deskripsi' => 'Cuci karpet dan permadani per keping',
                'metadata' => ['popular' => false, 'icon' => 'carpet', 'min_order' => 1],
            ],
            [
                'nama' => 'Cuci Sepatu',
                'tipe' => 'per_satuan',
                'harga_kg' => 0,
                'harga_satuan' => 25000,
                'satuan' => 'pasang',
                'durasi' => 48,
                'deskripsi' => 'Cuci sepatu sneakers per pasang',
                'metadata' => ['popular' => true, 'icon' => 'shoe', 'min_order' => 1],
            ],
            [
                'nama' => 'Cuci Boneka',
                'tipe' => 'per_satuan',
                'harga_kg' => 0,
                'harga_satuan' => 20000,
                'satuan' => 'pcs',
                'durasi' => 24,
                'deskripsi' => 'Cuci boneka dan mainan per item',
                'metadata' => ['popular' => false, 'icon' => 'toy', 'max_order' => 10],
            ],
        ];

        $index = ($counter - 1) % count($layanan);
        $item = $layanan[$index];

        return [
            'kode_layanan' => 'LYN'.str_pad((string) $counter++, 3, '0', STR_PAD_LEFT),
            'nama_layanan' => $item['nama'],
            'tipe_layanan' => $item['tipe'],
            'harga_per_kg' => $item['harga_kg'],
            'harga_per_satuan' => $item['harga_satuan'],
            'satuan' => $item['satuan'],
            'durasi_jam' => $item['durasi'],
            'deskripsi' => $item['deskripsi'],
            'status' => 'Aktif',
            'metadata' => $item['metadata'],
        ];
    }
}
