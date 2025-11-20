<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Referral;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola metadata Referral
//
// ? Metadata yang didukung:
// * - reward_history: History pemberian reward (array of rewards)
// * - special_reward: Reward spesial untuk milestone tertentu

class ReferralHelper
{
    public const META_REWARD_HISTORY = 'reward_history';
    public const META_SPECIAL_REWARD = 'special_reward';

    // * Ambil nilai dari metadata
    public static function getMetadata(Referral $referral, string $key, mixed $default = null): mixed
    {
        return data_get($referral->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Referral $referral, string $key, mixed $value): void
    {
        try {
            $metadata = $referral->metadata ?? [];
            data_set($metadata, $key, $value);
            $referral->metadata = $metadata;
        } catch (\Exception $e) {
            Log::error('Failed to set Referral metadata', [
                'referral_id' => $referral->id,
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Referral $referral, array $data): void
    {
        try {
            $referral->metadata = array_merge($referral->metadata ?? [], $data);
        } catch (\Exception $e) {
            Log::error('Failed to merge Referral metadata', [
                'referral_id' => $referral->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Ambil history pemberian reward
    public static function getRewardHistory(Referral $referral): array
    {
        return (array) self::getMetadata($referral, self::META_REWARD_HISTORY, []);
    }

    // * Tambah record ke reward history (pelanggan referee, poin, status)
    public static function addRewardHistory(Referral $referral, int $pelangganRefereeId, int $poin, string $status = 'berhasil'): void
    {
        try {
            $history = self::getRewardHistory($referral);
            $history[] = [
                'pelanggan_referee_id' => $pelangganRefereeId,
                'poin' => $poin,
                'tanggal' => now()->toIso8601String(),
                'status' => $status,
            ];
            self::setMetadata($referral, self::META_REWARD_HISTORY, $history);
        } catch (\Exception $e) {
            Log::error('Failed to add Referral reward history', [
                'referral_id' => $referral->id,
                'pelanggan_referee_id' => $pelangganRefereeId,
                'poin' => $poin,
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Ambil reward spesial untuk milestone tertentu
    public static function getSpecialReward(Referral $referral): ?array
    {
        return self::getMetadata($referral, self::META_SPECIAL_REWARD);
    }

    // * Set reward spesial untuk milestone tertentu
    public static function setSpecialReward(Referral $referral, array $rewardData): void
    {
        self::setMetadata($referral, self::META_SPECIAL_REWARD, $rewardData);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_REWARD_HISTORY => 'nullable|array',
            self::META_SPECIAL_REWARD => 'nullable|array',
        ];
    }

    /**
     * Tambah counter total referral (saat ada yang pakai kode)
     *
     * @param Referral $referral
     * @return void
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
     * Tambah successful referral dan poin (saat referee sudah transaksi)
     *
     * @param Referral $referral
     * @return void
     * @throws \Exception
     */
    public static function addSuccessfulReferral(Referral $referral): void
    {
        try {
            $referral->increment('total_berhasil');
            $referral->increment('total_poin', $referral->poin_referrer);
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
}
