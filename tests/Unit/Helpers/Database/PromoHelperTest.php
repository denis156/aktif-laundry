<?php

declare(strict_types=1);

use App\Helper\Database\PromoHelper;
use App\Models\Promo;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

describe('PromoHelper - Metadata Operations', function () {
    describe('getMetadata', function () {
        test('retrieves metadata value correctly', function () {
            $promo = Promo::factory()->create([
                'metadata' => ['test_key' => 'test_value'],
            ]);

            $result = PromoHelper::getMetadata($promo, 'test_key');

            expect($result)->toBe('test_value');
        });

        test('returns default value when key not found', function () {
            $promo = Promo::factory()->create(['metadata' => []]);

            $result = PromoHelper::getMetadata($promo, 'nonexistent', 'default');

            expect($result)->toBe('default');
        });

        test('returns null when key not found and no default', function () {
            $promo = Promo::factory()->create(['metadata' => []]);

            $result = PromoHelper::getMetadata($promo, 'nonexistent');

            expect($result)->toBeNull();
        });
    });

    describe('setMetadata', function () {
        test('sets metadata value correctly', function () {
            $promo = Promo::factory()->create(['metadata' => []]);

            PromoHelper::setMetadata($promo, 'test_key', 'test_value');

            expect($promo->metadata)->toHaveKey('test_key');
            expect($promo->metadata['test_key'])->toBe('test_value');
        });

        test('preserves existing metadata when setting new key', function () {
            $promo = Promo::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            PromoHelper::setMetadata($promo, 'new_key', 'new_value');

            expect($promo->metadata)->toHaveKey('existing');
            expect($promo->metadata)->toHaveKey('new_key');
        });

        test('handles null metadata gracefully', function () {
            $promo = Promo::factory()->create(['metadata' => null]);

            PromoHelper::setMetadata($promo, 'key', 'value');

            expect($promo->metadata)->toBeArray();
            expect($promo->metadata['key'])->toBe('value');
        });
    });

    describe('mergeMetadata', function () {
        test('merges new data with existing metadata', function () {
            $promo = Promo::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            PromoHelper::mergeMetadata($promo, ['new' => 'data']);

            expect($promo->metadata)->toHaveKey('existing');
            expect($promo->metadata)->toHaveKey('new');
        });

        test('overwrites existing keys when merging', function () {
            $promo = Promo::factory()->create([
                'metadata' => ['key' => 'old'],
            ]);

            PromoHelper::mergeMetadata($promo, ['key' => 'new']);

            expect($promo->metadata['key'])->toBe('new');
        });
    });
});

describe('PromoHelper - Layanan Operations', function () {
    test('getLayananId returns empty array when not set', function () {
        $promo = Promo::factory()->create(['metadata' => []]);

        $result = PromoHelper::getLayananId($promo);

        expect($result)->toBeArray();
        expect($result)->toBeEmpty();
    });

    test('getLayananId retrieves layanan IDs', function () {
        $layananIds = [1, 2, 3];
        $promo = Promo::factory()->create([
            'metadata' => [PromoHelper::META_LAYANAN_ID => $layananIds],
        ]);

        $result = PromoHelper::getLayananId($promo);

        expect($result)->toBe($layananIds);
    });

    test('setLayananId sets layanan IDs correctly', function () {
        $promo = Promo::factory()->create(['metadata' => []]);
        $layananIds = [1, 2, 3];

        PromoHelper::setLayananId($promo, $layananIds);

        expect($promo->metadata[PromoHelper::META_LAYANAN_ID])->toBe($layananIds);
    });

    test('isBerlakuUntukLayanan returns true when layanan list is empty', function () {
        $promo = Promo::factory()->create(['metadata' => []]);

        $result = PromoHelper::isBerlakuUntukLayanan($promo, 5);

        expect($result)->toBeTrue();
    });

    test('isBerlakuUntukLayanan returns true when layanan is in list', function () {
        $promo = Promo::factory()->create([
            'metadata' => [PromoHelper::META_LAYANAN_ID => [1, 2, 3]],
        ]);

        $result = PromoHelper::isBerlakuUntukLayanan($promo, 2);

        expect($result)->toBeTrue();
    });

    test('isBerlakuUntukLayanan returns false when layanan is not in list', function () {
        $promo = Promo::factory()->create([
            'metadata' => [PromoHelper::META_LAYANAN_ID => [1, 2, 3]],
        ]);

        $result = PromoHelper::isBerlakuUntukLayanan($promo, 5);

        expect($result)->toBeFalse();
    });
});

