<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Wilayah Kota Kendari
        $wilayah = [
            ['kelurahan' => 'Mandonga', 'kecamatan' => 'Mandonga'],
            ['kelurahan' => 'Wua-Wua', 'kecamatan' => 'Wua-Wua'],
            ['kelurahan' => 'Poasia', 'kecamatan' => 'Poasia'],
            ['kelurahan' => 'Baruga', 'kecamatan' => 'Baruga'],
            ['kelurahan' => 'Kendari', 'kecamatan' => 'Kendari'],
            ['kelurahan' => 'Kendari Barat', 'kecamatan' => 'Kendari Barat'],
            ['kelurahan' => 'Abeli', 'kecamatan' => 'Abeli'],
            ['kelurahan' => 'Kambu', 'kecamatan' => 'Kambu'],
            ['kelurahan' => 'Kadia', 'kecamatan' => 'Kadia'],
            ['kelurahan' => 'Puuwatu', 'kecamatan' => 'Puuwatu'],
        ];

        $selectedWilayah = fake()->randomElement($wilayah);
        $detailAlamat = fake()->streetAddress();
        $kabupatenKota = 'Kota Kendari';
        $provinsi = 'Sulawesi Tenggara';

        // Alamat lengkap display
        $alamatLengkap = implode(', ', array_filter([
            $detailAlamat,
            "Kel. {$selectedWilayah['kelurahan']}",
            "Kec. {$selectedWilayah['kecamatan']}",
            $kabupatenKota,
            $provinsi,
        ]));

        // Koordinat GPS Kota Kendari
        $latitude = fake()->latitude(-4.0, -3.9);
        $longitude = fake()->longitude(122.4, 122.6);

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'no_hp' => '8'.fake()->numerify('##########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar_url' => null,
            'super_admin' => false,
            // Data Kepegawaian
            'gaji' => fake()->numberBetween(3000000, 6000000),
            'jam_masuk' => fake()->time('H:i', '10:00'),
            'jam_keluar' => fake()->time('H:i', '20:00'),
            // Data Bank
            'bank_name' => fake()->randomElement(['BCA', 'Mandiri', 'BRI', 'BNI']),
            'bank_account_number' => fake()->numerify('##########'),
            'bank_account_name' => fake()->name(),
            // Alamat lengkap
            'alamat' => $alamatLengkap,
            'detail_alamat' => $detailAlamat,
            'kelurahan' => $selectedWilayah['kelurahan'],
            'kecamatan' => $selectedWilayah['kecamatan'],
            'kabupaten_kota' => $kabupatenKota,
            'provinsi' => $provinsi,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'super_admin' => true,
        ]);
    }
}
