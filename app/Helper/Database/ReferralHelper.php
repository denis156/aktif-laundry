<?php

declare(strict_types=1);

namespace App\Helpers\Database;

use App\Models\Referral;

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
        $metadata = $referral->metadata ?? [];
        data_set($metadata, $key, $value);
        $referral->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Referral $referral, array $data): void
    {
        $referral->metadata = array_merge($referral->metadata ?? [], $data);
    }

    // * Ambil history pemberian reward
    public static function getRewardHistory(Referral $referral): array
    {
        return (array) self::getMetadata($referral, self::META_REWARD_HISTORY, []);
    }

    // * Tambah record ke reward history (pelanggan referee, poin, status)
    public static function addRewardHistory(Referral $referral, int $pelangganRefereeId, int $poin, string $status = 'berhasil'): void
    {
        $history = self::getRewardHistory($referral);
        $history[] = [
            'pelanggan_referee_id' => $pelangganRefereeId,
            'poin' => $poin,
            'tanggal' => now()->toIso8601String(),
            'status' => $status,
        ];
        self::setMetadata($referral, self::META_REWARD_HISTORY, $history);
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
}
