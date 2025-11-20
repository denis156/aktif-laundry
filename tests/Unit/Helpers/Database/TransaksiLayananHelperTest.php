<?php

declare(strict_types=1);

use App\Helper\Database\TransaksiLayananHelper;
use App\Models\Layanan;
use App\Models\TransaksiLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

describe('TransaksiLayananHelper - Metadata Operations', function () {
    describe('getMetadata', function () {
        test('retrieves metadata value correctly', function () {
            $transaksiLayanan = TransaksiLayanan::factory()->create([
                'metadata' => ['test_key' => 'test_value'],
            ]);

            $result = TransaksiLayananHelper::getMetadata($transaksiLayanan, 'test_key');

            expect($result)->toBe('test_value');
        });

        test('returns default value when key not found', function () {
            $transaksiLayanan = TransaksiLayanan::factory()->create(['metadata' => []]);

            $result = TransaksiLayananHelper::getMetadata($transaksiLayanan, 'nonexistent', 'default');

            expect($result)->toBe('default');
        });

        test('returns null when key not found and no default', function () {
            $transaksiLayanan = TransaksiLayanan::factory()->create(['metadata' => []]);

            $result = TransaksiLayananHelper::getMetadata($transaksiLayanan, 'nonexistent');

            expect($result)->toBeNull();
        });
    });

    describe('setMetadata', function () {
        test('sets metadata value correctly', function () {
            $transaksiLayanan = TransaksiLayanan::factory()->create(['metadata' => []]);

            TransaksiLayananHelper::setMetadata($transaksiLayanan, 'test_key', 'test_value');

            expect($transaksiLayanan->metadata)->toHaveKey('test_key');
            expect($transaksiLayanan->metadata['test_key'])->toBe('test_value');
        });

        test('preserves existing metadata when setting new key', function () {
            $transaksiLayanan = TransaksiLayanan::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            TransaksiLayananHelper::setMetadata($transaksiLayanan, 'new_key', 'new_value');

            expect($transaksiLayanan->metadata)->toHaveKey('existing');
            expect($transaksiLayanan->metadata)->toHaveKey('new_key');
        });

        test('handles null metadata gracefully', function () {
            $transaksiLayanan = TransaksiLayanan::factory()->create(['metadata' => null]);

            TransaksiLayananHelper::setMetadata($transaksiLayanan, 'key', 'value');

            expect($transaksiLayanan->metadata)->toBeArray();
            expect($transaksiLayanan->metadata['key'])->toBe('value');
        });
    });

    describe('mergeMetadata', function () {
        test('merges new data with existing metadata', function () {
            $transaksiLayanan = TransaksiLayanan::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            TransaksiLayananHelper::mergeMetadata($transaksiLayanan, ['new' => 'data']);

            expect($transaksiLayanan->metadata)->toHaveKey('existing');
            expect($transaksiLayanan->metadata)->toHaveKey('new');
        });

        test('overwrites existing keys when merging', function () {
            $transaksiLayanan = TransaksiLayanan::factory()->create([
                'metadata' => ['key' => 'old'],
            ]);

            TransaksiLayananHelper::mergeMetadata($transaksiLayanan, ['key' => 'new']);

            expect($transaksiLayanan->metadata['key'])->toBe('new');
        });
    });
});

describe('TransaksiLayananHelper - Catatan Operations', function () {
    test('getCatatan returns null when not set', function () {
        $transaksiLayanan = TransaksiLayanan::factory()->create(['metadata' => []]);

        $result = TransaksiLayananHelper::getCatatan($transaksiLayanan);

        expect($result)->toBeNull();
    });

    test('getCatatan retrieves catatan value', function () {
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'metadata' => [TransaksiLayananHelper::META_CATATAN => 'Hati-hati dengan pakaian putih'],
        ]);

        $result = TransaksiLayananHelper::getCatatan($transaksiLayanan);

        expect($result)->toBe('Hati-hati dengan pakaian putih');
    });

    test('setCatatan sets catatan value correctly', function () {
        $transaksiLayanan = TransaksiLayanan::factory()->create(['metadata' => []]);

        TransaksiLayananHelper::setCatatan($transaksiLayanan, 'Jangan gunakan pemutih');

        expect($transaksiLayanan->metadata[TransaksiLayananHelper::META_CATATAN])->toBe('Jangan gunakan pemutih');
    });
});

describe('TransaksiLayananHelper - Petugas Operations', function () {
    test('getPetugas returns null when not set', function () {
        $transaksiLayanan = TransaksiLayanan::factory()->create(['metadata' => []]);

        $result = TransaksiLayananHelper::getPetugas($transaksiLayanan);

        expect($result)->toBeNull();
    });

    test('getPetugas retrieves petugas name', function () {
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'metadata' => [TransaksiLayananHelper::META_PETUGAS => 'Budi'],
        ]);

        $result = TransaksiLayananHelper::getPetugas($transaksiLayanan);

        expect($result)->toBe('Budi');
    });

    test('setPetugas sets petugas name correctly', function () {
        $transaksiLayanan = TransaksiLayanan::factory()->create(['metadata' => []]);

        TransaksiLayananHelper::setPetugas($transaksiLayanan, 'Andi');

        expect($transaksiLayanan->metadata[TransaksiLayananHelper::META_PETUGAS])->toBe('Andi');
    });
});

