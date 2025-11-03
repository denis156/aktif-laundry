<?php

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
            ['nama' => 'Cuci Kering', 'harga' => 5000, 'durasi' => 24, 'deskripsi' => 'Cuci dan kering saja'],
            ['nama' => 'Cuci Setrika', 'harga' => 7000, 'durasi' => 48, 'deskripsi' => 'Cuci, kering, dan setrika rapi'],
            ['nama' => 'Setrika Saja', 'harga' => 4000, 'durasi' => 12, 'deskripsi' => 'Setrika saja tanpa cuci'],
            ['nama' => 'Express 6 Jam', 'harga' => 12000, 'durasi' => 6, 'deskripsi' => 'Layanan kilat 6 jam jadi'],
            ['nama' => 'Express 12 Jam', 'harga' => 10000, 'durasi' => 12, 'deskripsi' => 'Layanan kilat 12 jam jadi'],
            ['nama' => 'Cuci Karpet', 'harga' => 15000, 'durasi' => 72, 'deskripsi' => 'Cuci karpet dan permadani'],
            ['nama' => 'Cuci Sepatu', 'harga' => 25000, 'durasi' => 48, 'deskripsi' => 'Cuci sepatu sneakers'],
            ['nama' => 'Cuci Boneka', 'harga' => 20000, 'durasi' => 24, 'deskripsi' => 'Cuci boneka dan mainan'],
        ];

        $index = ($counter - 1) % count($layanan);
        $item = $layanan[$index];

        return [
            'kode_layanan' => 'LYN' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
            'nama_layanan' => $item['nama'],
            'harga_per_kg' => $item['harga'],
            'durasi_jam' => $item['durasi'],
            'deskripsi' => $item['deskripsi'],
            'status' => 'Aktif',
        ];
    }
}
