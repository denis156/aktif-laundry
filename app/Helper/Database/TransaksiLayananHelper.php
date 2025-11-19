<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\TransaksiLayanan;

// ! Helper untuk mengelola metadata TransaksiLayanan
//
// ? Metadata yang didukung:
// * - catatan: Catatan untuk layanan tertentu
// * - petugas: Petugas yang handle layanan ini

class TransaksiLayananHelper
{
    public const META_CATATAN = 'catatan';
    public const META_PETUGAS = 'petugas';

    // * Ambil nilai dari metadata
    public static function getMetadata(TransaksiLayanan $transaksiLayanan, string $key, mixed $default = null): mixed
    {
        return data_get($transaksiLayanan->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(TransaksiLayanan $transaksiLayanan, string $key, mixed $value): void
    {
        $metadata = $transaksiLayanan->metadata ?? [];
        data_set($metadata, $key, $value);
        $transaksiLayanan->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(TransaksiLayanan $transaksiLayanan, array $data): void
    {
        $transaksiLayanan->metadata = array_merge($transaksiLayanan->metadata ?? [], $data);
    }

    // * Ambil catatan untuk layanan tertentu
    public static function getCatatan(TransaksiLayanan $transaksiLayanan): ?string
    {
        return self::getMetadata($transaksiLayanan, self::META_CATATAN);
    }

    // * Set catatan untuk layanan tertentu
    public static function setCatatan(TransaksiLayanan $transaksiLayanan, string $catatan): void
    {
        self::setMetadata($transaksiLayanan, self::META_CATATAN, $catatan);
    }

    // * Ambil nama petugas yang handle layanan ini
    public static function getPetugas(TransaksiLayanan $transaksiLayanan): ?string
    {
        return self::getMetadata($transaksiLayanan, self::META_PETUGAS);
    }

    // * Set nama petugas yang handle layanan ini
    public static function setPetugas(TransaksiLayanan $transaksiLayanan, string $petugas): void
    {
        self::setMetadata($transaksiLayanan, self::META_PETUGAS, $petugas);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_CATATAN => 'nullable|string',
            self::META_PETUGAS => 'nullable|string|max:100',
        ];
    }
}
