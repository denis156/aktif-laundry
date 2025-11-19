<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\AddressMetadata;
use App\Helper\RegionalLocation;
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

        // Wilayah Kota Kendari untuk kurir
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

        // Detail alamat kurir
        $detailAlamat = fake()->streetAddress();

        // Koordinat GPS Kota Kendari
        $latitude = fake()->latitude(-4.0, -3.9);
        $longitude = fake()->longitude(122.4, 122.6);

        // Generate metadata alamat dengan GPS menggunakan AddressMetadata helper
        $addressMetadata = AddressMetadata::generate(
            $detailAlamat,
            $selectedWilayah['kelurahan'],
            $selectedWilayah['kecamatan'],
            RegionalLocation::getRegencyName(),
            RegionalLocation::getProvinceName(),
            $latitude,
            $longitude
        );

        // Alamat lengkap gabungan (auto-generated dari metadata)
        $alamatLengkap = RegionalLocation::formatFullAddress(
            $detailAlamat,
            $selectedWilayah['kelurahan'],
            $selectedWilayah['kecamatan'],
            RegionalLocation::getRegencyName(),
            RegionalLocation::getProvinceName()
        );

        // Area coverage sesuai kecamatan di Kota Kendari
        $areaCoverage = fake()->randomElement([
            'Mandonga',
            'Wua-Wua',
            'Poasia',
            'Baruga',
            'Kendari',
            'Kendari Barat',
            'Abeli',
            'Kambu',
            'Kadia',
            'Puuwatu',
        ]);

        $jenisKendaraan = ['Motor', 'Mobil'];
        $selected = fake()->randomElement($jenisKendaraan);

        return [
            'kode_kurir' => 'KUR' . str_pad((string) $counter++, 3, '0', STR_PAD_LEFT),
            'nama' => fake()->name(),
            'no_hp' => '8' . fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'alamat' => $alamatLengkap, // Alamat lengkap (auto-generated dari metadata)
            'no_kendaraan' => fake()->regexify('[A-Z]{1,2} [0-9]{1,4} [A-Z]{1,3}'),
            'jenis_kendaraan' => $selected,
            'foto_profil' => null,
            'tanggal_bergabung' => fake()->dateTimeBetween('-2 years', 'now'),
            'status' => fake()->randomElement(['Aktif', 'Aktif', 'Tidak Aktif']),
            'total_antar' => fake()->numberBetween(0, 200),
            'total_jemput' => fake()->numberBetween(0, 200),
            'password' => static::$password ??= Hash::make('password'),
            'device_token' => null,
            'metadata' => array_merge($addressMetadata, [
                'area_coverage' => $areaCoverage,
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
            ]),
        ];
    }
}