describe('PromoHelper - Pelanggan Exclusion Operations', function () {
    test('getExcludePelangganId returns empty array when not set', function () {
        $promo = Promo::factory()->create(['metadata' => []]);

        $result = PromoHelper::getExcludePelangganId($promo);

        expect($result)->toBeArray();
        expect($result)->toBeEmpty();
    });

    test('getExcludePelangganId retrieves excluded pelanggan IDs', function () {
        $excludedIds = [10, 20, 30];
        $promo = Promo::factory()->create([
            'metadata' => [PromoHelper::META_EXCLUDE_PELANGGAN_ID => $excludedIds],
        ]);

        $result = PromoHelper::getExcludePelangganId($promo);

        expect($result)->toBe($excludedIds);
    });

    test('setExcludePelangganId sets excluded pelanggan IDs', function () {
        $promo = Promo::factory()->create(['metadata' => []]);
        $excludedIds = [10, 20, 30];

        PromoHelper::setExcludePelangganId($promo, $excludedIds);

        expect($promo->metadata[PromoHelper::META_EXCLUDE_PELANGGAN_ID])->toBe($excludedIds);
    });

    test('isPelangganExcluded returns true when pelanggan is excluded', function () {
        $promo = Promo::factory()->create([
            'metadata' => [PromoHelper::META_EXCLUDE_PELANGGAN_ID => [10, 20, 30]],
        ]);

        $result = PromoHelper::isPelangganExcluded($promo, 20);

        expect($result)->toBeTrue();
    });

    test('isPelangganExcluded returns false when pelanggan is not excluded', function () {
        $promo = Promo::factory()->create([
            'metadata' => [PromoHelper::META_EXCLUDE_PELANGGAN_ID => [10, 20, 30]],
        ]);

        $result = PromoHelper::isPelangganExcluded($promo, 50);

        expect($result)->toBeFalse();
    });

    test('isPelangganExcluded returns false when exclusion list is empty', function () {
        $promo = Promo::factory()->create(['metadata' => []]);

        $result = PromoHelper::isPelangganExcluded($promo, 50);

        expect($result)->toBeFalse();
    });
});

describe('PromoHelper - Banner and Terms Operations', function () {
    test('getBannerImage returns null when not set', function () {
        $promo = Promo::factory()->create(['metadata' => []]);

        $result = PromoHelper::getBannerImage($promo);

        expect($result)->toBeNull();
    });

    test('getBannerImage retrieves banner image path', function () {
        $imagePath = '/storage/promos/banner.jpg';
        $promo = Promo::factory()->create([
            'metadata' => [PromoHelper::META_BANNER_IMAGE => $imagePath],
        ]);

        $result = PromoHelper::getBannerImage($promo);

        expect($result)->toBe($imagePath);
    });

    test('setBannerImage sets banner image path', function () {
        $promo = Promo::factory()->create(['metadata' => []]);
        $imagePath = '/storage/promos/banner.jpg';

        PromoHelper::setBannerImage($promo, $imagePath);

        expect($promo->metadata[PromoHelper::META_BANNER_IMAGE])->toBe($imagePath);
    });

    test('getTermsConditions returns null when not set', function () {
        $promo = Promo::factory()->create(['metadata' => []]);

        $result = PromoHelper::getTermsConditions($promo);

        expect($result)->toBeNull();
    });

    test('getTermsConditions retrieves terms and conditions', function () {
        $terms = 'Syarat dan ketentuan berlaku';
        $promo = Promo::factory()->create([
            'metadata' => [PromoHelper::META_TERMS_CONDITIONS => $terms],
        ]);

        $result = PromoHelper::getTermsConditions($promo);

        expect($result)->toBe($terms);
    });

    test('setTermsConditions sets terms and conditions', function () {
        $promo = Promo::factory()->create(['metadata' => []]);
        $terms = 'Syarat dan ketentuan berlaku';

        PromoHelper::setTermsConditions($promo, $terms);

        expect($promo->metadata[PromoHelper::META_TERMS_CONDITIONS])->toBe($terms);
    });
});

