<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Pengiriman;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola metadata Pengiriman
//
// ? Metadata yang didukung:
// * - lokasi_pengambilan: GPS koordinat titik pengambilan cucian
// * - tracking: History tracking perjalanan kurir (array of checkpoints)
// * - review_customer: Review dari customer untuk delivery

class PengirimanHelper
{
    public const META_LOKASI_PENGAMBILAN = 'lokasi_pengambilan';

    public const META_TRACKING = 'tracking';

    public const META_REVIEW_CUSTOMER = 'review_customer';

    // * Ambil nilai dari metadata
    public static function getMetadata(Pengiriman $pengiriman, string $key, mixed $default = null): mixed
    {
        return data_get($pengiriman->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Pengiriman $pengiriman, string $key, mixed $value): void
    {
        try {
            $metadata = $pengiriman->metadata ?? [];
            data_set($metadata, $key, $value);
            $pengiriman->metadata = $metadata;
        } catch (\Exception $e) {
            Log::error('Failed to set Pengiriman metadata', [
                'pengiriman_id' => $pengiriman->id,
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Pengiriman $pengiriman, array $data): void
    {
        try {
            $pengiriman->metadata = array_merge($pengiriman->metadata ?? [], $data);
        } catch (\Exception $e) {
            Log::error('Failed to merge Pengiriman metadata', [
                'pengiriman_id' => $pengiriman->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Ambil data lokasi pengambilan cucian (GPS)
    public static function getLokasiPengambilan(Pengiriman $pengiriman): ?array
    {
        return self::getMetadata($pengiriman, self::META_LOKASI_PENGAMBILAN);
    }

    // * Set lokasi pengambilan dengan GPS
    public static function setLokasiPengambilan(Pengiriman $pengiriman, float $lat, float $lng, ?string $address = null): void
    {
        self::setMetadata($pengiriman, self::META_LOKASI_PENGAMBILAN, [
            'lat' => $lat,
            'lng' => $lng,
            'address' => $address,
        ]);
    }

    // * Ambil history tracking perjalanan kurir
    public static function getTracking(Pengiriman $pengiriman): array
    {
        return (array) self::getMetadata($pengiriman, self::META_TRACKING, []);
    }

    // * Tambah checkpoint tracking (status, waktu, lokasi, keterangan)
    public static function addTracking(Pengiriman $pengiriman, string $status, ?float $lat = null, ?float $lng = null, ?string $keterangan = null): void
    {
        try {
            $tracking = self::getTracking($pengiriman);
            $tracking[] = [
                'status' => $status,
                'waktu' => now()->toIso8601String(),
                'lat' => $lat,
                'lng' => $lng,
                'keterangan' => $keterangan,
            ];
            self::setMetadata($pengiriman, self::META_TRACKING, $tracking);
        } catch (\Exception $e) {
            Log::error('Failed to add Pengiriman tracking', [
                'pengiriman_id' => $pengiriman->id,
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Ambil review dari customer untuk delivery
    public static function getReviewCustomer(Pengiriman $pengiriman): ?string
    {
        return self::getMetadata($pengiriman, self::META_REVIEW_CUSTOMER);
    }

    // * Set review dari customer untuk delivery
    public static function setReviewCustomer(Pengiriman $pengiriman, string $review): void
    {
        self::setMetadata($pengiriman, self::META_REVIEW_CUSTOMER, $review);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_LOKASI_PENGAMBILAN => 'nullable|array',
            self::META_TRACKING => 'nullable|array',
            self::META_REVIEW_CUSTOMER => 'nullable|string|max:500',
        ];
    }
}
