<?php

declare(strict_types=1);

namespace App\Helper;

use Illuminate\Support\Facades\Log;

/**
 * Helper untuk mengelola metadata alamat wilayah Indonesia
 *
 * Digunakan oleh: User, Pelanggan, Kurir
 *
 * Metadata yang disimpan:
 * - detail_alamat: Alamat detail (jalan, nomor, RT/RW)
 * - kelurahan: Kelurahan/Desa
 * - kecamatan: Kecamatan
 * - kabupaten_kota: Kabupaten/Kota
 * - provinsi: Provinsi
 * - latitude: Koordinat GPS latitude
 * - longitude: Koordinat GPS longitude
 *
 * Features:
 * - Generate metadata alamat lengkap dengan GPS
 * - Extract metadata alamat dari model
 * - Update metadata alamat
 * - Format alamat lengkap dari metadata
 * - Validate struktur metadata alamat
 */
class AddressMetadata
{
    // Key constants untuk metadata
    public const META_DETAIL_ALAMAT = 'detail_alamat';

    public const META_KELURAHAN = 'kelurahan';

    public const META_KECAMATAN = 'kecamatan';

    public const META_KABUPATEN_KOTA = 'kabupaten_kota';

    public const META_PROVINSI = 'provinsi';

    public const META_LATITUDE = 'latitude';

    public const META_LONGITUDE = 'longitude';

    /**
     * Generate metadata alamat lengkap dengan GPS
     */
    public static function generate(
        string $detailAlamat,
        string $kelurahan,
        string $kecamatan,
        string $kabupatenKota,
        string $provinsi,
        ?float $latitude = null,
        ?float $longitude = null
    ): array {
        return [
            self::META_DETAIL_ALAMAT => $detailAlamat,
            self::META_KELURAHAN => $kelurahan,
            self::META_KECAMATAN => $kecamatan,
            self::META_KABUPATEN_KOTA => $kabupatenKota,
            self::META_PROVINSI => $provinsi,
            self::META_LATITUDE => $latitude,
            self::META_LONGITUDE => $longitude,
        ];
    }

    /**
     * Generate metadata alamat untuk Kota Kendari (shortcut method)
     */
    public static function generateKendari(
        string $detailAlamat,
        string $kelurahan,
        string $kecamatan,
        ?float $latitude = null,
        ?float $longitude = null
    ): array {
        return self::generate(
            $detailAlamat,
            $kelurahan,
            $kecamatan,
            RegionalLocation::getRegencyName(),
            RegionalLocation::getProvinceName(),
            $latitude,
            $longitude
        );
    }

    /**
     * Extract metadata alamat dari model
     */
    public static function extract(object $model): array
    {
        $metadata = $model->metadata ?? [];

        return [
            self::META_DETAIL_ALAMAT => $metadata[self::META_DETAIL_ALAMAT] ?? '',
            self::META_KELURAHAN => $metadata[self::META_KELURAHAN] ?? '',
            self::META_KECAMATAN => $metadata[self::META_KECAMATAN] ?? '',
            self::META_KABUPATEN_KOTA => $metadata[self::META_KABUPATEN_KOTA] ?? '',
            self::META_PROVINSI => $metadata[self::META_PROVINSI] ?? '',
            self::META_LATITUDE => $metadata[self::META_LATITUDE] ?? null,
            self::META_LONGITUDE => $metadata[self::META_LONGITUDE] ?? null,
        ];
    }

    // * Get specific metadata value dari model
    public static function get(object $model, string $key, mixed $default = null): mixed
    {
        $metadata = $model->metadata ?? [];

        return $metadata[$key] ?? $default;
    }

    // * Update metadata alamat di model (merge dengan metadata existing)
    public static function update(object $model, array $addressData): void
    {
        $metadata = $model->metadata ?? [];

        // Merge address data ke metadata existing
        $model->metadata = array_merge($metadata, $addressData);
    }

    /**
     * Set metadata alamat di model (replace semua metadata alamat)
     */
    public static function set(
        object $model,
        string $detailAlamat,
        string $kelurahan,
        string $kecamatan,
        string $kabupatenKota,
        string $provinsi,
        ?float $latitude = null,
        ?float $longitude = null
    ): void {
        $addressData = self::generate(
            $detailAlamat,
            $kelurahan,
            $kecamatan,
            $kabupatenKota,
            $provinsi,
            $latitude,
            $longitude
        );
        self::update($model, $addressData);
    }

    // * Format alamat lengkap dari metadata model
    public static function formatAddress(object $model, bool $fullAddress = true): string
    {
        $addressData = self::extract($model);

        if ($fullAddress) {
            return RegionalLocation::formatFullAddress(
                $addressData[self::META_DETAIL_ALAMAT],
                $addressData[self::META_KELURAHAN],
                $addressData[self::META_KECAMATAN],
                $addressData[self::META_KABUPATEN_KOTA],
                $addressData[self::META_PROVINSI]
            );
        }

        return RegionalLocation::formatShortAddress(
            $addressData[self::META_DETAIL_ALAMAT],
            $addressData[self::META_KELURAHAN],
            $addressData[self::META_KECAMATAN],
            $addressData[self::META_KABUPATEN_KOTA]
        );
    }

