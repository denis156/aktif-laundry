<?php

declare(strict_types=1);

use App\Helper\Database\ReferralHelper;
use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

describe('ReferralHelper - Metadata Operations', function () {
    describe('getMetadata', function () {
        test('retrieves metadata value correctly', function () {
            $referral = Referral::factory()->create([
                'metadata' => ['test_key' => 'test_value'],
            ]);

            $result = ReferralHelper::getMetadata($referral, 'test_key');

            expect($result)->toBe('test_value');
        });

        test('returns default value when key not found', function () {
            $referral = Referral::factory()->create(['metadata' => []]);

            $result = ReferralHelper::getMetadata($referral, 'nonexistent', 'default');

            expect($result)->toBe('default');
        });

        test('returns null when key not found and no default', function () {
            $referral = Referral::factory()->create(['metadata' => []]);

            $result = ReferralHelper::getMetadata($referral, 'nonexistent');

            expect($result)->toBeNull();
        });
    });

    describe('setMetadata', function () {
        test('sets metadata value correctly', function () {
            $referral = Referral::factory()->create(['metadata' => []]);

            ReferralHelper::setMetadata($referral, 'test_key', 'test_value');

            expect($referral->metadata)->toHaveKey('test_key');
            expect($referral->metadata['test_key'])->toBe('test_value');
        });

        test('preserves existing metadata when setting new key', function () {
            $referral = Referral::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            ReferralHelper::setMetadata($referral, 'new_key', 'new_value');

            expect($referral->metadata)->toHaveKey('existing');
            expect($referral->metadata)->toHaveKey('new_key');
        });

        test('handles null metadata gracefully', function () {
            $referral = Referral::factory()->create(['metadata' => null]);

            ReferralHelper::setMetadata($referral, 'key', 'value');

            expect($referral->metadata)->toBeArray();
            expect($referral->metadata['key'])->toBe('value');
        });
    });

    describe('mergeMetadata', function () {
        test('merges new data with existing metadata', function () {
            $referral = Referral::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            ReferralHelper::mergeMetadata($referral, ['new' => 'data']);

            expect($referral->metadata)->toHaveKey('existing');
            expect($referral->metadata)->toHaveKey('new');
        });

        test('overwrites existing keys when merging', function () {
            $referral = Referral::factory()->create([
                'metadata' => ['key' => 'old'],
            ]);

            ReferralHelper::mergeMetadata($referral, ['key' => 'new']);

            expect($referral->metadata['key'])->toBe('new');
        });
    });
});

describe('ReferralHelper - Reward History Operations', function () {
    test('getRewardHistory returns empty array when not set', function () {
        $referral = Referral::factory()->create(['metadata' => []]);

        $result = ReferralHelper::getRewardHistory($referral);

        expect($result)->toBeArray();
        expect($result)->toBeEmpty();
    });

    test('getRewardHistory retrieves reward history', function () {
        $history = [
            ['pelanggan_referee_id' => 1, 'poin' => 100, 'status' => 'berhasil'],
            ['pelanggan_referee_id' => 2, 'poin' => 100, 'status' => 'berhasil'],
        ];
        $referral = Referral::factory()->create([
            'metadata' => [ReferralHelper::META_REWARD_HISTORY => $history],
        ]);

        $result = ReferralHelper::getRewardHistory($referral);

        expect($result)->toBe($history);
    });

    test('addRewardHistory adds reward record to history', function () {
        $referral = Referral::factory()->create(['metadata' => []]);

        ReferralHelper::addRewardHistory($referral, 1, 100, 'berhasil');

        $history = ReferralHelper::getRewardHistory($referral);
        expect($history)->toHaveCount(1);
        expect($history[0]['pelanggan_referee_id'])->toBe(1);
        expect($history[0]['poin'])->toBe(100);
        expect($history[0]['status'])->toBe('berhasil');
        expect($history[0])->toHaveKey('tanggal');
    });

    test('addRewardHistory appends to existing history', function () {
        $referral = Referral::factory()->create([
            'metadata' => [
                ReferralHelper::META_REWARD_HISTORY => [
                    ['pelanggan_referee_id' => 1, 'poin' => 100, 'status' => 'berhasil'],
                ],
            ],
        ]);

        ReferralHelper::addRewardHistory($referral, 2, 150, 'berhasil');

        $history = ReferralHelper::getRewardHistory($referral);
        expect($history)->toHaveCount(2);
        expect($history[1]['pelanggan_referee_id'])->toBe(2);
        expect($history[1]['poin'])->toBe(150);
    });

    test('addRewardHistory uses default status berhasil', function () {
        $referral = Referral::factory()->create(['metadata' => []]);

        ReferralHelper::addRewardHistory($referral, 1, 100);

        $history = ReferralHelper::getRewardHistory($referral);
        expect($history[0]['status'])->toBe('berhasil');
    });
});

describe('ReferralHelper - Special Reward Operations', function () {
    test('getSpecialReward returns null when not set', function () {
        $referral = Referral::factory()->create(['metadata' => []]);

        $result = ReferralHelper::getSpecialReward($referral);

        expect($result)->toBeNull();
    });

    test('getSpecialReward retrieves special reward data', function () {
        $rewardData = ['milestone' => 10, 'bonus' => 500, 'description' => '10 referrals bonus'];
        $referral = Referral::factory()->create([
            'metadata' => [ReferralHelper::META_SPECIAL_REWARD => $rewardData],
        ]);

        $result = ReferralHelper::getSpecialReward($referral);

        expect($result)->toBe($rewardData);
    });

    test('setSpecialReward sets special reward data', function () {
        $referral = Referral::factory()->create(['metadata' => []]);
        $rewardData = ['milestone' => 20, 'bonus' => 1000, 'description' => '20 referrals bonus'];

        ReferralHelper::setSpecialReward($referral, $rewardData);

        expect($referral->metadata[ReferralHelper::META_SPECIAL_REWARD])->toBe($rewardData);
    });
});

describe('ReferralHelper - Counter Operations', function () {
    test('incrementReferral increases total_referral by 1', function () {
        $referral = Referral::factory()->create([
            'total_referral' => 5,
        ]);

        ReferralHelper::incrementReferral($referral);
        $referral->refresh();

        expect($referral->total_referral)->toBe(6);
    });

    test('addSuccessfulReferral increases total_berhasil and total_poin', function () {
        $referral = Referral::factory()->create([
            'total_berhasil' => 3,
            'total_poin' => 300,
            'poin_referrer' => 100,
        ]);

        ReferralHelper::addSuccessfulReferral($referral);
        $referral->refresh();

        expect($referral->total_berhasil)->toBe(4);
        expect($referral->total_poin)->toBe(400);
    });

    test('addSuccessfulReferral adds correct poin amount based on poin_referrer', function () {
        $referral = Referral::factory()->create([
            'total_berhasil' => 0,
            'total_poin' => 0,
            'poin_referrer' => 150,
        ]);

        ReferralHelper::addSuccessfulReferral($referral);
        $referral->refresh();

        expect($referral->total_berhasil)->toBe(1);
        expect($referral->total_poin)->toBe(150);
    });
});

describe('ReferralHelper - Validation Rules', function () {
    test('metadataRules returns correct validation rules', function () {
        $rules = ReferralHelper::metadataRules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey(ReferralHelper::META_REWARD_HISTORY);
        expect($rules)->toHaveKey(ReferralHelper::META_SPECIAL_REWARD);
    });
});
