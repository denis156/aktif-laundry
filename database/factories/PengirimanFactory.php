<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Kurir;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pengiriman>
 */
class PengirimanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        $tipe = fake()->randomElement(['Jemput', 'Antar']);
        $transaksi = Transaksi::inRandomOrder()->first();

        $jadwalWaktu = fake()->dateTimeBetween('-1 month', '+1 week');
        $status = fake()->randomElement(['Dijadwalkan', 'Dalam Perjalanan', 'Selesai', 'Batal']);

        $waktuMulai = $status !== 'Dijadwalkan' ? $jadwalWaktu : null;
        $waktuSelesai = $status === 'Selesai' ? (clone $waktuMulai)->modify('+' . fake()->numberBetween(10, 60) . ' minutes') : null;

        $fotoBukti = $status === 'Selesai' ? 'bukti_' . fake()->uuid() . '.jpg' : null;

        return [
            'kode_pengiriman' => 'PGR' . str_pad((string) $counter++, 3, '0', STR_PAD_LEFT),
            'transaksi_id' => $transaksi ? $transaksi->id : Transaksi::factory(),
            'kurir_id' => Kurir::inRandomOrder()->first()?->id ?? Kurir::factory(),
            'tipe' => $tipe,
            'alamat_tujuan' => fake()->address(),
            'nama_penerima' => fake()->name(),
            'no_hp_penerima' => '8' . fake()->numerify('##########'),
            'latitude' => fake()->latitude(-7.3, -7.0),
            'longitude' => fake()->longitude(107.5, 107.8),
            'jadwal_waktu' => $jadwalWaktu,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'biaya_antar' => fake()->randomElement([0, 5000, 10000, 15000, 20000]),
            'jarak_km' => fake()->randomFloat(2, 1, 15),
            'status' => $status,
            'catatan' => fake()->optional(0.3)->sentence(),
            'foto_bukti' => $fotoBukti,
            'metadata' => [
                'lokasi_pengambilan' => [
                    'lat' => fake()->latitude(-7.3, -7.0),
                    'lng' => fake()->longitude(107.5, 107.8),
                    'waktu' => $waktuMulai,
                ],
                'tracking' => $status === 'Selesai' ? [
                    ['lat' => fake()->latitude(-7.3, -7.0), 'lng' => fake()->longitude(107.5, 107.8), 'waktu' => $waktuMulai],
                    ['lat' => fake()->latitude(-7.3, -7.0), 'lng' => fake()->longitude(107.5, 107.8), 'waktu' => $waktuSelesai],
                ] : [],
                'review_customer' => $status === 'Selesai' ? [
                    'rating' => fake()->numberBetween(3, 5),
                    'komentar' => fake()->optional(0.5)->sentence(),
                ] : null,
            ],
        ];
    }
}
