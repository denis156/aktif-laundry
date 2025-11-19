<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\AddressMetadata;
use App\Helper\RegionalLocation;
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
        ];

        $selectedWilayah = fake()->randomElement($wilayah);
        $detailAlamat = fake()->streetAddress();

        // Koordinat GPS Kota Kendari
        $latitude = fake()->latitude(-4.0, -3.9);
        $longitude = fake()->longitude(122.4, 122.6);

        // Generate metadata alamat dengan GPS
        $addressMetadata = AddressMetadata::generate(
            $detailAlamat,
            $selectedWilayah['kelurahan'],
            $selectedWilayah['kecamatan'],
            RegionalLocation::getRegencyName(),
            RegionalLocation::getProvinceName(),
            $latitude,
            $longitude
        );

        // Alamat lengkap (auto-generated dari metadata)
        $alamatLengkap = RegionalLocation::formatFullAddress(
            $detailAlamat,
            $selectedWilayah['kelurahan'],
            $selectedWilayah['kecamatan'],
            RegionalLocation::getRegencyName(),
            RegionalLocation::getProvinceName()
        );

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'no_hp' => '8' . fake()->numerify('##########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar_url' => null,
            'super_admin' => false,
            'alamat' => $alamatLengkap,
            'remember_token' => Str::random(10),
            'metadata' => array_merge($addressMetadata, [
                'shift' => fake()->randomElement(['Pagi', 'Siang', 'Malam']),
                'gaji' => fake()->numberBetween(3000000, 6000000),
                'target_bulanan' => fake()->numberBetween(10000000, 50000000),
            ]),
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
