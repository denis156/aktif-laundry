<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Promo;

// ! Helper untuk mengelola metadata Promo
//
// ? Metadata yang didukung:
// * - layanan_id: Array ID layanan yang berlaku untuk promo
// * - exclude_pelanggan_id: Array ID pelanggan yang tidak boleh pakai promo
// * - banner_image: Path gambar banner promo
// * - terms_conditions: Syarat dan ketentuan promo
// * - auto_apply: Apakah promo otomatis terapply (boolean)

class PromoHelper
{
    public const META_LAYANAN_ID = 'layanan_id';
    public const META_EXCLUDE_PELANGGAN_ID = 'exclude_pelanggan_id';
    public const META_BANNER_IMAGE = 'banner_image';
    public const META_TERMS_CONDITIONS = 'terms_conditions';
    public const META_AUTO_APPLY = 'auto_apply';

    // * Ambil nilai dari metadata
    public static function getMetadata(Promo $promo, string $key, mixed $default = null): mixed
    {
        return data_get($promo->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Promo $promo, string $key, mixed $value): void
    {
        $metadata = $promo->metadata ?? [];
        data_set($metadata, $key, $value);
        $promo->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Promo $promo, array $data): void
    {
        $promo->metadata = array_merge($promo->metadata ?? [], $data);
    }

    // * Ambil array ID layanan yang berlaku untuk promo
    public static function getLayananId(Promo $promo): array
    {
        return (array) self::getMetadata($promo, self::META_LAYANAN_ID, []);
    }

    // * Set array ID layanan yang berlaku untuk promo
    public static function setLayananId(Promo $promo, array $layananIds): void
    {
        self::setMetadata($promo, self::META_LAYANAN_ID, $layananIds);
    }

    // ? Cek apakah promo berlaku untuk layanan tertentu
    public static function isBerlakuUntukLayanan(Promo $promo, int $layananId): bool
    {
        $layananIds = self::getLayananId($promo);

        // Jika empty, berarti berlaku untuk semua layanan
        if (empty($layananIds)) {
            return true;
        }

        return in_array($layananId, $layananIds, true);
    }

    // * Ambil array ID pelanggan yang tidak boleh pakai promo
    public static function getExcludePelangganId(Promo $promo): array
    {
        return (array) self::getMetadata($promo, self::META_EXCLUDE_PELANGGAN_ID, []);
    }

    // * Set array ID pelanggan yang tidak boleh pakai promo
    public static function setExcludePelangganId(Promo $promo, array $pelangganIds): void
    {
        self::setMetadata($promo, self::META_EXCLUDE_PELANGGAN_ID, $pelangganIds);
    }

    // ? Cek apakah pelanggan termasuk yang di-exclude
    public static function isPelangganExcluded(Promo $promo, int $pelangganId): bool
    {
        $excludedIds = self::getExcludePelangganId($promo);
        return in_array($pelangganId, $excludedIds, true);
    }

    // * Ambil path gambar banner promo
    public static function getBannerImage(Promo $promo): ?string
    {
        return self::getMetadata($promo, self::META_BANNER_IMAGE);
    }

    // * Set path gambar banner promo
    public static function setBannerImage(Promo $promo, string $imagePath): void
    {
        self::setMetadata($promo, self::META_BANNER_IMAGE, $imagePath);
    }

    // * Ambil syarat dan ketentuan promo
    public static function getTermsConditions(Promo $promo): ?string
    {
        return self::getMetadata($promo, self::META_TERMS_CONDITIONS);
    }

    // * Set syarat dan ketentuan promo
    public static function setTermsConditions(Promo $promo, string $terms): void
    {
        self::setMetadata($promo, self::META_TERMS_CONDITIONS, $terms);
    }

    // ? Cek apakah promo otomatis terapply
    public static function isAutoApply(Promo $promo): bool
    {
        return (bool) self::getMetadata($promo, self::META_AUTO_APPLY, false);
    }

    // * Set apakah promo otomatis terapply
    public static function setAutoApply(Promo $promo, bool $value): void
    {
        self::setMetadata($promo, self::META_AUTO_APPLY, $value);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_LAYANAN_ID => 'nullable|array',
            self::META_EXCLUDE_PELANGGAN_ID => 'nullable|array',
            self::META_BANNER_IMAGE => 'nullable|string',
            self::META_TERMS_CONDITIONS => 'nullable|string',
            self::META_AUTO_APPLY => 'nullable|boolean',
        ];
    }
}
