<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\JenisPakaian;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola JenisPakaian
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Kolom: penanganan_khusus, icon

class JenisPakaianHelper
{
    // * Ambil instruksi penanganan khusus
    public static function getPenangananKhusus(JenisPakaian $jenisPakaian): ?string
    {
        return $jenisPakaian->penanganan_khusus;
    }

    // * Set instruksi penanganan khusus
    public static function setPenangananKhusus(JenisPakaian $jenisPakaian, ?string $penanganan): void
    {
        $jenisPakaian->penanganan_khusus = $penanganan;
    }

    // * Ambil icon jenis pakaian
    public static function getIcon(JenisPakaian $jenisPakaian): ?string
    {
        return $jenisPakaian->icon;
    }

    // * Set icon jenis pakaian
    public static function setIcon(JenisPakaian $jenisPakaian, ?string $icon): void
    {
        $jenisPakaian->icon = $icon;
    }

    // ! Rules validasi
    public static function validationRules(): array
    {
        return [
            'penanganan_khusus' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
        ];
    }

    /**
     * Generate kode jenis pakaian otomatis
     */
    public static function generateKodeJenis(): string
    {
        try {
            $prefix = PengaturanHelper::getValue('format_id_jenis_pakaian', 'JNS');
            $prefixLength = strlen($prefix);

            $lastJenis = JenisPakaian::withTrashed()->orderBy('kode_jenis', 'desc')->first();

            if (! $lastJenis) {
                return $prefix.'001';
            }

            $lastNumber = (int) substr($lastJenis->kode_jenis, $prefixLength);
            $nextNumber = $lastNumber + 1;

            // Check if there are any gaps in the numbering
            while (JenisPakaian::where('kode_jenis', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                $nextNumber++;
            }

            return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Failed to generate kode jenis pakaian', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get jenis pakaian options untuk dropdown (hanya yang aktif)
     */
    public static function getJenisPakaianOptions(string $status = 'Aktif'): array
    {
        try {
            return JenisPakaian::where('status', $status)
                ->orderBy('nama_jenis')
                ->get()
                ->map(fn ($jenis) => [
                    'id' => $jenis->id,
                    'name' => $jenis->nama_jenis,
                    'code' => $jenis->kode_jenis,
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get jenis pakaian options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }
}
