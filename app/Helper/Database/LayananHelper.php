<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Layanan;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola metadata Layanan
//
// ? Metadata yang didukung:
// * - include: Apa saja yang termasuk dalam layanan (array)
// * - exclude: Apa yang tidak termasuk (array)
// * - min_order: Minimum order
// * - max_order: Maximum order
// * - popular: Apakah layanan populer (boolean)
// * - icon: Icon untuk tampilan
// * - deskripsi_detail: Deskripsi lengkap layanan

class LayananHelper
{
    public const META_INCLUDE = 'include';
    public const META_EXCLUDE = 'exclude';
    public const META_MIN_ORDER = 'min_order';
    public const META_MAX_ORDER = 'max_order';
    public const META_POPULAR = 'popular';
    public const META_ICON = 'icon';
    public const META_DESKRIPSI_DETAIL = 'deskripsi_detail';

    // * Ambil nilai dari metadata
    public static function getMetadata(Layanan $layanan, string $key, mixed $default = null): mixed
    {
        return data_get($layanan->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Layanan $layanan, string $key, mixed $value): void
    {
        try {
            $metadata = $layanan->metadata ?? [];
            data_set($metadata, $key, $value);
            $layanan->metadata = $metadata;
        } catch (\Exception $e) {
            Log::error('Failed to set Layanan metadata', [
                'layanan_id' => $layanan->id,
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Layanan $layanan, array $data): void
    {
        try {
            $layanan->metadata = array_merge($layanan->metadata ?? [], $data);
        } catch (\Exception $e) {
            Log::error('Failed to merge Layanan metadata', [
                'layanan_id' => $layanan->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Ambil daftar apa yang termasuk dalam layanan
    public static function getInclude(Layanan $layanan): array
    {
        return (array) self::getMetadata($layanan, self::META_INCLUDE, []);
    }

    // * Set daftar apa yang termasuk dalam layanan
    public static function setInclude(Layanan $layanan, array $items): void
    {
        self::setMetadata($layanan, self::META_INCLUDE, $items);
    }

    // * Ambil daftar apa yang tidak termasuk dalam layanan
    public static function getExclude(Layanan $layanan): array
    {
        return (array) self::getMetadata($layanan, self::META_EXCLUDE, []);
    }

    // * Set daftar apa yang tidak termasuk dalam layanan
    public static function setExclude(Layanan $layanan, array $items): void
    {
        self::setMetadata($layanan, self::META_EXCLUDE, $items);
    }

    // * Ambil minimum order layanan
    public static function getMinOrder(Layanan $layanan): ?int
    {
        return self::getMetadata($layanan, self::META_MIN_ORDER);
    }

    // * Set minimum order layanan
    public static function setMinOrder(Layanan $layanan, int $value): void
    {
        self::setMetadata($layanan, self::META_MIN_ORDER, $value);
    }

    // * Ambil maximum order layanan
    public static function getMaxOrder(Layanan $layanan): ?int
    {
        return self::getMetadata($layanan, self::META_MAX_ORDER);
    }

    // * Set maximum order layanan
    public static function setMaxOrder(Layanan $layanan, int $value): void
    {
        self::setMetadata($layanan, self::META_MAX_ORDER, $value);
    }

    // ? Cek apakah layanan termasuk popular
    public static function isPopular(Layanan $layanan): bool
    {
        return (bool) self::getMetadata($layanan, self::META_POPULAR, false);
    }

    // * Set layanan sebagai popular atau tidak
    public static function setPopular(Layanan $layanan, bool $value): void
    {
        self::setMetadata($layanan, self::META_POPULAR, $value);
    }

    // * Ambil icon layanan
    public static function getIcon(Layanan $layanan): ?string
    {
        return self::getMetadata($layanan, self::META_ICON);
    }

    // * Set icon layanan
    public static function setIcon(Layanan $layanan, string $value): void
    {
        self::setMetadata($layanan, self::META_ICON, $value);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_INCLUDE => 'nullable|array',
            self::META_EXCLUDE => 'nullable|array',
            self::META_MIN_ORDER => 'nullable|integer|min:1',
            self::META_MAX_ORDER => 'nullable|integer|min:1',
            self::META_POPULAR => 'nullable|boolean',
            self::META_ICON => 'nullable|string|max:100',
            self::META_DESKRIPSI_DETAIL => 'nullable|string',
        ];
    }

    /**
     * Dapatkan array options layanan untuk dropdown/select
     * Format: ['id' => id, 'name' => 'Nama Layanan - Rp x.xxx/satuan']
     *
     * @param string $status Filter berdasarkan status (default: 'Aktif')
     * @return array
     */
    public static function getLayananOptions(string $status = 'Aktif'): array
    {
        try {
            return Layanan::where('status', $status)
                ->orderBy('nama_layanan')
                ->get(['id', 'nama_layanan', 'tipe_layanan', 'harga_per_kg', 'harga_per_satuan', 'satuan'])
                ->map(function (Layanan $layanan) {
                    $harga = $layanan->tipe_layanan === 'per_kg'
                        ? 'Rp '.number_format($layanan->harga_per_kg, 0, ',', '.').'/'.$layanan->satuan
                        : 'Rp '.number_format($layanan->harga_per_satuan, 0, ',', '.').'/'.$layanan->satuan;

                    return [
                        'id' => $layanan->id,
                        'name' => $layanan->nama_layanan.' - '.$harga,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get layanan options', [
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }
}
