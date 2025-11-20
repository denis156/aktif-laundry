<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\JenisPakaian;
use Illuminate\Support\Facades\Log;

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
        try {
            $metadata = $jenisPakaian->metadata ?? [];
            data_set($metadata, $key, $value);
            $jenisPakaian->metadata = $metadata;
        } catch (\Exception $e) {
            Log::error('Failed to set JenisPakaian metadata', [
                'jenis_pakaian_id' => $jenisPakaian->id,
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Merge data ke metadata
    public static function mergeMetadata(JenisPakaian $jenisPakaian, array $data): void
    {
        try {
            $jenisPakaian->metadata = array_merge($jenisPakaian->metadata ?? [], $data);
        } catch (\Exception $e) {
            Log::error('Failed to merge JenisPakaian metadata', [
                'jenis_pakaian_id' => $jenisPakaian->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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
