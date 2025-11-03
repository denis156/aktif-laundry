<?php

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

        return [
            'kode_pelanggan' => 'PLG' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
            'nama' => fake()->name(),
            'no_hp' => '8' . fake()->numerify('###########'),
            'alamat' => fake()->address(),
            'email' => fake()->unique()->safeEmail(),
            'tanggal_daftar' => fake()->dateTimeBetween('-1 year', 'now'),
            'total_transaksi' => fake()->numberBetween(0, 20),
            'status' => fake()->randomElement(['Aktif', 'Tidak Aktif']),
        ];
    }
}