describe('TransaksiLayananHelper - Service Type Checks', function () {
    test('isPerKg returns true for per_kg service', function () {
        $layanan = Layanan::factory()->create(['tipe_layanan' => 'per_kg']);
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'layanan_id' => $layanan->id,
        ]);

        $result = TransaksiLayananHelper::isPerKg($transaksiLayanan);

        expect($result)->toBeTrue();
    });

    test('isPerKg returns false for per_satuan service', function () {
        $layanan = Layanan::factory()->create(['tipe_layanan' => 'per_satuan']);
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'layanan_id' => $layanan->id,
        ]);

        $result = TransaksiLayananHelper::isPerKg($transaksiLayanan);

        expect($result)->toBeFalse();
    });

    test('isPerSatuan returns true for per_satuan service', function () {
        $layanan = Layanan::factory()->create(['tipe_layanan' => 'per_satuan']);
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'layanan_id' => $layanan->id,
        ]);

        $result = TransaksiLayananHelper::isPerSatuan($transaksiLayanan);

        expect($result)->toBeTrue();
    });

    test('isPerSatuan returns false for per_kg service', function () {
        $layanan = Layanan::factory()->create(['tipe_layanan' => 'per_kg']);
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'layanan_id' => $layanan->id,
        ]);

        $result = TransaksiLayananHelper::isPerSatuan($transaksiLayanan);

        expect($result)->toBeFalse();
    });
});

describe('TransaksiLayananHelper - Display Label', function () {
    test('getDisplayLabel shows kg for per_kg service', function () {
        $layanan = Layanan::factory()->create(['tipe_layanan' => 'per_kg']);
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'layanan_id' => $layanan->id,
            'nama_layanan' => 'Cuci Kering',
            'berat_kg' => 3.5,
        ]);

        $result = TransaksiLayananHelper::getDisplayLabel($transaksiLayanan);

        expect($result)->toBe('Cuci Kering (3.50 kg)');
    });

    test('getDisplayLabel shows pcs for per_satuan service', function () {
        $layanan = Layanan::factory()->create(['tipe_layanan' => 'per_satuan']);
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'layanan_id' => $layanan->id,
            'nama_layanan' => 'Setrika',
            'jumlah_satuan' => 10,
        ]);

        $result = TransaksiLayananHelper::getDisplayLabel($transaksiLayanan);

        expect($result)->toBe('Setrika (10 pcs)');
    });
});

describe('TransaksiLayananHelper - Perhitungan Label', function () {
    test('getPerhitunganLabel formats per_kg service correctly', function () {
        $layanan = Layanan::factory()->create(['tipe_layanan' => 'per_kg']);
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'layanan_id' => $layanan->id,
            'berat_kg' => 3.5,
            'harga_per_kg' => 7000,
            'jenis_pakaian' => [],
        ]);

        $result = TransaksiLayananHelper::getPerhitunganLabel($transaksiLayanan);

        expect($result)->toBe('3.50 kg × Rp 7.000');
    });

    test('getPerhitunganLabel includes jenis pakaian for per_kg service', function () {
        $layanan = Layanan::factory()->create(['tipe_layanan' => 'per_kg']);
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'layanan_id' => $layanan->id,
            'berat_kg' => 3.5,
            'harga_per_kg' => 7000,
            'jenis_pakaian' => [
                ['nama' => 'Kemeja', 'jumlah' => 5],
                ['nama' => 'Celana', 'jumlah' => 3],
            ],
        ]);

        $result = TransaksiLayananHelper::getPerhitunganLabel($transaksiLayanan);

        expect($result)->toBe('3.50 kg × Rp 7.000 - Kemeja (5), Celana (3)');
    });

    test('getPerhitunganLabel formats per_satuan service correctly', function () {
        $layanan = Layanan::factory()->create(['tipe_layanan' => 'per_satuan']);
        $transaksiLayanan = TransaksiLayanan::factory()->create([
            'layanan_id' => $layanan->id,
            'jumlah_satuan' => 10,
            'harga_per_satuan' => 5000,
        ]);

        $result = TransaksiLayananHelper::getPerhitunganLabel($transaksiLayanan);

        expect($result)->toBe('10 pcs × Rp 5.000');
    });
});

describe('TransaksiLayananHelper - Validation Rules', function () {
    test('metadataRules returns correct validation rules', function () {
        $rules = TransaksiLayananHelper::metadataRules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey(TransaksiLayananHelper::META_CATATAN);
        expect($rules)->toHaveKey(TransaksiLayananHelper::META_PETUGAS);
    });
});