describe('PromoHelper - Auto Apply Operations', function () {
    test('isAutoApply returns false by default', function () {
        $promo = Promo::factory()->create(['metadata' => []]);

        $result = PromoHelper::isAutoApply($promo);

        expect($result)->toBeFalse();
    });

    test('isAutoApply returns true when set to true', function () {
        $promo = Promo::factory()->create([
            'metadata' => [PromoHelper::META_AUTO_APPLY => true],
        ]);

        $result = PromoHelper::isAutoApply($promo);

        expect($result)->toBeTrue();
    });

    test('isAutoApply returns false when explicitly set to false', function () {
        $promo = Promo::factory()->create([
            'metadata' => [PromoHelper::META_AUTO_APPLY => false],
        ]);

        $result = PromoHelper::isAutoApply($promo);

        expect($result)->toBeFalse();
    });

    test('setAutoApply sets auto apply to true', function () {
        $promo = Promo::factory()->create(['metadata' => []]);

        PromoHelper::setAutoApply($promo, true);

        expect($promo->metadata[PromoHelper::META_AUTO_APPLY])->toBeTrue();
    });

    test('setAutoApply sets auto apply to false', function () {
        $promo = Promo::factory()->create(['metadata' => []]);

        PromoHelper::setAutoApply($promo, false);

        expect($promo->metadata[PromoHelper::META_AUTO_APPLY])->toBeFalse();
    });
});

describe('PromoHelper - Validation', function () {
    test('isValid returns true for active promo within period with available quota', function () {
        $promo = Promo::factory()->create([
            'status' => 'Aktif',
            'tanggal_mulai' => now()->subDay(),
            'tanggal_berakhir' => now()->addDay(),
            'kuota_total' => 100,
            'kuota_terpakai' => 50,
        ]);

        $result = PromoHelper::isValid($promo);

        expect($result)->toBeTrue();
    });

    test('isValid returns false for inactive promo', function () {
        $promo = Promo::factory()->create([
            'status' => 'Tidak Aktif',
            'tanggal_mulai' => now()->subDay(),
            'tanggal_berakhir' => now()->addDay(),
            'kuota_total' => 100,
            'kuota_terpakai' => 50,
        ]);

        $result = PromoHelper::isValid($promo);

        expect($result)->toBeFalse();
    });

    test('isValid returns false when promo not started yet', function () {
        $promo = Promo::factory()->create([
            'status' => 'Aktif',
            'tanggal_mulai' => now()->addDay(),
            'tanggal_berakhir' => now()->addWeek(),
            'kuota_total' => 100,
            'kuota_terpakai' => 50,
        ]);

        $result = PromoHelper::isValid($promo);

        expect($result)->toBeFalse();
    });

    test('isValid returns false when promo expired', function () {
        $promo = Promo::factory()->create([
            'status' => 'Aktif',
            'tanggal_mulai' => now()->subWeek(),
            'tanggal_berakhir' => now()->subDay(),
            'kuota_total' => 100,
            'kuota_terpakai' => 50,
        ]);

        $result = PromoHelper::isValid($promo);

        expect($result)->toBeFalse();
    });

    test('isValid returns false when quota is exhausted', function () {
        $promo = Promo::factory()->create([
            'status' => 'Aktif',
            'tanggal_mulai' => now()->subDay(),
            'tanggal_berakhir' => now()->addDay(),
            'kuota_total' => 100,
            'kuota_terpakai' => 100,
        ]);

        $result = PromoHelper::isValid($promo);

        expect($result)->toBeFalse();
    });

    test('isValid returns true when kuota_total is null (unlimited)', function () {
        $promo = Promo::factory()->create([
            'status' => 'Aktif',
            'tanggal_mulai' => now()->subDay(),
            'tanggal_berakhir' => now()->addDay(),
            'kuota_total' => null,
            'kuota_terpakai' => 1000,
        ]);

        $result = PromoHelper::isValid($promo);

        expect($result)->toBeTrue();
    });
});

