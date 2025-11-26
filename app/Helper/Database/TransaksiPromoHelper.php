<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Promo;
use App\Models\Transaksi;
use App\Models\TransaksiPromo;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola TransaksiPromo
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Snapshot promo untuk audit trail
// ? Support multiple promo per transaksi dengan urutan apply

class TransaksiPromoHelper
{
    // * Tipe penerapan promo
    public const DITERAPKAN_SUBTOTAL = 'subtotal';

    public const DITERAPKAN_LAYANAN = 'layanan_tertentu';

    /**
     * Create snapshot promo untuk transaksi (audit trail)
     * Menyimpan data promo saat transaksi dibuat, jadi kalau promo berubah/dihapus
     * data transaksi lama tetap akurat
     */
    public static function createSnapshot(
        Transaksi $transaksi,
        Promo $promo,
        int $nilaiDiskonNominal,
        ?int $layananId = null,
        int $urutanApply = 1
    ): TransaksiPromo {
        try {
            // Hitung nilai diskon persen jika ada
            $nilaiDiskonPersen = null;
            if ($promo->tipe_diskon === PromoHelper::TIPE_PERSEN) {
                $nilaiDiskonPersen = $promo->nilai_diskon;
            }

            // Hitung gratis kg/hari jika ada
            $gratisKg = null;
            $gratisHari = null;
            if ($promo->tipe_diskon === PromoHelper::TIPE_GRATIS_KG) {
                $gratisKg = $promo->nilai_diskon;
            } elseif ($promo->tipe_diskon === PromoHelper::TIPE_GRATIS_HARI) {
                $gratisHari = $promo->nilai_diskon;
            }

            // Tentukan diterapkan ke mana
            $diterapkanKe = $layananId ? self::DITERAPKAN_LAYANAN : self::DITERAPKAN_SUBTOTAL;

            return TransaksiPromo::create([
                'transaksi_id' => $transaksi->id,
                'promo_id' => $promo->id,
                'layanan_id' => $layananId,
                'kode_promo' => $promo->kode_promo,
                'nama_promo' => $promo->nama_promo,
                'tipe_diskon' => $promo->tipe_diskon,
                'nilai_diskon_persen' => $nilaiDiskonPersen,
                'nilai_diskon_nominal' => $nilaiDiskonNominal,
                'diskon_maksimal' => $promo->diskon_maksimal,
                'gratis_kg' => $gratisKg,
                'gratis_hari' => $gratisHari,
                'diterapkan_ke' => $diterapkanKe,
                'urutan_apply' => $urutanApply,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create TransaksiPromo snapshot', [
                'transaksi_id' => $transaksi->id,
                'promo_id' => $promo->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get total diskon nominal dari semua promo yang diterapkan
     */
    public static function getTotalDiskonNominal(Transaksi $transaksi): int
    {
        return TransaksiPromo::where('transaksi_id', $transaksi->id)
            ->sum('nilai_diskon_nominal');
    }

    /**
     * Get semua promo yang diterapkan, sorted by urutan_apply
     */
    public static function getPromoList(Transaksi $transaksi): \Illuminate\Database\Eloquent\Collection
    {
        return TransaksiPromo::where('transaksi_id', $transaksi->id)
            ->orderBy('urutan_apply')
            ->get();
    }

    /**
     * Get promo yang diterapkan ke subtotal
     */
    public static function getPromoSubtotal(Transaksi $transaksi): \Illuminate\Database\Eloquent\Collection
    {
        return TransaksiPromo::where('transaksi_id', $transaksi->id)
            ->where('diterapkan_ke', self::DITERAPKAN_SUBTOTAL)
            ->orderBy('urutan_apply')
            ->get();
    }

    /**
     * Get promo yang diterapkan ke layanan tertentu
     */
    public static function getPromoLayanan(Transaksi $transaksi, ?int $layananId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = TransaksiPromo::where('transaksi_id', $transaksi->id)
            ->where('diterapkan_ke', self::DITERAPKAN_LAYANAN);

        if ($layananId) {
            $query->where('layanan_id', $layananId);
        }

        return $query->orderBy('urutan_apply')->get();
    }

    /**
     * Format label promo untuk display
     * Contoh: "DISKON50 - Diskon 50% (Maks Rp 50.000)"
     */
    public static function formatLabel(TransaksiPromo $transaksiPromo): string
    {
        $label = "{$transaksiPromo->kode_promo} - {$transaksiPromo->nama_promo}";

        // Tambahkan detail diskon
        if ($transaksiPromo->tipe_diskon === PromoHelper::TIPE_PERSEN) {
            $detail = "Diskon {$transaksiPromo->nilai_diskon_persen}%";
            if ($transaksiPromo->diskon_maksimal) {
                $detail .= ' (Maks Rp '.number_format($transaksiPromo->diskon_maksimal, 0, ',', '.').')';
            }
            $label .= " - {$detail}";
        } elseif ($transaksiPromo->tipe_diskon === PromoHelper::TIPE_NOMINAL) {
            $label .= ' - Potongan Rp '.number_format($transaksiPromo->nilai_diskon_nominal, 0, ',', '.');
        } elseif ($transaksiPromo->tipe_diskon === PromoHelper::TIPE_GRATIS_KG) {
            $label .= " - Gratis {$transaksiPromo->gratis_kg} kg";
        } elseif ($transaksiPromo->tipe_diskon === PromoHelper::TIPE_GRATIS_HARI) {
            $label .= " - Gratis Express {$transaksiPromo->gratis_hari} hari";
        } elseif ($transaksiPromo->tipe_diskon === PromoHelper::TIPE_GRATIS_ONGKIR) {
            $label .= ' - Gratis Ongkir';
        } elseif ($transaksiPromo->tipe_diskon === PromoHelper::TIPE_CASHBACK) {
            $label .= ' - Cashback Rp '.number_format($transaksiPromo->nilai_diskon_nominal, 0, ',', '.');
        }

        return $label;
    }

    /**
     * Cek apakah transaksi punya promo gratis ongkir
     */
    public static function hasGratisOngkir(Transaksi $transaksi): bool
    {
        return TransaksiPromo::where('transaksi_id', $transaksi->id)
            ->where('tipe_diskon', PromoHelper::TIPE_GRATIS_ONGKIR)
            ->exists();
    }

    /**
     * Cek apakah transaksi punya promo cashback
     */
    public static function hasCashback(Transaksi $transaksi): bool
    {
        return TransaksiPromo::where('transaksi_id', $transaksi->id)
            ->where('tipe_diskon', PromoHelper::TIPE_CASHBACK)
            ->exists();
    }

    /**
     * Get total cashback dari semua promo
     */
    public static function getTotalCashback(Transaksi $transaksi): int
    {
        return TransaksiPromo::where('transaksi_id', $transaksi->id)
            ->where('tipe_diskon', PromoHelper::TIPE_CASHBACK)
            ->sum('nilai_diskon_nominal');
    }

    /**
     * Get total gratis kg dari semua promo
     */
    public static function getTotalGratisKg(Transaksi $transaksi): int
    {
        return TransaksiPromo::where('transaksi_id', $transaksi->id)
            ->where('tipe_diskon', PromoHelper::TIPE_GRATIS_KG)
            ->sum('gratis_kg') ?? 0;
    }

    /**
     * Delete semua promo dari transaksi (untuk recalculate)
     */
    public static function clearAll(Transaksi $transaksi): void
    {
        try {
            TransaksiPromo::where('transaksi_id', $transaksi->id)->delete();
        } catch (\Exception $e) {
            Log::error('Failed to clear TransaksiPromo', [
                'transaksi_id' => $transaksi->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // ! Rules validasi
    public static function validationRules(): array
    {
        return [
            'transaksi_id' => 'required|exists:transaksi,id',
            'promo_id' => 'required|exists:promo,id',
            'layanan_id' => 'nullable|exists:layanan,id',
            'kode_promo' => 'required|string|max:50',
            'nama_promo' => 'required|string|max:255',
            'tipe_diskon' => 'required|string|max:50',
            'nilai_diskon_persen' => 'nullable|integer|min:0|max:100',
            'nilai_diskon_nominal' => 'required|integer|min:0',
            'diskon_maksimal' => 'nullable|integer|min:0',
            'gratis_kg' => 'nullable|integer|min:0',
            'gratis_hari' => 'nullable|integer|min:0',
            'diterapkan_ke' => 'required|in:subtotal,layanan_tertentu',
            'urutan_apply' => 'required|integer|min:1',
        ];
    }
}
