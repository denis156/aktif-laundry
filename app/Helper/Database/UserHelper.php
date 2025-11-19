<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\User;
use App\Helper\AddressMetadata;
use Illuminate\Support\Facades\Hash;

// ! Helper untuk mengelola data User
//
// ? Metadata yang disimpan:
// * - detail_alamat: Alamat detail (jalan, nomor, RT/RW)
// * - kelurahan: Kelurahan/Desa
// * - kecamatan: Kecamatan
// * - kabupaten_kota: Kabupaten/Kota
// * - provinsi: Provinsi
// * - latitude: Koordinat GPS latitude
// * - longitude: Koordinat GPS longitude
// * - shift: Shift kerja (pagi/siang/malam)
// * - gaji: Gaji pokok
// * - target_bulanan: Target penjualan/kinerja bulanan

class UserHelper
{
    public const META_SHIFT = 'shift';
    public const META_GAJI = 'gaji';

    // * Ambil nilai dari metadata
    public static function getMetadata(User $user, string $key, mixed $default = null): mixed
    {
        return data_get($user->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(User $user, string $key, mixed $value): void
    {
        $metadata = $user->metadata ?? [];
        data_set($metadata, $key, $value);
        $user->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(User $user, array $data): void
    {
        $user->metadata = array_merge($user->metadata ?? [], $data);
    }

    // * Set alamat regional ke metadata dan sync ke kolom alamat
    public static function setAlamatRegional(
        User $user,
        string $detailAlamat,
        string $kelurahan,
        string $kecamatan,
        string $kabupatenKota,
        string $provinsi,
        ?float $latitude = null,
        ?float $longitude = null
    ): void {
        // Set metadata alamat menggunakan AddressMetadata
        AddressMetadata::set(
            $user,
            $detailAlamat,
            $kelurahan,
            $kecamatan,
            $kabupatenKota,
            $provinsi,
            $latitude,
            $longitude
        );

        // Sync kolom alamat (text) dengan metadata
        AddressMetadata::syncAlamatColumn($user);
    }

    // * Get alamat lengkap dari metadata
    public static function getAlamatLengkap(User $user): string
    {
        return AddressMetadata::formatAddress($user, true);
    }

    // * Update alamat regional dari array data
    public static function updateAlamatRegional(User $user, array $data): void
    {
        $addressData = AddressMetadata::generate(
            $data['detail_alamat'] ?? '',
            $data['kelurahan'] ?? '',
            $data['kecamatan'] ?? '',
            $data['kabupaten_kota'] ?? '',
            $data['provinsi'] ?? '',
            isset($data['latitude']) ? (float) $data['latitude'] : null,
            isset($data['longitude']) ? (float) $data['longitude'] : null
        );

        AddressMetadata::update($user, $addressData);
        AddressMetadata::syncAlamatColumn($user);
    }

    // * Ambil shift kerja user
    public static function getShift(User $user): ?string
    {
        return self::getMetadata($user, self::META_SHIFT);
    }

    // * Set shift kerja user
    public static function setShift(User $user, string $shift): void
    {
        self::setMetadata($user, self::META_SHIFT, $shift);
    }

    // * Ambil gaji pokok user
    public static function getGaji(User $user): ?int
    {
        return self::getMetadata($user, self::META_GAJI);
    }

    // * Set gaji pokok user
    public static function setGaji(User $user, int $gaji): void
    {
        self::setMetadata($user, self::META_GAJI, $gaji);
    }

    // * Create user baru dengan alamat regional dan GPS
    public static function createUser(array $data): User
    {
        // Generate metadata alamat dengan GPS
        $addressMetadata = AddressMetadata::generate(
            $data['detail_alamat'] ?? '',
            $data['kelurahan'] ?? '',
            $data['kecamatan'] ?? '',
            $data['kabupaten_kota'] ?? '',
            $data['provinsi'] ?? '',
            isset($data['latitude']) ? (float) $data['latitude'] : null,
            isset($data['longitude']) ? (float) $data['longitude'] : null
        );

        // Build alamat lengkap untuk kolom alamat (text)
        $alamatLengkap = AddressMetadata::buildAlamatFromArray($data);

        // Prepare metadata (merge alamat + shift + gaji)
        $metadata = array_merge($addressMetadata, [
            self::META_SHIFT => $data['shift'] ?? null,
            self::META_GAJI => $data['gaji'] ?? null,
        ]);

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'] ?? null,
            'password' => Hash::make($data['password']),
            'alamat' => $alamatLengkap,
            'avatar_url' => $data['avatar_url'] ?? null,
            'super_admin' => $data['super_admin'] ?? false,
            'metadata' => $metadata,
        ]);

        return $user;
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_SHIFT => 'nullable|string|max:50',
            self::META_GAJI => 'nullable|integer|min:0',
        ];
    }
}
