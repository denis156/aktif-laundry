<?php

declare(strict_types=1);

use App\Helper\Database\LayananHelper;
use App\Models\Layanan;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

describe('LayananHelper - Metadata Operations', function () {
    describe('getMetadata', function () {
        test('retrieves metadata value correctly', function () {
            $layanan = Layanan::factory()->create([
                'metadata' => ['test_key' => 'test_value'],
            ]);

            $result = LayananHelper::getMetadata($layanan, 'test_key');

            expect($result)->toBe('test_value');
        });

        test('returns default value when key not found', function () {
            $layanan = Layanan::factory()->create(['metadata' => []]);

            $result = LayananHelper::getMetadata($layanan, 'nonexistent', 'default');

            expect($result)->toBe('default');
        });

        test('returns null when key not found and no default', function () {
            $layanan = Layanan::factory()->create(['metadata' => []]);

            $result = LayananHelper::getMetadata($layanan, 'nonexistent');

            expect($result)->toBeNull();
        });

        test('handles nested metadata keys', function () {
            $layanan = Layanan::factory()->create([
                'metadata' => ['nested' => ['key' => 'value']],
            ]);

            $result = LayananHelper::getMetadata($layanan, 'nested.key');

            expect($result)->toBe('value');
        });
    });

    describe('setMetadata', function () {
        test('sets metadata value correctly', function () {
            $layanan = Layanan::factory()->create(['metadata' => []]);

            LayananHelper::setMetadata($layanan, 'test_key', 'test_value');

            expect($layanan->metadata)->toHaveKey('test_key');
            expect($layanan->metadata['test_key'])->toBe('test_value');
        });

        test('preserves existing metadata when setting new key', function () {
            $layanan = Layanan::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            LayananHelper::setMetadata($layanan, 'new_key', 'new_value');

            expect($layanan->metadata)->toHaveKey('existing');
            expect($layanan->metadata)->toHaveKey('new_key');
        });

        test('overwrites existing metadata value', function () {
            $layanan = Layanan::factory()->create([
                'metadata' => ['key' => 'old_value'],
            ]);

            LayananHelper::setMetadata($layanan, 'key', 'new_value');

            expect($layanan->metadata['key'])->toBe('new_value');
        });

        test('handles null metadata gracefully', function () {
            $layanan = Layanan::factory()->create(['metadata' => null]);

            LayananHelper::setMetadata($layanan, 'key', 'value');

            expect($layanan->metadata)->toBeArray();
            expect($layanan->metadata['key'])->toBe('value');
        });
    });

    describe('mergeMetadata', function () {
        test('merges new data with existing metadata', function () {
            $layanan = Layanan::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            LayananHelper::mergeMetadata($layanan, ['new' => 'data']);

            expect($layanan->metadata)->toHaveKey('existing');
            expect($layanan->metadata)->toHaveKey('new');
        });

        test('overwrites existing keys when merging', function () {
            $layanan = Layanan::factory()->create([
                'metadata' => ['key' => 'old'],
            ]);

            LayananHelper::mergeMetadata($layanan, ['key' => 'new']);

            expect($layanan->metadata['key'])->toBe('new');
        });

        test('handles null metadata when merging', function () {
            $layanan = Layanan::factory()->create(['metadata' => null]);

            LayananHelper::mergeMetadata($layanan, ['key' => 'value']);

            expect($layanan->metadata)->toBeArray();
            expect($layanan->metadata['key'])->toBe('value');
        });
    });
});

describe('LayananHelper - Include/Exclude Operations', function () {
    test('getInclude returns empty array when not set', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        $result = LayananHelper::getInclude($layanan);

        expect($result)->toBeArray();
        expect($result)->toBeEmpty();
    });

    test('getInclude retrieves include items', function () {
        $includeItems = ['Cuci', 'Setrika', 'Lipat'];
        $layanan = Layanan::factory()->create([
            'metadata' => [LayananHelper::META_INCLUDE => $includeItems],
        ]);

        $result = LayananHelper::getInclude($layanan);

        expect($result)->toBe($includeItems);
    });

    test('setInclude sets include items correctly', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);
        $includeItems = ['Cuci', 'Setrika', 'Lipat'];

        LayananHelper::setInclude($layanan, $includeItems);

        expect($layanan->metadata[LayananHelper::META_INCLUDE])->toBe($includeItems);
    });

    test('getExclude returns empty array when not set', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        $result = LayananHelper::getExclude($layanan);

        expect($result)->toBeArray();
        expect($result)->toBeEmpty();
    });

    test('getExclude retrieves exclude items', function () {
        $excludeItems = ['Pewangi premium', 'Antar jemput'];
        $layanan = Layanan::factory()->create([
            'metadata' => [LayananHelper::META_EXCLUDE => $excludeItems],
        ]);

        $result = LayananHelper::getExclude($layanan);

        expect($result)->toBe($excludeItems);
    });

    test('setExclude sets exclude items correctly', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);
        $excludeItems = ['Pewangi premium', 'Antar jemput'];

        LayananHelper::setExclude($layanan, $excludeItems);

        expect($layanan->metadata[LayananHelper::META_EXCLUDE])->toBe($excludeItems);
    });
});

