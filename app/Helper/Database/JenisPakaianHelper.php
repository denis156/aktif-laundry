<?php

declare(strict_types=1);

namespace App\Helpers\Database;

use App\Models\JenisPakaian;

// ! Helper untuk mengelola metadata JenisPakaian
//
// ? Metadata yang didukung:
// * - penanganan_khusus: Instruksi khusus untuk pakaian putih atau baju spesial

class JenisPakaianHelper
{
    public const META_PENANGANAN_KHUSUS = 'penanganan_khusus';

    // * Ambil nilai dari metadata
    public static function getMetadata(JenisPakaian $jenisPakaian, string $key, mixed $default = null): mixed
    {
        return data_get($jenisPakaian->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(JenisPakaian $jenisPakaian, string $key, mixed $value): void
    {
        $metadata = $jenisPakaian->metadata ?? [];
        data_set($metadata, $key, $value);
        $jenisPakaian->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(JenisPakaian $jenisPakaian, array $data): void
    {
        $jenisPakaian->metadata = array_merge($jenisPakaian->metadata ?? [], $data);
    }

    // * Ambil instruksi penanganan khusus
    public static function getPenangananKhusus(JenisPakaian $jenisPakaian): ?string
    {
        return self::getMetadata($jenisPakaian, self::META_PENANGANAN_KHUSUS);
    }

    // * Set instruksi penanganan khusus
    public static function setPenangananKhusus(JenisPakaian $jenisPakaian, string $penanganan): void
    {
        self::setMetadata($jenisPakaian, self::META_PENANGANAN_KHUSUS, $penanganan);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_PENANGANAN_KHUSUS => 'nullable|string',
        ];
    }
}
