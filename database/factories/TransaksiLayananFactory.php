<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\JenisPakaian;
use App\Models\Layanan;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransaksiLayanan>
 */
class TransaksiLayananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $layanan = Layanan::inRandomOrder()->first() ?? Layanan::factory()->create();
        $transaksi = Transaksi::inRandomOrder()->first();

        $isPerKg = $layanan->tipe_layanan === 'per_kg';

        if ($isPerKg) {
            // Generate jenis pakaian untuk layanan per kg
            $jenisPakaianAll = JenisPakaian::all();
            $jumlahJenis = fake()->numberBetween(1, 4);
            $jenisPakaian = [];

            if ($jenisPakaianAll->isNotEmpty()) {
                $selected = $jenisPakaianAll->random(min($jumlahJenis, $jenisPakaianAll->count()));
                foreach ($selected as $jp) {
                    $jenisPakaian[] = [
                        'jenis_pakaian_id' => $jp->id,
                        'nama_jenis' => $jp->nama_jenis,
                        'jumlah' => fake()->numberBetween(1, 5),
                    ];
                }
            }

            $beratKg = fake()->randomFloat(2, 1, 10);
            $hargaPerKg = $layanan->harga_per_kg;
            $subtotal = $beratKg * $hargaPerKg;

            return [
                'transaksi_id' => $transaksi?->id ?? Transaksi::factory(),
                'layanan_id' => $layanan->id,
                'nama_layanan' => $layanan->nama_layanan,
                'jenis_pakaian' => $jenisPakaian,
                'berat_kg' => $beratKg,
                'harga_per_kg' => $hargaPerKg,
                'jumlah_satuan' => null,
                'harga_per_satuan' => null,
                'subtotal' => (int) $subtotal,
                'catatan_khusus' => fake()->optional(0.2)->sentence(),
            ];
        } else {
            // Layanan per satuan
            $jumlahSatuan = fake()->numberBetween(1, 5);
            $hargaPerSatuan = $layanan->harga_per_satuan;
            $subtotal = $jumlahSatuan * $hargaPerSatuan;

            return [
                'transaksi_id' => $transaksi?->id ?? Transaksi::factory(),
                'layanan_id' => $layanan->id,
                'nama_layanan' => $layanan->nama_layanan,
                'jenis_pakaian' => null,
                'berat_kg' => null,
                'harga_per_kg' => null,
                'jumlah_satuan' => $jumlahSatuan,
                'harga_per_satuan' => $hargaPerSatuan,
                'subtotal' => (int) $subtotal,
                'catatan_khusus' => fake()->optional(0.2)->sentence(),
            ];
        }
    }
}
