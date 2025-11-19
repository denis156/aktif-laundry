<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggan>
 */
class PelangganFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        // Sample data untuk wilayah Indonesia
        $wilayah = [
            ['kelurahan' => 'Cibiru Wetan', 'kecamatan' => 'Cileunyi', 'kabupaten' => 'Bandung', 'provinsi' => 'Jawa Barat'],
            ['kelurahan' => 'Cijambe', 'kecamatan' => 'Cileunyi', 'kabupaten' => 'Bandung', 'provinsi' => 'Jawa Barat'],
            ['kelurahan' => 'Cisurupan', 'kecamatan' => 'Cileunyi', 'kabupaten' => 'Bandung', 'provinsi' => 'Jawa Barat'],
            ['kelurahan' => 'Dago', 'kecamatan' => 'Coblong', 'kabupaten' => 'Bandung', 'provinsi' => 'Jawa Barat'],
            ['kelurahan' => 'Cipaganti', 'kecamatan' => 'Coblong', 'kabupaten' => 'Bandung', 'provinsi' => 'Jawa Barat'],
        ];

        $selectedWilayah = fake()->randomElement($wilayah);

        // Generate email with 70% probability
        $hasEmail = fake()->boolean(70);
        $email = $hasEmail ? fake()->unique()->safeEmail() : null;

        return [
            'kode_pelanggan' => 'PLG' . str_pad((string) $counter++, 3, '0', STR_PAD_LEFT),
            'nama' => fake()->name(),
            'no_hp' => '8' . fake()->numerify('##########'),
            'email' => $email,
            'alamat' => fake()->streetAddress(),
            'kelurahan' => $selectedWilayah['kelurahan'],
            'kecamatan' => $selectedWilayah['kecamatan'],
            'kabupaten_kota' => $selectedWilayah['kabupaten'],
            'provinsi' => $selectedWilayah['provinsi'],
            'latitude' => fake()->latitude(-7.3, -7.0),
            'longitude' => fake()->longitude(107.5, 107.8),
            'tanggal_daftar' => fake()->dateTimeBetween('-1 year', 'now'),
            'total_transaksi' => 0,
            'status' => fake()->randomElement(['Aktif', 'Aktif', 'Aktif', 'Tidak Aktif']),
            'kode_referral_dipakai' => null,
            'direferensikan_oleh' => null,
            'metadata' => [
                'member_card' => fake()->optional(0.3)->numerify('MEMBER###########'),
                'loyalty_points' => fake()->numberBetween(0, 500),
                'preferensi_pengiriman' => fake()->randomElement(['Jemput', 'Antar', 'Sendiri']),
            ],
        ];
    }
}
