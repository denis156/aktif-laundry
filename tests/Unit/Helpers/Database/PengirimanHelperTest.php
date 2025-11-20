<?php

declare(strict_types=1);

use App\Helper\Database\PengirimanHelper;
use App\Models\Pengiriman;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

describe('PengirimanHelper - Metadata Operations', function () {
    describe('getMetadata', function () {
        test('retrieves metadata value correctly', function () {
            $pengiriman = Pengiriman::factory()->create([
                'metadata' => ['test_key' => 'test_value'],
            ]);

            $result = PengirimanHelper::getMetadata($pengiriman, 'test_key');

            expect($result)->toBe('test_value');
        });

        test('returns default value when key not found', function () {
            $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

            $result = PengirimanHelper::getMetadata($pengiriman, 'nonexistent', 'default');

            expect($result)->toBe('default');
        });

        test('returns null when key not found and no default', function () {
            $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

            $result = PengirimanHelper::getMetadata($pengiriman, 'nonexistent');

            expect($result)->toBeNull();
        });
    });

    describe('setMetadata', function () {
        test('sets metadata value correctly', function () {
            $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

            PengirimanHelper::setMetadata($pengiriman, 'test_key', 'test_value');

            expect($pengiriman->metadata)->toHaveKey('test_key');
            expect($pengiriman->metadata['test_key'])->toBe('test_value');
        });

        test('preserves existing metadata when setting new key', function () {
            $pengiriman = Pengiriman::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            PengirimanHelper::setMetadata($pengiriman, 'new_key', 'new_value');

            expect($pengiriman->metadata)->toHaveKey('existing');
            expect($pengiriman->metadata)->toHaveKey('new_key');
        });

        test('handles null metadata gracefully', function () {
            $pengiriman = Pengiriman::factory()->create(['metadata' => null]);

            PengirimanHelper::setMetadata($pengiriman, 'key', 'value');

            expect($pengiriman->metadata)->toBeArray();
            expect($pengiriman->metadata['key'])->toBe('value');
        });
    });

    describe('mergeMetadata', function () {
        test('merges new data with existing metadata', function () {
            $pengiriman = Pengiriman::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            PengirimanHelper::mergeMetadata($pengiriman, ['new' => 'data']);

            expect($pengiriman->metadata)->toHaveKey('existing');
            expect($pengiriman->metadata)->toHaveKey('new');
        });

        test('overwrites existing keys when merging', function () {
            $pengiriman = Pengiriman::factory()->create([
                'metadata' => ['key' => 'old'],
            ]);

            PengirimanHelper::mergeMetadata($pengiriman, ['key' => 'new']);

            expect($pengiriman->metadata['key'])->toBe('new');
        });
    });
});

describe('PengirimanHelper - Lokasi Pengambilan Operations', function () {
    test('getLokasiPengambilan returns null when not set', function () {
        $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

        $result = PengirimanHelper::getLokasiPengambilan($pengiriman);

        expect($result)->toBeNull();
    });

    test('getLokasiPengambilan retrieves location data', function () {
        $locationData = ['lat' => -3.9778, 'lng' => 122.5152, 'address' => 'Jl. Test'];
        $pengiriman = Pengiriman::factory()->create([
            'metadata' => [PengirimanHelper::META_LOKASI_PENGAMBILAN => $locationData],
        ]);

        $result = PengirimanHelper::getLokasiPengambilan($pengiriman);

        expect($result)->toBe($locationData);
    });

    test('setLokasiPengambilan sets location with GPS', function () {
        $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

        PengirimanHelper::setLokasiPengambilan($pengiriman, -3.9778, 122.5152, 'Jl. Test');

        $location = $pengiriman->metadata[PengirimanHelper::META_LOKASI_PENGAMBILAN];
        expect($location)->toBeArray();
        expect($location['lat'])->toBe(-3.9778);
        expect($location['lng'])->toBe(122.5152);
        expect($location['address'])->toBe('Jl. Test');
    });

    test('setLokasiPengambilan sets location without address', function () {
        $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

        PengirimanHelper::setLokasiPengambilan($pengiriman, -3.9778, 122.5152);

        $location = $pengiriman->metadata[PengirimanHelper::META_LOKASI_PENGAMBILAN];
        expect($location)->toBeArray();
        expect($location['lat'])->toBe(-3.9778);
        expect($location['lng'])->toBe(122.5152);
        expect($location['address'])->toBeNull();
    });
});

