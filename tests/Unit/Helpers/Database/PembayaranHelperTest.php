<?php

declare(strict_types=1);

use App\Helper\Database\PembayaranHelper;
use App\Models\Pembayaran;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

describe('PembayaranHelper - Metadata Operations', function () {
    describe('getMetadata', function () {
        test('retrieves metadata value correctly', function () {
            $pembayaran = Pembayaran::factory()->create([
                'metadata' => ['test_key' => 'test_value'],
            ]);

            $result = PembayaranHelper::getMetadata($pembayaran, 'test_key');

            expect($result)->toBe('test_value');
        });

        test('returns default value when key not found', function () {
            $pembayaran = Pembayaran::factory()->create(['metadata' => []]);

            $result = PembayaranHelper::getMetadata($pembayaran, 'nonexistent', 'default');

            expect($result)->toBe('default');
        });

        test('returns null when key not found and no default', function () {
            $pembayaran = Pembayaran::factory()->create(['metadata' => []]);

            $result = PembayaranHelper::getMetadata($pembayaran, 'nonexistent');

            expect($result)->toBeNull();
        });
    });

    describe('setMetadata', function () {
        test('sets metadata value correctly', function () {
            $pembayaran = Pembayaran::factory()->create(['metadata' => []]);

            PembayaranHelper::setMetadata($pembayaran, 'test_key', 'test_value');

            expect($pembayaran->metadata)->toHaveKey('test_key');
            expect($pembayaran->metadata['test_key'])->toBe('test_value');
        });

        test('preserves existing metadata when setting new key', function () {
            $pembayaran = Pembayaran::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            PembayaranHelper::setMetadata($pembayaran, 'new_key', 'new_value');

            expect($pembayaran->metadata)->toHaveKey('existing');
            expect($pembayaran->metadata)->toHaveKey('new_key');
        });

        test('handles null metadata gracefully', function () {
            $pembayaran = Pembayaran::factory()->create(['metadata' => null]);

            PembayaranHelper::setMetadata($pembayaran, 'key', 'value');

            expect($pembayaran->metadata)->toBeArray();
            expect($pembayaran->metadata['key'])->toBe('value');
        });
    });

    describe('mergeMetadata', function () {
        test('merges new data with existing metadata', function () {
            $pembayaran = Pembayaran::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            PembayaranHelper::mergeMetadata($pembayaran, ['new' => 'data']);

            expect($pembayaran->metadata)->toHaveKey('existing');
            expect($pembayaran->metadata)->toHaveKey('new');
        });

        test('overwrites existing keys when merging', function () {
            $pembayaran = Pembayaran::factory()->create([
                'metadata' => ['key' => 'old'],
            ]);

            PembayaranHelper::mergeMetadata($pembayaran, ['key' => 'new']);

            expect($pembayaran->metadata['key'])->toBe('new');
        });
    });
});

describe('PembayaranHelper - Bank Pengirim Operations', function () {
    test('getBankPengirim returns null when not set', function () {
        $pembayaran = Pembayaran::factory()->create(['metadata' => []]);

        $result = PembayaranHelper::getBankPengirim($pembayaran);

        expect($result)->toBeNull();
    });

    test('getBankPengirim retrieves bank name', function () {
        $pembayaran = Pembayaran::factory()->create([
            'metadata' => [PembayaranHelper::META_BANK_PENGIRIM => 'BCA'],
        ]);

        $result = PembayaranHelper::getBankPengirim($pembayaran);

        expect($result)->toBe('BCA');
    });

    test('setBankPengirim sets bank name correctly', function () {
        $pembayaran = Pembayaran::factory()->create(['metadata' => []]);

        PembayaranHelper::setBankPengirim($pembayaran, 'Mandiri');

        expect($pembayaran->metadata[PembayaranHelper::META_BANK_PENGIRIM])->toBe('Mandiri');
    });
});

describe('PembayaranHelper - Rekening Pengirim Operations', function () {
    test('getRekeningPengirim returns null when not set', function () {
        $pembayaran = Pembayaran::factory()->create(['metadata' => []]);

        $result = PembayaranHelper::getRekeningPengirim($pembayaran);

        expect($result)->toBeNull();
    });

    test('getRekeningPengirim retrieves rekening number', function () {
        $pembayaran = Pembayaran::factory()->create([
            'metadata' => [PembayaranHelper::META_REKENING_PENGIRIM => '1234567890'],
        ]);

        $result = PembayaranHelper::getRekeningPengirim($pembayaran);

        expect($result)->toBe('1234567890');
    });

    test('setRekeningPengirim sets rekening number correctly', function () {
        $pembayaran = Pembayaran::factory()->create(['metadata' => []]);

        PembayaranHelper::setRekeningPengirim($pembayaran, '9876543210');

        expect($pembayaran->metadata[PembayaranHelper::META_REKENING_PENGIRIM])->toBe('9876543210');
    });
});

describe('PembayaranHelper - Nama Pengirim Operations', function () {
    test('getNamaPengirim returns null when not set', function () {
        $pembayaran = Pembayaran::factory()->create(['metadata' => []]);

        $result = PembayaranHelper::getNamaPengirim($pembayaran);

        expect($result)->toBeNull();
    });

    test('getNamaPengirim retrieves sender name', function () {
        $pembayaran = Pembayaran::factory()->create([
            'metadata' => [PembayaranHelper::META_NAMA_PENGIRIM => 'John Doe'],
        ]);

        $result = PembayaranHelper::getNamaPengirim($pembayaran);

        expect($result)->toBe('John Doe');
    });

    test('setNamaPengirim sets sender name correctly', function () {
        $pembayaran = Pembayaran::factory()->create(['metadata' => []]);

        PembayaranHelper::setNamaPengirim($pembayaran, 'Jane Smith');

        expect($pembayaran->metadata[PembayaranHelper::META_NAMA_PENGIRIM])->toBe('Jane Smith');
    });
});

describe('PembayaranHelper - Validation Rules', function () {
    test('metadataRules returns correct validation rules', function () {
        $rules = PembayaranHelper::metadataRules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey(PembayaranHelper::META_BANK_PENGIRIM);
        expect($rules)->toHaveKey(PembayaranHelper::META_REKENING_PENGIRIM);
        expect($rules)->toHaveKey(PembayaranHelper::META_NAMA_PENGIRIM);
        expect($rules)->toHaveKey(PembayaranHelper::META_WAKTU_TRANSFER);
    });
});
