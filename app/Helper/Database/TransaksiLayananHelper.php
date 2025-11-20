<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\TransaksiLayanan;
use Illuminate\Support\Facades\Log;

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
        try {
            $metadata = $transaksiLayanan->metadata ?? [];
            data_set($metadata, $key, $value);
            $transaksiLayanan->metadata = $metadata;
        } catch (\Exception $e) {
            Log::error('Failed to set TransaksiLayanan metadata', [
                'transaksi_layanan_id' => $transaksiLayanan->id,
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Merge data ke metadata
    public static function mergeMetadata(TransaksiLayanan $transaksiLayanan, array $data): void
    {
        try {
            $transaksiLayanan->metadata = array_merge($transaksiLayanan->metadata ?? [], $data);
        } catch (\Exception $e) {
            Log::error('Failed to merge TransaksiLayanan metadata', [
                'transaksi_layanan_id' => $transaksiLayanan->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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

    /**
     * Cek apakah layanan per kilogram
     *
     * @param TransaksiLayanan $transaksiLayanan
     * @return bool
     */
    public static function isPerKg(TransaksiLayanan $transaksiLayanan): bool
    {
        return $transaksiLayanan->berat_kg !== null;
    }

    /**
     * Cek apakah layanan per satuan
     *
     * @param TransaksiLayanan $transaksiLayanan
     * @return bool
     */
    public static function isPerSatuan(TransaksiLayanan $transaksiLayanan): bool
    {
        return $transaksiLayanan->jumlah_satuan !== null;
    }

    /**
     * Dapatkan label untuk tampilan
     * Contoh: "Cuci Kering (3.5 kg)" atau "Setrika (10 pcs)"
     *
     * @param TransaksiLayanan $transaksiLayanan
     * @return string
     */
    public static function getDisplayLabel(TransaksiLayanan $transaksiLayanan): string
    {
        if (self::isPerKg($transaksiLayanan)) {
            return "{$transaksiLayanan->nama_layanan} ({$transaksiLayanan->berat_kg} kg)";
        }

        return "{$transaksiLayanan->nama_layanan} ({$transaksiLayanan->jumlah_satuan} pcs)";
    }

    /**
     * Dapatkan label perhitungan untuk display
     * Contoh: "3.5 kg × Rp 7.000 - Kemeja (5), Celana (3)"
     *
     * @param TransaksiLayanan $transaksiLayanan
     * @return string
     */
    public static function getPerhitunganLabel(TransaksiLayanan $transaksiLayanan): string
    {
        if (self::isPerKg($transaksiLayanan)) {
            $jenisPakaian = '';
            if (!empty($transaksiLayanan->jenis_pakaian)) {
                $items = collect($transaksiLayanan->jenis_pakaian)->map(function ($item) {
                    return "{$item['nama']} ({$item['jumlah']})";
                })->implode(', ');
                $jenisPakaian = " - {$items}";
            }

            return "{$transaksiLayanan->berat_kg} kg × Rp " . number_format($transaksiLayanan->harga_per_kg, 0, ',', '.') . $jenisPakaian;
        }

        return "{$transaksiLayanan->jumlah_satuan} pcs × Rp " . number_format($transaksiLayanan->harga_per_satuan, 0, ',', '.');
    }
}
