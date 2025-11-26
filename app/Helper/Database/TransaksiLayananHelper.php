<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\TransaksiLayanan;

// ! Helper untuk mengelola TransaksiLayanan
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Kolom JSON hanya: jenis_pakaian (array of clothing types untuk per_kg)
// ? Kolom lain: catatan_khusus, berat_kg, jumlah_satuan, harga_per_kg, harga_per_satuan, subtotal

class TransaksiLayananHelper
{
    // * Ambil catatan khusus untuk layanan tertentu
    public static function getCatatanKhusus(TransaksiLayanan $transaksiLayanan): ?string
    {
        return $transaksiLayanan->catatan_khusus;
    }

    // * Set catatan khusus untuk layanan tertentu
    public static function setCatatanKhusus(TransaksiLayanan $transaksiLayanan, ?string $catatan): void
    {
        $transaksiLayanan->catatan_khusus = $catatan;
    }

    // * Ambil array jenis pakaian (untuk per_kg layanan)
    public static function getJenisPakaian(TransaksiLayanan $transaksiLayanan): array
    {
        return $transaksiLayanan->jenis_pakaian ?? [];
    }

    // * Set array jenis pakaian (untuk per_kg layanan)
    public static function setJenisPakaian(TransaksiLayanan $transaksiLayanan, array $jenisPakaian): void
    {
        $transaksiLayanan->jenis_pakaian = $jenisPakaian;
    }

    // ! Rules validasi
    public static function validationRules(): array
    {
        return [
            'catatan_khusus' => 'nullable|string',
            'jenis_pakaian' => 'nullable|array',
            'berat_kg' => 'nullable|numeric|min:0',
            'jumlah_satuan' => 'nullable|integer|min:0',
            'harga_per_kg' => 'nullable|integer|min:0',
            'harga_per_satuan' => 'nullable|integer|min:0',
            'subtotal' => 'required|integer|min:0',
        ];
    }

    /**
     * Cek apakah layanan per kilogram
     */
    public static function isPerKg(TransaksiLayanan $transaksiLayanan): bool
    {
        return $transaksiLayanan->layanan && $transaksiLayanan->layanan->tipe_layanan === 'per_kg';
    }

    /**
     * Cek apakah layanan per satuan
     */
    public static function isPerSatuan(TransaksiLayanan $transaksiLayanan): bool
    {
        return $transaksiLayanan->layanan && $transaksiLayanan->layanan->tipe_layanan === 'per_satuan';
    }

    /**
     * Dapatkan label untuk tampilan
     * Contoh: "Cuci Kering (3.5 kg)" atau "Setrika (10 pcs)"
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
     */
    public static function getPerhitunganLabel(TransaksiLayanan $transaksiLayanan): string
    {
        if (self::isPerKg($transaksiLayanan)) {
            $jenisPakaian = '';
            if (! empty($transaksiLayanan->jenis_pakaian)) {
                $items = collect($transaksiLayanan->jenis_pakaian)->map(function ($item) {
                    return "{$item['nama']} ({$item['jumlah']})";
                })->implode(', ');
                $jenisPakaian = " - {$items}";
            }

            return "{$transaksiLayanan->berat_kg} kg × Rp ".number_format($transaksiLayanan->harga_per_kg, 0, ',', '.').$jenisPakaian;
        }

        return "{$transaksiLayanan->jumlah_satuan} pcs × Rp ".number_format($transaksiLayanan->harga_per_satuan, 0, ',', '.');
    }

    /**
     * Hitung subtotal berdasarkan tipe layanan
     */
    public static function hitungSubtotal(TransaksiLayanan $transaksiLayanan): int
    {
        if (self::isPerKg($transaksiLayanan)) {
            return (int) ($transaksiLayanan->berat_kg * $transaksiLayanan->harga_per_kg);
        }

        return $transaksiLayanan->jumlah_satuan * $transaksiLayanan->harga_per_satuan;
    }

    /**
     * Update subtotal dan sync ke model
     */
    public static function updateSubtotal(TransaksiLayanan $transaksiLayanan): void
    {
        $transaksiLayanan->subtotal = self::hitungSubtotal($transaksiLayanan);
    }
}
