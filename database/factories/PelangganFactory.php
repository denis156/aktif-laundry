<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Helper\AddressMetadata;
use App\Helper\RegionalLocation;
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
            'kode_pelanggan' => 'PLG' . str_pad((string) $counter++, 3, '0', STR_PAD_LEFT),
            'nama' => fake()->name(),
            'no_hp' => '8' . fake()->numerify('##########'),
            'email' => $email,
            'alamat' => $alamatLengkap, // Alamat lengkap (auto-generated dari metadata)
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
}
