<?php

declare(strict_types=1);

use App\Helper\Database\JenisPakaianHelper;
use App\Models\JenisPakaian;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

describe('JenisPakaianHelper - Metadata Operations', function () {
    describe('getMetadata', function () {
        test('retrieves metadata value correctly', function () {
            $jenisPakaian = JenisPakaian::factory()->create([
                'metadata' => ['test_key' => 'test_value'],
            ]);

            $result = JenisPakaianHelper::getMetadata($jenisPakaian, 'test_key');

            expect($result)->toBe('test_value');
        });

        test('returns default value when key not found', function () {
            $jenisPakaian = JenisPakaian::factory()->create(['metadata' => []]);

            $result = JenisPakaianHelper::getMetadata($jenisPakaian, 'nonexistent', 'default');

            expect($result)->toBe('default');
        });

        test('returns null when key not found and no default', function () {
            $jenisPakaian = JenisPakaian::factory()->create(['metadata' => []]);

            $result = JenisPakaianHelper::getMetadata($jenisPakaian, 'nonexistent');

            expect($result)->toBeNull();
        });

        test('handles nested metadata keys', function () {
            $jenisPakaian = JenisPakaian::factory()->create([
                'metadata' => ['nested' => ['key' => 'value']],
            ]);

            $result = JenisPakaianHelper::getMetadata($jenisPakaian, 'nested.key');

            expect($result)->toBe('value');
        });
    });

    describe('setMetadata', function () {
        test('sets metadata value correctly', function () {
            $jenisPakaian = JenisPakaian::factory()->create(['metadata' => []]);

            JenisPakaianHelper::setMetadata($jenisPakaian, 'test_key', 'test_value');

            expect($jenisPakaian->metadata)->toHaveKey('test_key');
            expect($jenisPakaian->metadata['test_key'])->toBe('test_value');
        });

        test('preserves existing metadata when setting new key', function () {
            $jenisPakaian = JenisPakaian::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            JenisPakaianHelper::setMetadata($jenisPakaian, 'new_key', 'new_value');

            expect($jenisPakaian->metadata)->toHaveKey('existing');
            expect($jenisPakaian->metadata)->toHaveKey('new_key');
        });

        test('overwrites existing metadata value', function () {
            $jenisPakaian = JenisPakaian::factory()->create([
                'metadata' => ['key' => 'old_value'],
            ]);

            JenisPakaianHelper::setMetadata($jenisPakaian, 'key', 'new_value');

            expect($jenisPakaian->metadata['key'])->toBe('new_value');
        });

        test('handles null metadata gracefully', function () {
            $jenisPakaian = JenisPakaian::factory()->create(['metadata' => null]);

            JenisPakaianHelper::setMetadata($jenisPakaian, 'key', 'value');

            expect($jenisPakaian->metadata)->toBeArray();
            expect($jenisPakaian->metadata['key'])->toBe('value');
        });
    });

    describe('mergeMetadata', function () {
        test('merges new data with existing metadata', function () {
            $jenisPakaian = JenisPakaian::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            JenisPakaianHelper::mergeMetadata($jenisPakaian, ['new' => 'data']);

            expect($jenisPakaian->metadata)->toHaveKey('existing');
            expect($jenisPakaian->metadata)->toHaveKey('new');
        });

        test('overwrites existing keys when merging', function () {
            $jenisPakaian = JenisPakaian::factory()->create([
                'metadata' => ['key' => 'old'],
            ]);

            JenisPakaianHelper::mergeMetadata($jenisPakaian, ['key' => 'new']);

            expect($jenisPakaian->metadata['key'])->toBe('new');
        });

        test('handles null metadata when merging', function () {
            $jenisPakaian = JenisPakaian::factory()->create(['metadata' => null]);

            JenisPakaianHelper::mergeMetadata($jenisPakaian, ['key' => 'value']);

            expect($jenisPakaian->metadata)->toBeArray();
            expect($jenisPakaian->metadata['key'])->toBe('value');
        });
    });
});

describe('JenisPakaianHelper - Penanganan Khusus Operations', function () {
    test('getPenangananKhusus returns null when not set', function () {
        $jenisPakaian = JenisPakaian::factory()->create(['metadata' => []]);

        $result = JenisPakaianHelper::getPenangananKhusus($jenisPakaian);

        expect($result)->toBeNull();
    });

    test('getPenangananKhusus retrieves penanganan khusus correctly', function () {
        $penanganan = 'Cuci terpisah dari pakaian berwarna';
        $jenisPakaian = JenisPakaian::factory()->create([
            'metadata' => [JenisPakaianHelper::META_PENANGANAN_KHUSUS => $penanganan],
        ]);

        $result = JenisPakaianHelper::getPenangananKhusus($jenisPakaian);

        expect($result)->toBe($penanganan);
    });

    test('setPenangananKhusus sets penanganan khusus correctly', function () {
        $jenisPakaian = JenisPakaian::factory()->create(['metadata' => []]);
        $penanganan = 'Gunakan deterjen khusus untuk pakaian putih';

        JenisPakaianHelper::setPenangananKhusus($jenisPakaian, $penanganan);

        expect($jenisPakaian->metadata[JenisPakaianHelper::META_PENANGANAN_KHUSUS])->toBe($penanganan);
    });

    test('setPenangananKhusus preserves other metadata', function () {
        $jenisPakaian = JenisPakaian::factory()->create([
            'metadata' => ['other_key' => 'other_value'],
        ]);
        $penanganan = 'Jangan gunakan pemutih';

        JenisPakaianHelper::setPenangananKhusus($jenisPakaian, $penanganan);

        expect($jenisPakaian->metadata)->toHaveKey('other_key');
        expect($jenisPakaian->metadata[JenisPakaianHelper::META_PENANGANAN_KHUSUS])->toBe($penanganan);
    });
});

describe('JenisPakaianHelper - Validation Rules', function () {
    test('metadataRules returns correct validation rules', function () {
        $rules = JenisPakaianHelper::metadataRules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey(JenisPakaianHelper::META_PENANGANAN_KHUSUS);
        expect($rules[JenisPakaianHelper::META_PENANGANAN_KHUSUS])->toBe('nullable|string');
    });
});
