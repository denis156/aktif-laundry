<?php

declare(strict_types=1);

namespace App\Observers;

use App\Helper\Database\PengaturanHelper;
use App\Helper\Database\ReferralHelper;
use App\Models\Pelanggan;
use App\Models\Referral;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PelangganObserver
{
    /**
     * Handle the Pelanggan "created" event.
     * Auto-generate referral code jika sistem referral aktif
     */
    public function created(Pelanggan $pelanggan): void
    {
        // Check if referral system is enabled
        $isReferralEnabled = (bool) PengaturanHelper::getValue('enable_referral', false);

        if (! $isReferralEnabled) {
            return;
        }

        try {
            // Get default promo settings
            $defaultPromoReferrerId = PengaturanHelper::getValue('default_promo_referrer_id', null);
            $defaultPromoRefereeId = PengaturanHelper::getValue('default_promo_referee_id', null);

            // Generate referral code
            $kodeReferral = ReferralHelper::generateKodeReferral();

            // Create referral record
            Referral::create([
                'pelanggan_id' => $pelanggan->id,
                'kode_referral' => $kodeReferral,
                'promo_referrer_id' => $defaultPromoReferrerId,
                'promo_referee_id' => $defaultPromoRefereeId,
                'total_referral' => 0,
                'total_berhasil' => 0,
                'campaign_source' => null,
            ]);

            Log::info('Referral code auto-generated for pelanggan', [
                'pelanggan_id' => $pelanggan->id,
                'kode_referral' => $kodeReferral,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to auto-generate referral code', [
                'pelanggan_id' => $pelanggan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw exception - pelanggan creation should still succeed
        }
    }

    /**
     * Handle the Pelanggan "updating" event.
     * Event ini dipanggil sebelum update, jadi kita bisa cek avatar lama
     */
    public function updating(Pelanggan $pelanggan): void
    {
        // Cek apakah avatar_url berubah
        if ($pelanggan->isDirty('avatar_url')) {
            // Ambil avatar lama dari database (original)
            $oldAvatar = $pelanggan->getOriginal('avatar_url');

            // Hapus avatar lama jika ada
            if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }
    }

    /**
     * Handle the Pelanggan "deleted" event.
     * Hapus avatar saat pelanggan dihapus
     */
    public function deleted(Pelanggan $pelanggan): void
    {
        // Hapus avatar jika ada
        if ($pelanggan->avatar_url && Storage::disk('public')->exists($pelanggan->avatar_url)) {
            Storage::disk('public')->delete($pelanggan->avatar_url);
        }
    }

    /**
     * Handle the Pelanggan "force deleted" event.
     * Hapus avatar saat pelanggan di-force delete (soft delete)
     */
    public function forceDeleted(Pelanggan $pelanggan): void
    {
        // Hapus avatar jika ada
        if ($pelanggan->avatar_url && Storage::disk('public')->exists($pelanggan->avatar_url)) {
            Storage::disk('public')->delete($pelanggan->avatar_url);
        }
    }
}
