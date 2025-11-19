<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kurir>
 */
class KurirFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;

        $jenisKendaraan = ['Motor', 'Mobil'];
        $selected = fake()->randomElement($jenisKendaraan);

        return [
            'kode_kurir' => 'KUR' . str_pad((string) $counter++, 3, '0', STR_PAD_LEFT),
            'nama' => fake()->name(),
            'no_hp' => '8' . fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'alamat' => fake()->address(),
            'no_kendaraan' => fake()->regexify('[A-Z]{1,2} [0-9]{1,4} [A-Z]{1,3}'),
            'jenis_kendaraan' => $selected,
            'foto_profil' => null,
            'tanggal_bergabung' => fake()->dateTimeBetween('-2 years', 'now'),
            'status' => fake()->randomElement(['Aktif', 'Aktif', 'Tidak Aktif']),
            'total_antar' => fake()->numberBetween(0, 200),
            'total_jemput' => fake()->numberBetween(0, 200),
            'password' => static::$password ??= Hash::make('password'),
            'device_token' => null,
            'metadata' => [
                'area_coverage' => fake()->randomElement(['Bandung Timur', 'Bandung Barat', 'Bandung Utara', 'Bandung Selatan']),
                'bank_info' => [
                    'bank' => fake()->randomElement(['BCA', 'Mandiri', 'BRI', 'BNI']),
                    'no_rekening' => fake()->numerify('##########'),
                    'nama_pemilik' => fake()->name(),
                ],
                'emergency_contact' => [
                    'nama' => fake()->name(),
                    'no_hp' => '8' . fake()->numerify('##########'),
                    'hubungan' => fake()->randomElement(['Orang Tua', 'Saudara', 'Teman']),
                ],
            ],
        ];
    }
}
