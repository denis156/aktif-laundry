<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Kurir;
use App\Models\Pengaturan;
use App\Helper\AddressMetadata;
use App\Helper\PhoneNumber;
use Illuminate\Support\Facades\Hash;

// ! Helper untuk mengelola data Kurir
//
// ? Metadata yang disimpan:
// * - detail_alamat: Alamat detail (jalan, nomor, RT/RW)
// * - kelurahan: Kelurahan/Desa
// * - kecamatan: Kecamatan
// * - kabupaten_kota: Kabupaten/Kota
// * - provinsi: Provinsi
// * - latitude: Koordinat GPS latitude
// * - longitude: Koordinat GPS longitude
// * - area_coverage: Area yang bisa dilayani kurir (array)
// * - bank_info: Info rekening bank kurir
// * - emergency_contact: Kontak darurat kurir

class KurirHelper
{
    public const META_AREA_COVERAGE = 'area_coverage';
    public const META_BANK_INFO = 'bank_info';
    public const META_EMERGENCY_CONTACT = 'emergency_contact';

    // * Ambil nilai dari metadata
    public static function getMetadata(Kurir $kurir, string $key, mixed $default = null): mixed
    {
        return data_get($kurir->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Kurir $kurir, string $key, mixed $value): void
    {
        $metadata = $kurir->metadata ?? [];
        data_set($metadata, $key, $value);
        $kurir->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Kurir $kurir, array $data): void
    {
        $kurir->metadata = array_merge($kurir->metadata ?? [], $data);
    }

    // * Ambil daftar area yang bisa dilayani kurir
    public static function getAreaCoverage(Kurir $kurir): array
    {
        return (array) self::getMetadata($kurir, self::META_AREA_COVERAGE, []);
    }

    // * Set daftar area yang bisa dilayani kurir
    public static function setAreaCoverage(Kurir $kurir, array $areas): void
    {
        self::setMetadata($kurir, self::META_AREA_COVERAGE, $areas);
    }

    // * Ambil informasi rekening bank kurir
    public static function getBankInfo(Kurir $kurir): ?array
    {
        return self::getMetadata($kurir, self::META_BANK_INFO);
    }

    // * Set informasi rekening bank kurir
    public static function setBankInfo(Kurir $kurir, array $bankData): void
    {
        self::setMetadata($kurir, self::META_BANK_INFO, $bankData);
    }

    // * Ambil kontak darurat kurir
    public static function getEmergencyContact(Kurir $kurir): ?array
    {
        return self::getMetadata($kurir, self::META_EMERGENCY_CONTACT);
    }

    // * Set kontak darurat kurir
    public static function setEmergencyContact(Kurir $kurir, array $contactData): void
    {
        self::setMetadata($kurir, self::META_EMERGENCY_CONTACT, $contactData);
    }

    // * Set alamat regional ke metadata dan sync ke kolom alamat
    public static function setAlamatRegional(
        Kurir $kurir,
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
            $kurir,
            $detailAlamat,
            $kelurahan,
            $kecamatan,
            $kabupatenKota,
            $provinsi,
            $latitude,
            $longitude
        );

        // Sync kolom alamat (text) dengan metadata
        AddressMetadata::syncAlamatColumn($kurir);
    }

    // * Get alamat lengkap dari metadata
    public static function getAlamatLengkap(Kurir $kurir): string
    {
        return AddressMetadata::formatAddress($kurir, true);
    }

    // * Update alamat regional dari array data
    public static function updateAlamatRegional(Kurir $kurir, array $data): void
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

        AddressMetadata::update($kurir, $addressData);
        AddressMetadata::syncAlamatColumn($kurir);
    }

    // * Generate kode kurir unik
    public static function generateKodeKurir(): string
    {
        $prefix = Pengaturan::getValue('format_id_kurir', 'KUR');
        $prefixLength = strlen($prefix);

        $lastKurir = Kurir::withTrashed()->orderBy('kode_kurir', 'desc')->first();

        if (!$lastKurir) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastKurir->kode_kurir, $prefixLength);
        $nextNumber = $lastNumber + 1;

        // Check if there are any gaps in the numbering
        while (Kurir::where('kode_kurir', $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
            $nextNumber++;
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // * Create kurir baru dengan alamat regional dan GPS
    public static function createKurir(array $data): Kurir
    {
        // Normalize nomor HP
        $normalizedPhone = PhoneNumber::normalize($data['no_hp'] ?? '');
        if (!$normalizedPhone) {
            throw new \Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
        }

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

        // Create kurir
        $kurir = Kurir::create([
            'kode_kurir' => self::generateKodeKurir(),
            'nama' => $data['nama'],
            'no_hp' => $normalizedPhone,
            'email' => $data['email'] ?? null,
            'alamat' => $alamatLengkap,
            'no_kendaraan' => $data['no_kendaraan'] ?? null,
            'jenis_kendaraan' => $data['jenis_kendaraan'] ?? null,
            'foto_profil' => $data['foto_profil'] ?? null,
            'tanggal_bergabung' => $data['tanggal_bergabung'] ?? now(),
            'status' => 'Aktif',
            'total_antar' => 0,
            'total_jemput' => 0,
            'password' => isset($data['password']) ? Hash::make($data['password']) : null,
            'metadata' => $addressMetadata,
        ]);

        return $kurir;
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_AREA_COVERAGE => 'nullable|array',
            self::META_BANK_INFO => 'nullable|array',
            self::META_EMERGENCY_CONTACT => 'nullable|array',
        ];
    }
}
