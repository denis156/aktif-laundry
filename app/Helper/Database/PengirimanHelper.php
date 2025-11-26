<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Pengiriman;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola Pengiriman
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Kolom JSON hanya: tracking (array of tracking points)
// ? Kolom lain: lokasi_pickup_latitude, lokasi_pickup_longitude, lokasi_pickup_address, review_rating, review_text

class PengirimanHelper
{
    // * Ambil data lokasi pengambilan cucian (GPS)
    public static function getLokasiPengambilan(Pengiriman $pengiriman): ?array
    {
        if ($pengiriman->lokasi_pickup_latitude && $pengiriman->lokasi_pickup_longitude) {
            return [
                'lat' => (float) $pengiriman->lokasi_pickup_latitude,
                'lng' => (float) $pengiriman->lokasi_pickup_longitude,
                'address' => $pengiriman->lokasi_pickup_address,
            ];
        }

        return null;
    }

    // * Set lokasi pengambilan dengan GPS
    public static function setLokasiPengambilan(Pengiriman $pengiriman, float $lat, float $lng, ?string $address = null): void
    {
        $pengiriman->lokasi_pickup_latitude = $lat;
        $pengiriman->lokasi_pickup_longitude = $lng;
        $pengiriman->lokasi_pickup_address = $address;
    }

    // * Ambil history tracking perjalanan kurir
    public static function getTracking(Pengiriman $pengiriman): array
    {
        return $pengiriman->tracking ?? [];
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
            $pengiriman->tracking = $tracking;
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
    public static function getReview(Pengiriman $pengiriman): ?array
    {
        if ($pengiriman->review_rating || $pengiriman->review_text) {
            return [
                'rating' => $pengiriman->review_rating,
                'text' => $pengiriman->review_text,
            ];
        }

        return null;
    }

    // * Set review dari customer untuk delivery
    public static function setReview(Pengiriman $pengiriman, int $rating, ?string $text = null): void
    {
        $pengiriman->review_rating = $rating;
        $pengiriman->review_text = $text;
    }

    // ! Rules validasi
    public static function validationRules(): array
    {
        return [
            'lokasi_pickup_latitude' => 'nullable|numeric|between:-90,90',
            'lokasi_pickup_longitude' => 'nullable|numeric|between:-180,180',
            'lokasi_pickup_address' => 'nullable|string',
            'review_rating' => 'nullable|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:500',
            'tracking' => 'nullable|array',
        ];
    }

    /**
     * Generate kode pengiriman otomatis
     */
    public static function generateKodePengiriman(): string
    {
        try {
            $prefix = PengaturanHelper::getValue('format_id_pengiriman', 'PNG');
            $prefixLength = strlen($prefix);

            $lastPengiriman = Pengiriman::withTrashed()->orderBy('kode_pengiriman', 'desc')->first();

            if (! $lastPengiriman) {
                return $prefix.'001';
            }

            $lastNumber = (int) substr($lastPengiriman->kode_pengiriman, $prefixLength);
            $nextNumber = $lastNumber + 1;

            // Check if there are any gaps in the numbering
            while (Pengiriman::where('kode_pengiriman', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                $nextNumber++;
            }

            return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Failed to generate kode pengiriman', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get status options untuk dropdown
     */
    public static function getStatusOptions(): array
    {
        return [
            ['id' => 'Menunggu', 'name' => 'Menunggu'],
            ['id' => 'Dijadwalkan', 'name' => 'Dijadwalkan'],
            ['id' => 'Dalam Perjalanan', 'name' => 'Dalam Perjalanan'],
            ['id' => 'Selesai', 'name' => 'Selesai'],
            ['id' => 'Batal', 'name' => 'Batal'],
        ];
    }

    /**
     * Get tipe options untuk dropdown
     */
    public static function getTipeOptions(): array
    {
        return [
            ['id' => 'Jemput', 'name' => 'Jemput'],
            ['id' => 'Antar', 'name' => 'Antar'],
        ];
    }

    /**
     * Hitung durasi pengiriman dalam menit
     */
    public static function hitungDurasi(Pengiriman $pengiriman): ?int
    {
        if (! $pengiriman->waktu_mulai || ! $pengiriman->waktu_selesai) {
            return null;
        }

        return $pengiriman->waktu_mulai->diffInMinutes($pengiriman->waktu_selesai);
    }

    /**
     * Format durasi untuk display
     * Contoh: "1 jam 30 menit" atau "45 menit"
     */
    public static function formatDurasi(Pengiriman $pengiriman): ?string
    {
        $durasi = self::hitungDurasi($pengiriman);

        if ($durasi === null) {
            return null;
        }

        $jam = floor($durasi / 60);
        $menit = $durasi % 60;

        if ($jam > 0 && $menit > 0) {
            return "{$jam} jam {$menit} menit";
        } elseif ($jam > 0) {
            return "{$jam} jam";
        } else {
            return "{$menit} menit";
        }
    }
}