describe('PromoHelper - Quota Operations', function () {
    test('hasQuota returns true when quota available', function () {
        $promo = Promo::factory()->create([
            'kuota_total' => 100,
            'kuota_terpakai' => 50,
        ]);

        $result = PromoHelper::hasQuota($promo);

        expect($result)->toBeTrue();
    });

    test('hasQuota returns false when quota exhausted', function () {
        $promo = Promo::factory()->create([
            'kuota_total' => 100,
            'kuota_terpakai' => 100,
        ]);

        $result = PromoHelper::hasQuota($promo);

        expect($result)->toBeFalse();
    });

    test('hasQuota returns false when quota exceeded', function () {
        $promo = Promo::factory()->create([
            'kuota_total' => 100,
            'kuota_terpakai' => 150,
        ]);

        $result = PromoHelper::hasQuota($promo);

        expect($result)->toBeFalse();
    });

    test('hasQuota returns true when kuota_total is null (unlimited)', function () {
        $promo = Promo::factory()->create([
            'kuota_total' => null,
            'kuota_terpakai' => 1000,
        ]);

        $result = PromoHelper::hasQuota($promo);

        expect($result)->toBeTrue();
    });

    test('incrementUsage increments kuota_terpakai', function () {
        $promo = Promo::factory()->create([
            'kuota_total' => 100,
            'kuota_terpakai' => 50,
            'status' => 'Aktif',
        ]);

        PromoHelper::incrementUsage($promo);
        $promo->refresh();

        expect($promo->kuota_terpakai)->toBe(51);
    });

    test('incrementUsage updates status to Habis when quota reached', function () {
        $promo = Promo::factory()->create([
            'kuota_total' => 100,
            'kuota_terpakai' => 99,
            'status' => 'Aktif',
        ]);

        PromoHelper::incrementUsage($promo);
        $promo->refresh();

        expect($promo->kuota_terpakai)->toBe(100);
        expect($promo->status)->toBe('Habis');
    });

    test('incrementUsage does not change status when quota is null', function () {
        $promo = Promo::factory()->create([
            'kuota_total' => null,
            'kuota_terpakai' => 99,
            'status' => 'Aktif',
        ]);

        PromoHelper::incrementUsage($promo);
        $promo->refresh();

        expect($promo->kuota_terpakai)->toBe(100);
        expect($promo->status)->toBe('Aktif');
    });

    test('incrementUsage does not change status when quota not yet reached', function () {
        $promo = Promo::factory()->create([
            'kuota_total' => 100,
            'kuota_terpakai' => 50,
            'status' => 'Aktif',
        ]);

        PromoHelper::incrementUsage($promo);
        $promo->refresh();

        expect($promo->status)->toBe('Aktif');
    });
});

describe('PromoHelper - Validation Rules', function () {
    test('metadataRules returns correct validation rules', function () {
        $rules = PromoHelper::metadataRules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey(PromoHelper::META_LAYANAN_ID);
        expect($rules)->toHaveKey(PromoHelper::META_EXCLUDE_PELANGGAN_ID);
        expect($rules)->toHaveKey(PromoHelper::META_BANNER_IMAGE);
        expect($rules)->toHaveKey(PromoHelper::META_TERMS_CONDITIONS);
        expect($rules)->toHaveKey(PromoHelper::META_AUTO_APPLY);
    });
});