    // * Validate apakah metadata alamat sudah lengkap
    public static function isValid(array $metadata, bool $requireAll = false): bool
    {
        // Minimal harus ada detail_alamat
        if (empty($metadata[self::META_DETAIL_ALAMAT])) {
            return false;
        }

        // Jika require all, cek semua field
        if ($requireAll) {
            $required = [
                self::META_DETAIL_ALAMAT,
                self::META_KELURAHAN,
                self::META_KECAMATAN,
                self::META_KABUPATEN_KOTA,
                self::META_PROVINSI,
            ];

            foreach ($required as $key) {
                if (empty($metadata[$key])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get array keys untuk metadata alamat
     */
    public static function getKeys(): array
    {
        return [
            self::META_DETAIL_ALAMAT,
            self::META_KELURAHAN,
            self::META_KECAMATAN,
            self::META_KABUPATEN_KOTA,
            self::META_PROVINSI,
            self::META_LATITUDE,
            self::META_LONGITUDE,
        ];
    }

    // * Check apakah metadata mengandung alamat lengkap
    public static function hasCompleteAddress(object $model): bool
    {
        $metadata = $model->metadata ?? [];

        return self::isValid($metadata, true);
    }

    // * Get detail alamat saja (tanpa wilayah)
    public static function getDetailAlamat(object $model): string
    {
        return self::get($model, self::META_DETAIL_ALAMAT, '');
    }

    // * Get kelurahan dari metadata
    public static function getKelurahan(object $model): string
    {
        return self::get($model, self::META_KELURAHAN, '');
    }

    // * Get kecamatan dari metadata
    public static function getKecamatan(object $model): string
    {
        return self::get($model, self::META_KECAMATAN, '');
    }

    // * Get kabupaten/kota dari metadata
    public static function getKabupatenKota(object $model): string
    {
        return self::get($model, self::META_KABUPATEN_KOTA, '');
    }

    /**
     * Get provinsi dari metadata
     */
    public static function getProvinsi(object $model): string
    {
        return self::get($model, self::META_PROVINSI, '');
    }

    /**
     * Get latitude dari metadata
     */
    public static function getLatitude(object $model): ?float
    {
        return self::get($model, self::META_LATITUDE);
    }

    /**
     * Get longitude dari metadata
     */
    public static function getLongitude(object $model): ?float
    {
        return self::get($model, self::META_LONGITUDE);
    }

    /**
     * Get koordinat GPS (latitude, longitude) dari metadata
     */
    public static function getCoordinates(object $model): array
    {
        return [
            'latitude' => self::getLatitude($model),
            'longitude' => self::getLongitude($model),
        ];
    }

    /**
     * Check apakah model memiliki koordinat GPS
     */
    public static function hasCoordinates(object $model): bool
    {
        $latitude = self::getLatitude($model);
        $longitude = self::getLongitude($model);

        return $latitude !== null && $longitude !== null;
    }

    /**
     * Build alamat lengkap string dari komponen regional
     * Semua model (Pelanggan, User, Kurir) menggunakan metadata
     */
    public static function buildAlamatString(object $model): string
    {
        return self::formatAddress($model, true);
    }

    /**
     * Sync kolom alamat (text) dengan komponen regional
     * Auto-fill kolom alamat dari komponen regional
     */
    public static function syncAlamatColumn(object $model): void
    {
        // Extract metadata untuk build alamat
        $metadata = $model->metadata ?? [];

        // Build alamat dari metadata langsung (lebih reliable)
        $alamatString = self::buildAlamatFromArray($metadata);

        // Set alamat jika alamat_string tidak kosong
        if (! empty($alamatString)) {
            $model->alamat = $alamatString;
        } else {
            Log::warning('AddressMetadata: Failed to sync alamat - alamat_string is empty', [
                'model_class' => get_class($model),
                'model_id' => $model->id ?? null,
            ]);
        }
    }

    /**
     * Build alamat dari array data (untuk digunakan saat create/update)
     * Gunakan ini saat menerima input dari form
     */
    public static function buildAlamatFromArray(array $data): string
    {
        $detailAlamat = $data['detail_alamat'] ?? '';
        $kelurahan = $data['kelurahan'] ?? '';
        $kecamatan = $data['kecamatan'] ?? '';
        $kabupatenKota = $data['kabupaten_kota'] ?? $data['kabupaten'] ?? '';
        $provinsi = $data['provinsi'] ?? '';

        return RegionalLocation::formatFullAddress(
            $detailAlamat,
            $kelurahan,
            $kecamatan,
            $kabupatenKota,
            $provinsi
        );
    }
}
