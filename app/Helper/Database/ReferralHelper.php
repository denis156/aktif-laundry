<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Referral;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola Referral
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Kolom: total_referral, total_berhasil, campaign_source
// ? Relationship: promo_referrer_id, promo_referee_id

class ReferralHelper
{
    /**
     * Tambah counter total referral (saat ada yang pakai kode)
     *
     * @throws \Exception
     */
    public static function incrementReferral(Referral $referral): void
    {
        try {
            $referral->increment('total_referral');
        } catch (\Exception $e) {
            Log::error('Failed to increment Referral counter', [
                'referral_id' => $referral->id,
                'kode_referral' => $referral->kode_referral,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Tambah successful referral (saat referee sudah transaksi)
     *
     * @throws \Exception
     */
    public static function addSuccessfulReferral(Referral $referral): void
    {
        try {
            $referral->increment('total_berhasil');
        } catch (\Exception $e) {
            Log::error('Failed to add successful Referral', [
                'referral_id' => $referral->id,
                'kode_referral' => $referral->kode_referral,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get referral options untuk dropdown (kode referral aktif)
     */
    public static function getReferralOptions(): array
    {
        try {
            return Referral::with('pelanggan')
                ->whereHas('pelanggan', function ($query) {
                    $query->where('status', 'Aktif');
                })
                ->orderBy('kode_referral')
                ->get()
                ->map(fn ($referral) => [
                    'id' => $referral->id,
                    'name' => $referral->kode_referral.' - '.($referral->pelanggan->nama ?? 'Unknown'),
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get referral options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Generate kode referral unik
     */
    public static function generateKodeReferral(): string
    {
        try {
            $prefix = PengaturanHelper::getValue('format_id_referral', 'REF');
            $prefixLength = strlen($prefix);

            $lastReferral = Referral::orderBy('kode_referral', 'desc')->first();

            if (! $lastReferral) {
                return $prefix.'001';
            }

            $lastNumber = (int) substr($lastReferral->kode_referral, $prefixLength);
            $nextNumber = $lastNumber + 1;

            // Check if there are any gaps in the numbering
            while (Referral::where('kode_referral', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                $nextNumber++;
            }

            return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Failed to generate kode referral', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Ambil promo untuk referrer (yang mengajak)
     */
    public static function getPromoReferrer(Referral $referral): ?\App\Models\Promo
    {
        return $referral->promoReferrer;
    }

    /**
     * Ambil promo untuk referee (yang diajak)
     */
    public static function getPromoReferee(Referral $referral): ?\App\Models\Promo
    {
        return $referral->promoReferee;
    }

    /**
     * Set campaign source untuk tracking
     */
    public static function setCampaignSource(Referral $referral, ?string $source): void
    {
        $referral->campaign_source = $source;
    }

    /**
     * Get campaign source
     */
    public static function getCampaignSource(Referral $referral): ?string
    {
        return $referral->campaign_source;
    }

    /**
     * Hitung conversion rate (berapa persen yang berhasil transaksi)
     */
    public static function getConversionRate(Referral $referral): float
    {
        if ($referral->total_referral === 0) {
            return 0.0;
        }

        return round(($referral->total_berhasil / $referral->total_referral) * 100, 2);
    }

    // ! Rules validasi
    public static function validationRules(): array
    {
        return [
            'pelanggan_id' => 'required|exists:pelanggan,id',
            'promo_referrer_id' => 'nullable|exists:promo,id',
            'promo_referee_id' => 'nullable|exists:promo,id',
            'campaign_source' => 'nullable|string|max:100',
        ];
    }
}
