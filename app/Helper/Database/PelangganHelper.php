<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Pelanggan;

// ! Helper untuk mengelola metadata Pelanggan
//
// ? Metadata yang didukung:
// * - member_card: Nomor kartu member
// * - loyalty_points: Poin loyalty pelanggan
// * - preferensi_pengiriman: Pilihan antar_jemput atau ambil_sendiri

class PelangganHelper
{
    public const META_MEMBER_CARD = 'member_card';
    public const META_LOYALTY_POINTS = 'loyalty_points';
    public const META_PREFERENSI_PENGIRIMAN = 'preferensi_pengiriman';

    // * Ambil nilai dari metadata
    public static function getMetadata(Pelanggan $pelanggan, string $key, mixed $default = null): mixed
    {
        return data_get($pelanggan->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Pelanggan $pelanggan, string $key, mixed $value): void
    {
        $metadata = $pelanggan->metadata ?? [];
        data_set($metadata, $key, $value);
        $pelanggan->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Pelanggan $pelanggan, array $data): void
    {
        $pelanggan->metadata = array_merge($pelanggan->metadata ?? [], $data);
    }

    // * Ambil total poin loyalty pelanggan
    public static function getLoyaltyPoints(Pelanggan $pelanggan): int
    {
        return (int) self::getMetadata($pelanggan, self::META_LOYALTY_POINTS, 0);
    }

    // * Set total poin loyalty pelanggan
    public static function setLoyaltyPoints(Pelanggan $pelanggan, int $points): void
    {
        self::setMetadata($pelanggan, self::META_LOYALTY_POINTS, $points);
    }

    // * Tambah poin loyalty (reward, cashback, dll)
    public static function addLoyaltyPoints(Pelanggan $pelanggan, int $points): void
    {
        $current = self::getLoyaltyPoints($pelanggan);
        self::setLoyaltyPoints($pelanggan, $current + $points);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_MEMBER_CARD => 'nullable|string|max:50',
            self::META_LOYALTY_POINTS => 'nullable|integer|min:0',
            self::META_PREFERENSI_PENGIRIMAN => 'nullable|in:antar_jemput,ambil_sendiri',
        ];
    }
}
