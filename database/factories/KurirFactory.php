<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\Database\KurirHelper;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kurir>
 */
class KurirFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

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
            'kode_kurir' => KurirHelper::generateKodeKurir(),
            'nama' => fake()->name(),
            'no_hp' => '8'.fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => null,
            'password' => static::$password ??= Hash::make('password'),
            'device_token' => null,
            // Alamat lengkap
            'alamat' => $alamatLengkap,
            'detail_alamat' => $detailAlamat,
            'kelurahan' => $selectedWilayah['kelurahan'],
            'kecamatan' => $selectedWilayah['kecamatan'],
            'kabupaten_kota' => $kabupatenKota,
            'provinsi' => $provinsi,
            'latitude' => $latitude,
            'longitude' => $longitude,
            // Vehicle info
            'no_kendaraan' => fake()->regexify('[A-Z]{1,2} [0-9]{1,4} [A-Z]{1,3}'),
            'jenis_kendaraan' => fake()->randomElement(['Motor', 'Mobil']),
            // Profile
            'tanggal_bergabung' => fake()->dateTimeBetween('-2 years', 'now'),
            'status' => fake()->randomElement(['Aktif', 'Aktif', 'Tidak Aktif']),
            // Data Bank
            'bank_name' => fake()->randomElement(['BCA', 'Mandiri', 'BRI', 'BNI']),
            'bank_account_number' => fake()->numerify('##########'),
            'bank_account_name' => fake()->name(),
            // Emergency Contact
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => '8'.fake()->numerify('##########'),
            'emergency_contact_relation' => fake()->randomElement(['Orang Tua', 'Saudara', 'Teman']),
            // Avatar
            'avatar_url' => null,
        ];
    }

    /**
     * Indicate that the courier should have a custom password.
     */
    public function withPassword(string $password): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Indicate that the courier should have a device token for push notifications.
     */
    public function withDeviceToken(?string $token = null): static
    {
        return $this->state(fn (array $attributes) => [
            'device_token' => $token ?? fake()->regexify('[a-f0-9]{64}'),
        ]);
    }

    /**
     * Indicate that the courier is currently inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Tidak Aktif',
        ]);
    }

    /**
     * Indicate that the courier is on leave/cuti.
     */
    public function onLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Cuti',
        ]);
    }
}
