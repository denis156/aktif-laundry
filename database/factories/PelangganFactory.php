<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\Database\PelangganHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggan>
 */
class PelangganFactory extends Factory
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

        // Generate email with 70% probability
        $hasEmail = fake()->boolean(70);
        $email = $hasEmail ? fake()->unique()->safeEmail() : null;

        return [
            'kode_pelanggan' => PelangganHelper::generateKodePelanggan(),
            'nama' => fake()->name(),
            'no_hp' => '8'.fake()->numerify('##########'),
            'email' => $email,
            'email_verified_at' => null,
            'password' => null,
            'fcm_token' => null,
            // Alamat lengkap
            'alamat' => $alamatLengkap,
            'detail_alamat' => $detailAlamat,
            'kelurahan' => $selectedWilayah['kelurahan'],
            'kecamatan' => $selectedWilayah['kecamatan'],
            'kabupaten_kota' => $kabupatenKota,
            'provinsi' => $provinsi,
            'latitude' => $latitude,
            'longitude' => $longitude,
            // Data membership
            'tanggal_daftar' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => fake()->randomElement(['Aktif', 'Aktif', 'Aktif', 'Tidak Aktif']),
            'loyalty_points' => 0,
            'member_card' => fake()->optional(0.3)->numerify('MEMBER###########'),
            'avatar_url' => null,
            // Referral (PelangganObserver akan auto-create Referral jika sistem aktif)
            'direferensikan_oleh' => null,
        ];
    }

    /**
     * Indicate that the customer has registered on mobile app with a password.
     */
    public function withPassword(?string $password = null): static
    {
        return $this->state(function (array $attributes) use ($password) {
            if ($password === null) {
                static::$password ??= \Illuminate\Support\Facades\Hash::make('password');

                return [
                    'password' => static::$password,
                    'email_verified_at' => now(),
                ];
            }

            return [
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'email_verified_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the customer has an FCM token for push notifications.
     */
    public function withFcmToken(?string $token = null): static
    {
        return $this->state(fn (array $attributes) => [
            'fcm_token' => $token ?? fake()->regexify('[a-zA-Z0-9_-]{152}'),
        ]);
    }

    /**
     * Indicate that the customer is a registered mobile app user.
     */
    public function registered(?string $password = null): static
    {
        return $this->withPassword($password)->withFcmToken();
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
     * Indicate that the customer is a loyal customer with many loyalty points.
     */
    public function loyal(): static
    {
        return $this->state(fn (array $attributes) => [
            'tanggal_daftar' => fake()->dateTimeBetween('-3 years', '-1 year'),
            'loyalty_points' => fake()->numberBetween(500, 2000),
            'member_card' => 'MEMBER'.fake()->numerify('###########'),
        ]);
    }

    /**
     * Indicate that the customer was referred by another customer.
     */
    public function referred(int $referrerId): static
    {
        return $this->state(fn (array $attributes) => [
            'direferensikan_oleh' => $referrerId,
        ]);
    }
}