describe('PengirimanHelper - Tracking Operations', function () {
    test('getTracking returns empty array when not set', function () {
        $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

        $result = PengirimanHelper::getTracking($pengiriman);

        expect($result)->toBeArray();
        expect($result)->toBeEmpty();
    });

    test('getTracking retrieves tracking array', function () {
        $trackingData = [
            ['status' => 'Dijemput', 'waktu' => '2024-01-01T10:00:00Z'],
            ['status' => 'Dikirim', 'waktu' => '2024-01-01T15:00:00Z'],
        ];
        $pengiriman = Pengiriman::factory()->create([
            'metadata' => [PengirimanHelper::META_TRACKING => $trackingData],
        ]);

        $result = PengirimanHelper::getTracking($pengiriman);

        expect($result)->toBe($trackingData);
    });

    test('addTracking adds checkpoint to tracking array', function () {
        $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

        PengirimanHelper::addTracking($pengiriman, 'Dijemput', -3.9778, 122.5152, 'Sudah diambil');

        $tracking = PengirimanHelper::getTracking($pengiriman);
        expect($tracking)->toHaveCount(1);
        expect($tracking[0]['status'])->toBe('Dijemput');
        expect($tracking[0]['lat'])->toBe(-3.9778);
        expect($tracking[0]['lng'])->toBe(122.5152);
        expect($tracking[0]['keterangan'])->toBe('Sudah diambil');
        expect($tracking[0])->toHaveKey('waktu');
    });

    test('addTracking appends to existing tracking array', function () {
        $pengiriman = Pengiriman::factory()->create([
            'metadata' => [
                PengirimanHelper::META_TRACKING => [
                    ['status' => 'Dijemput', 'waktu' => '2024-01-01T10:00:00Z'],
                ],
            ],
        ]);

        PengirimanHelper::addTracking($pengiriman, 'Dikirim', -3.9900, 122.5200);

        $tracking = PengirimanHelper::getTracking($pengiriman);
        expect($tracking)->toHaveCount(2);
        expect($tracking[1]['status'])->toBe('Dikirim');
    });

    test('addTracking works without GPS coordinates', function () {
        $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

        PengirimanHelper::addTracking($pengiriman, 'Proses');

        $tracking = PengirimanHelper::getTracking($pengiriman);
        expect($tracking)->toHaveCount(1);
        expect($tracking[0]['status'])->toBe('Proses');
        expect($tracking[0]['lat'])->toBeNull();
        expect($tracking[0]['lng'])->toBeNull();
    });
});

describe('PengirimanHelper - Review Customer Operations', function () {
    test('getReviewCustomer returns null when not set', function () {
        $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

        $result = PengirimanHelper::getReviewCustomer($pengiriman);

        expect($result)->toBeNull();
    });

    test('getReviewCustomer retrieves review', function () {
        $pengiriman = Pengiriman::factory()->create([
            'metadata' => [PengirimanHelper::META_REVIEW_CUSTOMER => 'Pelayanan sangat baik'],
        ]);

        $result = PengirimanHelper::getReviewCustomer($pengiriman);

        expect($result)->toBe('Pelayanan sangat baik');
    });

    test('setReviewCustomer sets review correctly', function () {
        $pengiriman = Pengiriman::factory()->create(['metadata' => []]);

        PengirimanHelper::setReviewCustomer($pengiriman, 'Kurir ramah dan cepat');

        expect($pengiriman->metadata[PengirimanHelper::META_REVIEW_CUSTOMER])->toBe('Kurir ramah dan cepat');
    });
});

describe('PengirimanHelper - Validation Rules', function () {
    test('metadataRules returns correct validation rules', function () {
        $rules = PengirimanHelper::metadataRules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey(PengirimanHelper::META_LOKASI_PENGAMBILAN);
        expect($rules)->toHaveKey(PengirimanHelper::META_TRACKING);
        expect($rules)->toHaveKey(PengirimanHelper::META_REVIEW_CUSTOMER);
    });
});
