<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\AddressMetadata;
use App\Helper\RegionalLocation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggan>
 */
class PelangganFactory extends Factory
{
    /**
     * The current password being used by the factory.
     * Shared static password to improve performance when creating multiple customers.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Sample data untuk wilayah Kota Kendari, Sulawesi Tenggara
        // Data ini sesuai dengan scope RegionalLocation helper
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

        // Gunakan RegionalLocation helper untuk provinsi dan kabupaten/kota
        $provinsi = RegionalLocation::getProvinceName(); // Sulawesi Tenggara
        $kabupatenKota = RegionalLocation::getRegencyName(); // Kota Kendari

        // Detail alamat (jalan, nomor rumah, RT/RW)
        $detailAlamat = fake()->streetAddress();

        // Koordinat GPS Kota Kendari: latitude (-3.9 sampai -4.0), longitude (122.4 sampai 122.6)
        $latitude = fake()->latitude(-4.0, -3.9);
        $longitude = fake()->longitude(122.4, 122.6);

        // Generate metadata alamat dengan GPS menggunakan AddressMetadata helper
        $addressMetadata = AddressMetadata::generate(
            $detailAlamat,
            $selectedWilayah['kelurahan'],
            $selectedWilayah['kecamatan'],
            $kabupatenKota,
            $provinsi,
            $latitude,
            $longitude
        );

        // Alamat lengkap gabungan semua komponen (auto-generated dari metadata)
        $alamatLengkap = RegionalLocation::formatFullAddress(
            $detailAlamat,
            $selectedWilayah['kelurahan'],
            $selectedWilayah['kecamatan'],
            $kabupatenKota,
            $provinsi
        );

        // Generate email with 70% probability
        $hasEmail = fake()->boolean(70);
        $email = $hasEmail ? fake()->unique()->safeEmail() : null;

        return [
            'kode_pelanggan' => 'PLG'.str_pad((string) fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'nama' => fake()->name(),
            'no_hp' => '8'.fake()->numerify('##########'),
            'email' => $email,
            'alamat' => $alamatLengkap, // Alamat lengkap (auto-generated dari metadata)
            'password' => null, // Nullable - customer may not have registered on mobile app yet
            'device_token' => null, // Nullable - no push notification token by default
            'tanggal_daftar' => fake()->dateTimeBetween('-1 year', 'now'),
            'total_transaksi' => 0,
            'status' => fake()->randomElement(['Aktif', 'Aktif', 'Aktif', 'Tidak Aktif']),
            'kode_referral_dipakai' => null,
            'direferensikan_oleh' => null,
            'metadata' => array_merge($addressMetadata, [
                'member_card' => fake()->optional(0.3)->numerify('MEMBER###########'),
                'loyalty_points' => fake()->numberBetween(0, 500),
                'preferensi_pengiriman' => fake()->randomElement(['antar_jemput', 'ambil_sendiri']),
            ]),
        ];
    }

    /**
     * Indicate that the customer has registered on mobile app with a password.
     * Creates a customer with hashed password (default: 'password').
     *
     * @param  string|null  $password  The password to set, or null to use default 'password'
     */
    public function withPassword(?string $password = null): static
    {
        return $this->state(function (array $attributes) use ($password) {
            if ($password === null) {
                // Use shared static password for performance
                static::$password ??= Hash::make('password');

                return ['password' => static::$password];
            }

            return ['password' => Hash::make($password)];
        });
    }

    /**
     * Indicate that the customer has NOT registered on mobile app (no password).
     * This is the default state, but can be explicitly used for clarity.
     */
    public function withoutPassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
        ]);
    }

    /**
     * Indicate that the customer has a device token for push notifications.
     *
     * @param  string|null  $token  The device token, or null to generate a fake one
     */
    public function withDeviceToken(?string $token = null): static
    {
        return $this->state(fn (array $attributes) => [
            'device_token' => $token ?? fake()->regexify('[a-f0-9]{64}'),
        ]);
    }

    /**
     * Indicate that the customer is a registered mobile app user.
     * Sets password and device token automatically.
     *
     * @param  string|null  $password  The password to set, or null to use default 'password'
     */
    public function registered(?string $password = null): static
    {
        return $this->withPassword($password)->withDeviceToken();
    }

    /**
     * Indicate that the customer is currently inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Tidak Aktif',
        ]);
    }

    /**
     * Indicate that the customer is a loyal customer with many transactions.
     */
    public function loyal(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_transaksi' => fake()->numberBetween(20, 100),
            'tanggal_daftar' => fake()->dateTimeBetween('-3 years', '-1 year'),
            'metadata' => array_merge($attributes['metadata'], [
                'member_card' => 'MEMBER'.fake()->numerify('###########'),
                'loyalty_points' => fake()->numberBetween(500, 2000),
            ]),
        ]);
    }

    /**
     * Indicate that the customer was referred by another customer.
     *
     * @param  int  $referrerId  The ID of the referring customer
     * @param  string|null  $referralCode  The referral code used
     */
    public function referred(int $referrerId, ?string $referralCode = null): static
    {
        return $this->state(fn (array $attributes) => [
            'direferensikan_oleh' => $referrerId,
            'kode_referral_dipakai' => $referralCode ?? fake()->regexify('[A-Z0-9]{8}'),
        ]);
    }
}
