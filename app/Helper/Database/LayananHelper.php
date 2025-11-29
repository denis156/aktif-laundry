<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Layanan;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola Layanan
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Kolom: include (JSON), exclude (JSON), min_order, max_order, is_popular, icon

class LayananHelper
{
    // * Ambil daftar apa yang termasuk dalam layanan
    public static function getInclude(Layanan $layanan): array
    {
        return $layanan->include ?? [];
    }

    // * Set daftar apa yang termasuk dalam layanan
    public static function setInclude(Layanan $layanan, array $items): void
    {
        $layanan->include = $items;
    }

    // * Ambil daftar apa yang tidak termasuk dalam layanan
    public static function getExclude(Layanan $layanan): array
    {
        return $layanan->exclude ?? [];
    }

    // * Set daftar apa yang tidak termasuk dalam layanan
    public static function setExclude(Layanan $layanan, array $items): void
    {
        $layanan->exclude = $items;
    }

    // * Ambil minimum order layanan
    public static function getMinOrder(Layanan $layanan): ?int
    {
        return $layanan->min_order;
    }

    // * Set minimum order layanan
    public static function setMinOrder(Layanan $layanan, ?int $value): void
    {
        $layanan->min_order = $value;
    }

    // * Ambil maximum order layanan
    public static function getMaxOrder(Layanan $layanan): ?int
    {
        return $layanan->max_order;
    }

    // * Set maximum order layanan
    public static function setMaxOrder(Layanan $layanan, ?int $value): void
    {
        $layanan->max_order = $value;
    }

    // ? Cek apakah layanan termasuk popular
    public static function isPopular(Layanan $layanan): bool
    {
        return (bool) $layanan->is_popular;
    }

    // * Set layanan sebagai popular atau tidak
    public static function setPopular(Layanan $layanan, bool $value): void
    {
        $layanan->is_popular = $value;
    }

    // * Ambil icon layanan
    public static function getIcon(Layanan $layanan): ?string
    {
        return $layanan->icon;
    }

    // * Set icon layanan
    public static function setIcon(Layanan $layanan, ?string $value): void
    {
        $layanan->icon = $value;
    }

    // ! Rules validasi
    public static function validationRules(): array
    {
        return [
            'include' => 'nullable|array',
            'exclude' => 'nullable|array',
            'min_order' => 'nullable|integer|min:1',
            'max_order' => 'nullable|integer|min:1',
            'is_popular' => 'nullable|boolean',
            'icon' => 'nullable|string|max:100',
        ];
    }

    /**
     * Dapatkan array options layanan untuk dropdown/select
     * Format: ['id' => id, 'name' => 'Nama Layanan - Rp x.xxx/satuan']
     *
     * @param  string  $status  Filter berdasarkan status (default: 'Aktif')
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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Format durasi jam menjadi estimasi waktu yang mudah dibaca
     * Contoh: 24 jam = "Estimasi 1 hari", 30 jam = "Estimasi 1 hari 6 jam"
     */
    public static function formatEstimasiWaktu(int $durasiJam): string
    {
        $estimasiHari = floor($durasiJam / 24);
        $estimasiJam = $durasiJam % 24;

        if ($estimasiHari > 0 && $estimasiJam > 0) {
            return "Estimasi {$estimasiHari} hari {$estimasiJam} jam";
        } elseif ($estimasiHari > 0) {
            return "Estimasi {$estimasiHari} hari";
        } else {
            return "Estimasi {$estimasiJam} jam";
        }
    }

    /**
     * Format harga menjadi string yang mudah dibaca
     */
    public static function formatHarga(?float $harga, ?string $satuan): string
    {
        if ($harga === null) {
            return 'Rp 0/'.($satuan ?? 'satuan');
        }

        return 'Rp '.number_format($harga, 0, ',', '.').'/'.($satuan ?? 'satuan');
    }

    /**
     * Generate kode layanan otomatis
     */
    public static function generateKodeLayanan(): string
    {
        try {
            $prefix = PengaturanHelper::getValue('format_id_layanan', 'LYN');
            $prefixLength = strlen($prefix);

            $lastLayanan = Layanan::withTrashed()->orderBy('kode_layanan', 'desc')->first();

            if (! $lastLayanan) {
                return $prefix.'001';
            }

            $lastNumber = (int) substr($lastLayanan->kode_layanan, $prefixLength);
            $nextNumber = $lastNumber + 1;

            // Check if there are any gaps in the numbering
            while (Layanan::where('kode_layanan', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                $nextNumber++;
            }

            return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Failed to generate kode layanan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
