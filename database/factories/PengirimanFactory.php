<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\Database\PengirimanHelper;
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
        $tipe = fake()->randomElement(['Jemput', 'Antar']);
        $transaksi = Transaksi::inRandomOrder()->first();

        $jadwalWaktu = fake()->dateTimeBetween('-1 month', '+1 week');
        $status = fake()->randomElement(['Dijadwalkan', 'Dalam Perjalanan', 'Selesai', 'Batal']);

        $waktuMulai = $status !== 'Dijadwalkan' ? $jadwalWaktu : null;
        $waktuSelesai = $status === 'Selesai'
            ? (clone $waktuMulai)->modify('+'.fake()->numberBetween(10, 60).' minutes')
            : null;

        $fotoBukti = $status === 'Selesai' ? 'bukti_'.fake()->uuid().'.jpg' : null;

        // Koordinat GPS Kota Kendari
        $latitude = fake()->latitude(-4.0, -3.9);
        $longitude = fake()->longitude(122.4, 122.6);

        return [
            'kode_pengiriman' => PengirimanHelper::generateKodePengiriman(),
            'transaksi_id' => $transaksi?->id ?? Transaksi::factory(),
            'kurir_id' => Kurir::where('status', 'Aktif')->inRandomOrder()->first()?->id ?? Kurir::factory(),
            'tipe' => $tipe,
            // Alamat dan penerima
            'alamat_tujuan' => fake()->address(),
            'nama_penerima' => fake()->name(),
            'no_hp_penerima' => '8'.fake()->numerify('##########'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            // Lokasi pickup (untuk jemput)
            'lokasi_pickup_latitude' => $tipe === 'Jemput' ? fake()->latitude(-4.0, -3.9) : null,
            'lokasi_pickup_longitude' => $tipe === 'Jemput' ? fake()->longitude(122.4, 122.6) : null,
            'lokasi_pickup_address' => $tipe === 'Jemput' ? fake()->address() : null,
            // Timeline
            'jadwal_waktu' => $jadwalWaktu,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            // Status
            'status' => $status,
            'catatan' => fake()->optional(0.3)->sentence(),
            'foto_bukti' => $fotoBukti,
            // Tracking
            'tracking' => $status === 'Selesai' ? [
                ['status' => 'Dijemput', 'waktu' => $waktuMulai, 'lat' => fake()->latitude(-4.0, -3.9), 'lng' => fake()->longitude(122.4, 122.6)],
                ['status' => 'Dalam Perjalanan', 'waktu' => (clone $waktuMulai)->modify('+10 minutes'), 'lat' => fake()->latitude(-4.0, -3.9), 'lng' => fake()->longitude(122.4, 122.6)],
                ['status' => 'Selesai', 'waktu' => $waktuSelesai, 'lat' => $latitude, 'lng' => $longitude],
            ] : [],
            // Review (hanya jika selesai)
            'review_rating' => $status === 'Selesai' ? fake()->numberBetween(3, 5) : null,
            'review_text' => $status === 'Selesai' ? fake()->optional(0.5)->sentence() : null,
        ];
    }

    /**
     * Indicate that delivery is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $waktuMulai = fake()->dateTimeBetween('-1 week', 'now');
            $waktuSelesai = (clone $waktuMulai)->modify('+'.fake()->numberBetween(10, 60).' minutes');

            return [
                'status' => 'Selesai',
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
                'foto_bukti' => 'bukti_'.fake()->uuid().'.jpg',
                'tracking' => [
                    ['status' => 'Dijemput', 'waktu' => $waktuMulai],
                    ['status' => 'Selesai', 'waktu' => $waktuSelesai],
                ],
                'review_rating' => fake()->numberBetween(4, 5),
                'review_text' => fake()->sentence(),
            ];
        });
    }

    /**
     * Indicate that delivery is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Dijadwalkan',
            'waktu_mulai' => null,
            'waktu_selesai' => null,
            'foto_bukti' => null,
            'tracking' => [],
        ]);
    }
}