describe('LayananHelper - Min/Max Order Operations', function () {
    test('getMinOrder returns null when not set', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        $result = LayananHelper::getMinOrder($layanan);

        expect($result)->toBeNull();
    });

    test('getMinOrder retrieves minimum order value', function () {
        $layanan = Layanan::factory()->create([
            'metadata' => [LayananHelper::META_MIN_ORDER => 3],
        ]);

        $result = LayananHelper::getMinOrder($layanan);

        expect($result)->toBe(3);
    });

    test('setMinOrder sets minimum order value', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        LayananHelper::setMinOrder($layanan, 5);

        expect($layanan->metadata[LayananHelper::META_MIN_ORDER])->toBe(5);
    });

    test('getMaxOrder returns null when not set', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        $result = LayananHelper::getMaxOrder($layanan);

        expect($result)->toBeNull();
    });

    test('getMaxOrder retrieves maximum order value', function () {
        $layanan = Layanan::factory()->create([
            'metadata' => [LayananHelper::META_MAX_ORDER => 50],
        ]);

        $result = LayananHelper::getMaxOrder($layanan);

        expect($result)->toBe(50);
    });

    test('setMaxOrder sets maximum order value', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        LayananHelper::setMaxOrder($layanan, 100);

        expect($layanan->metadata[LayananHelper::META_MAX_ORDER])->toBe(100);
    });
});

describe('LayananHelper - Popular Operations', function () {
    test('isPopular returns false by default', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        $result = LayananHelper::isPopular($layanan);

        expect($result)->toBeFalse();
    });

    test('isPopular returns true when set to true', function () {
        $layanan = Layanan::factory()->create([
            'metadata' => [LayananHelper::META_POPULAR => true],
        ]);

        $result = LayananHelper::isPopular($layanan);

        expect($result)->toBeTrue();
    });

    test('isPopular returns false when explicitly set to false', function () {
        $layanan = Layanan::factory()->create([
            'metadata' => [LayananHelper::META_POPULAR => false],
        ]);

        $result = LayananHelper::isPopular($layanan);

        expect($result)->toBeFalse();
    });

    test('setPopular sets popular to true', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        LayananHelper::setPopular($layanan, true);

        expect($layanan->metadata[LayananHelper::META_POPULAR])->toBeTrue();
    });

    test('setPopular sets popular to false', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        LayananHelper::setPopular($layanan, false);

        expect($layanan->metadata[LayananHelper::META_POPULAR])->toBeFalse();
    });
});

describe('LayananHelper - Icon Operations', function () {
    test('getIcon returns null when not set', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        $result = LayananHelper::getIcon($layanan);

        expect($result)->toBeNull();
    });

    test('getIcon retrieves icon value', function () {
        $layanan = Layanan::factory()->create([
            'metadata' => [LayananHelper::META_ICON => 'icon-laundry'],
        ]);

        $result = LayananHelper::getIcon($layanan);

        expect($result)->toBe('icon-laundry');
    });

    test('setIcon sets icon value', function () {
        $layanan = Layanan::factory()->create(['metadata' => []]);

        LayananHelper::setIcon($layanan, 'icon-wash');

        expect($layanan->metadata[LayananHelper::META_ICON])->toBe('icon-wash');
    });
});

describe('LayananHelper - Validation Rules', function () {
    test('metadataRules returns correct validation rules', function () {
        $rules = LayananHelper::metadataRules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey(LayananHelper::META_INCLUDE);
        expect($rules)->toHaveKey(LayananHelper::META_EXCLUDE);
        expect($rules)->toHaveKey(LayananHelper::META_MIN_ORDER);
        expect($rules)->toHaveKey(LayananHelper::META_MAX_ORDER);
        expect($rules)->toHaveKey(LayananHelper::META_POPULAR);
        expect($rules)->toHaveKey(LayananHelper::META_ICON);
        expect($rules)->toHaveKey(LayananHelper::META_DESKRIPSI_DETAIL);
    });
});
