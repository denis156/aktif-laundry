<?php

declare(strict_types=1);

use App\Helper\Database\TransaksiHelper;
use App\Helper\Database\TransaksiLayananHelper;
use App\Models\Layanan;
use App\Models\Transaksi;
use App\Models\TransaksiLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

pest()->use(RefreshDatabase::class);

describe('TransaksiHelper - Metadata Operations', function () {
    describe('getMetadata', function () {
        test('retrieves metadata value correctly', function () {
            $transaksi = Transaksi::factory()->create([
                'metadata' => ['test_key' => 'test_value'],
            ]);

            $result = TransaksiHelper::getMetadata($transaksi, 'test_key');

            expect($result)->toBe('test_value');
        });

        test('returns default value when key not found', function () {
            $transaksi = Transaksi::factory()->create(['metadata' => []]);

            $result = TransaksiHelper::getMetadata($transaksi, 'nonexistent', 'default');

            expect($result)->toBe('default');
        });

        test('returns null when key not found and no default', function () {
            $transaksi = Transaksi::factory()->create(['metadata' => []]);

            $result = TransaksiHelper::getMetadata($transaksi, 'nonexistent');

            expect($result)->toBeNull();
        });

        test('handles nested metadata keys', function () {
            $transaksi = Transaksi::factory()->create([
                'metadata' => ['nested' => ['key' => 'value']],
            ]);

            $result = TransaksiHelper::getMetadata($transaksi, 'nested.key');

            expect($result)->toBe('value');
        });
    });

    describe('setMetadata', function () {
        test('sets metadata value correctly', function () {
            $transaksi = Transaksi::factory()->create(['metadata' => []]);

            TransaksiHelper::setMetadata($transaksi, 'test_key', 'test_value');

            expect($transaksi->metadata)->toHaveKey('test_key');
            expect($transaksi->metadata['test_key'])->toBe('test_value');
        });

        test('preserves existing metadata when setting new key', function () {
            $transaksi = Transaksi::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            TransaksiHelper::setMetadata($transaksi, 'new_key', 'new_value');

            expect($transaksi->metadata)->toHaveKey('existing');
            expect($transaksi->metadata)->toHaveKey('new_key');
        });

        test('overwrites existing metadata value', function () {
            $transaksi = Transaksi::factory()->create([
                'metadata' => ['key' => 'old_value'],
            ]);

            TransaksiHelper::setMetadata($transaksi, 'key', 'new_value');

            expect($transaksi->metadata['key'])->toBe('new_value');
        });

        test('handles null metadata gracefully', function () {
            $transaksi = Transaksi::factory()->create(['metadata' => null]);

            TransaksiHelper::setMetadata($transaksi, 'key', 'value');

            expect($transaksi->metadata)->toBeArray();
            expect($transaksi->metadata['key'])->toBe('value');
        });
    });

    describe('mergeMetadata', function () {
        test('merges new data with existing metadata', function () {
            $transaksi = Transaksi::factory()->create([
                'metadata' => ['existing' => 'value'],
            ]);

            TransaksiHelper::mergeMetadata($transaksi, ['new' => 'data']);

            expect($transaksi->metadata)->toHaveKey('existing');
            expect($transaksi->metadata)->toHaveKey('new');
        });

        test('overwrites existing keys when merging', function () {
            $transaksi = Transaksi::factory()->create([
                'metadata' => ['key' => 'old'],
            ]);

            TransaksiHelper::mergeMetadata($transaksi, ['key' => 'new']);

            expect($transaksi->metadata['key'])->toBe('new');
        });

        test('handles null metadata when merging', function () {
            $transaksi = Transaksi::factory()->create(['metadata' => null]);

            TransaksiHelper::mergeMetadata($transaksi, ['key' => 'value']);

            expect($transaksi->metadata)->toBeArray();
            expect($transaksi->metadata['key'])->toBe('value');
        });
    });
});

describe('TransaksiHelper - Promo Operations', function () {
    test('getPromoInfo retrieves promo metadata', function () {
        $promoData = ['kode_promo' => 'DISKON50', 'nilai' => 50000];
        $transaksi = Transaksi::factory()->create([
            'metadata' => [TransaksiHelper::META_PROMO => $promoData],
        ]);

        $result = TransaksiHelper::getPromoInfo($transaksi);

        expect($result)->toBe($promoData);
    });

    test('setPromoInfo sets promo metadata', function () {
        $transaksi = Transaksi::factory()->create(['metadata' => []]);
        $promoData = ['kode_promo' => 'DISKON50', 'nilai' => 50000];

        TransaksiHelper::setPromoInfo($transaksi, $promoData);

        expect($transaksi->metadata[TransaksiHelper::META_PROMO])->toBe($promoData);
    });

    test('getPromoInfo returns null when no promo', function () {
        $transaksi = Transaksi::factory()->create(['metadata' => []]);

        $result = TransaksiHelper::getPromoInfo($transaksi);

        expect($result)->toBeNull();
    });
});

describe('TransaksiHelper - Referral Operations', function () {
    test('getReferralInfo retrieves referral metadata', function () {
        $referralData = ['kode_referral' => 'REF123', 'poin' => 1000];
        $transaksi = Transaksi::factory()->create([
            'metadata' => [TransaksiHelper::META_REFERRAL => $referralData],
        ]);

        $result = TransaksiHelper::getReferralInfo($transaksi);

        expect($result)->toBe($referralData);
    });

    test('setReferralInfo sets referral metadata', function () {
        $transaksi = Transaksi::factory()->create(['metadata' => []]);
        $referralData = ['kode_referral' => 'REF123', 'poin' => 1000];

        TransaksiHelper::setReferralInfo($transaksi, $referralData);

        expect($transaksi->metadata[TransaksiHelper::META_REFERRAL])->toBe($referralData);
    });
});

describe('TransaksiHelper - Kurir Operations', function () {
    test('getKurirJemput retrieves kurir jemput name', function () {
        $transaksi = Transaksi::factory()->create([
            'metadata' => [TransaksiHelper::META_KURIR_JEMPUT => 'Budi'],
        ]);

        $result = TransaksiHelper::getKurirJemput($transaksi);

        expect($result)->toBe('Budi');
    });

    test('setKurirJemput sets kurir jemput name', function () {
        $transaksi = Transaksi::factory()->create(['metadata' => []]);

        TransaksiHelper::setKurirJemput($transaksi, 'Budi');

        expect($transaksi->metadata[TransaksiHelper::META_KURIR_JEMPUT])->toBe('Budi');
    });

    test('getKurirAntar retrieves kurir antar name', function () {
        $transaksi = Transaksi::factory()->create([
            'metadata' => [TransaksiHelper::META_KURIR_ANTAR => 'Andi'],
        ]);

        $result = TransaksiHelper::getKurirAntar($transaksi);

        expect($result)->toBe('Andi');
    });

    test('setKurirAntar sets kurir antar name', function () {
        $transaksi = Transaksi::factory()->create(['metadata' => []]);

        TransaksiHelper::setKurirAntar($transaksi, 'Andi');

        expect($transaksi->metadata[TransaksiHelper::META_KURIR_ANTAR])->toBe('Andi');
    });
});

describe('TransaksiHelper - Pembayaran Operations', function () {
    test('getPembayaranInfo retrieves pembayaran metadata', function () {
        $pembayaranData = ['metode' => 'QRIS', 'status' => 'Lunas'];
        $transaksi = Transaksi::factory()->create([
            'metadata' => [TransaksiHelper::META_PEMBAYARAN => $pembayaranData],
        ]);

        $result = TransaksiHelper::getPembayaranInfo($transaksi);

        expect($result)->toBe($pembayaranData);
    });

    test('setPembayaranInfo sets pembayaran metadata', function () {
        $transaksi = Transaksi::factory()->create(['metadata' => []]);
        $pembayaranData = ['metode' => 'QRIS', 'status' => 'Lunas'];

        TransaksiHelper::setPembayaranInfo($transaksi, $pembayaranData);

        expect($transaksi->metadata[TransaksiHelper::META_PEMBAYARAN])->toBe($pembayaranData);
    });
});

describe('TransaksiHelper - Foto Bukti Operations', function () {
    describe('Foto Bukti Timbangan', function () {
        test('getFotoBuktiTimbangan returns empty array when no photos', function () {
            $transaksi = Transaksi::factory()->create(['metadata' => []]);

            $result = TransaksiHelper::getFotoBuktiTimbangan($transaksi);

            expect($result)->toBeArray();
            expect($result)->toBeEmpty();
        });

        test('getFotoBuktiTimbangan retrieves photo array', function () {
            $photos = ['path/to/photo1.jpg', 'path/to/photo2.jpg'];
            $transaksi = Transaksi::factory()->create([
                'metadata' => [TransaksiHelper::META_FOTO_BUKTI_TIMBANGAN => $photos],
            ]);

            $result = TransaksiHelper::getFotoBuktiTimbangan($transaksi);

            expect($result)->toBe($photos);
        });

        test('addFotoBuktiTimbangan adds photo to array', function () {
            $transaksi = Transaksi::factory()->create(['metadata' => []]);

            TransaksiHelper::addFotoBuktiTimbangan($transaksi, 'path/to/photo.jpg');

            $photos = TransaksiHelper::getFotoBuktiTimbangan($transaksi);
            expect($photos)->toHaveCount(1);
            expect($photos[0])->toBe('path/to/photo.jpg');
        });

        test('addFotoBuktiTimbangan appends to existing array', function () {
            $transaksi = Transaksi::factory()->create([
                'metadata' => [TransaksiHelper::META_FOTO_BUKTI_TIMBANGAN => ['existing.jpg']],
            ]);

            TransaksiHelper::addFotoBuktiTimbangan($transaksi, 'new.jpg');

            $photos = TransaksiHelper::getFotoBuktiTimbangan($transaksi);
            expect($photos)->toHaveCount(2);
            expect($photos[1])->toBe('new.jpg');
        });
    });

    describe('Foto Bukti Pembayaran', function () {
        test('getFotoBuktiPembayaran returns empty array when no photos', function () {
            $transaksi = Transaksi::factory()->create(['metadata' => []]);

            $result = TransaksiHelper::getFotoBuktiPembayaran($transaksi);

            expect($result)->toBeArray();
            expect($result)->toBeEmpty();
        });

        test('getFotoBuktiPembayaran retrieves photo array', function () {
            $photos = ['path/to/payment1.jpg', 'path/to/payment2.jpg'];
            $transaksi = Transaksi::factory()->create([
                'metadata' => [TransaksiHelper::META_FOTO_BUKTI_PEMBAYARAN => $photos],
            ]);

            $result = TransaksiHelper::getFotoBuktiPembayaran($transaksi);

            expect($result)->toBe($photos);
        });

        test('addFotoBuktiPembayaran adds photo to array', function () {
            $transaksi = Transaksi::factory()->create(['metadata' => []]);

            TransaksiHelper::addFotoBuktiPembayaran($transaksi, 'path/to/payment.jpg');

            $photos = TransaksiHelper::getFotoBuktiPembayaran($transaksi);
            expect($photos)->toHaveCount(1);
            expect($photos[0])->toBe('path/to/payment.jpg');
        });

        test('addFotoBuktiPembayaran appends to existing array', function () {
            $transaksi = Transaksi::factory()->create([
                'metadata' => [TransaksiHelper::META_FOTO_BUKTI_PEMBAYARAN => ['existing.jpg']],
            ]);

            TransaksiHelper::addFotoBuktiPembayaran($transaksi, 'new.jpg');

            $photos = TransaksiHelper::getFotoBuktiPembayaran($transaksi);
            expect($photos)->toHaveCount(2);
            expect($photos[1])->toBe('new.jpg');
        });
    });
});

describe('TransaksiHelper - Status Operations', function () {
    test('getAllStatus returns all status constants', function () {
        $statuses = TransaksiHelper::getAllStatus();

        expect($statuses)->toBeArray();
        expect($statuses)->toHaveCount(5);
        expect($statuses)->toContain('Menunggu');
        expect($statuses)->toContain('Proses');
        expect($statuses)->toContain('Selesai');
        expect($statuses)->toContain('Diambil');
        expect($statuses)->toContain('Batal');
    });

    test('isValidStatus returns true for valid status', function () {
        expect(TransaksiHelper::isValidStatus('Menunggu'))->toBeTrue();
        expect(TransaksiHelper::isValidStatus('Proses'))->toBeTrue();
        expect(TransaksiHelper::isValidStatus('Selesai'))->toBeTrue();
        expect(TransaksiHelper::isValidStatus('Diambil'))->toBeTrue();
        expect(TransaksiHelper::isValidStatus('Batal'))->toBeTrue();
    });

    test('isValidStatus returns false for invalid status', function () {
        expect(TransaksiHelper::isValidStatus('Invalid'))->toBeFalse();
        expect(TransaksiHelper::isValidStatus(''))->toBeFalse();
        expect(TransaksiHelper::isValidStatus('menunggu'))->toBeFalse(); // case-sensitive
    });
});

describe('TransaksiHelper - Metode Pembayaran Operations', function () {
    test('getAllMetodePembayaran returns all payment methods', function () {
        $methods = TransaksiHelper::getAllMetodePembayaran();

        expect($methods)->toBeArray();
        expect($methods)->toHaveCount(7);
        expect($methods)->toContain('Tunai');
        expect($methods)->toContain('Non-Tunai');
        expect($methods)->toContain('Transfer');
        expect($methods)->toContain('QRIS');
        expect($methods)->toContain('E-Wallet');
        expect($methods)->toContain('Debit');
        expect($methods)->toContain('Kredit');
    });

    test('isValidMetodePembayaran returns true for valid method', function () {
        expect(TransaksiHelper::isValidMetodePembayaran('Tunai'))->toBeTrue();
        expect(TransaksiHelper::isValidMetodePembayaran('QRIS'))->toBeTrue();
        expect(TransaksiHelper::isValidMetodePembayaran('Transfer'))->toBeTrue();
    });

    test('isValidMetodePembayaran returns false for invalid method', function () {
        expect(TransaksiHelper::isValidMetodePembayaran('Invalid'))->toBeFalse();
        expect(TransaksiHelper::isValidMetodePembayaran(''))->toBeFalse();
        expect(TransaksiHelper::isValidMetodePembayaran('tunai'))->toBeFalse(); // case-sensitive
    });
});

describe('TransaksiHelper - hitungUlangTotal', function () {
    test('calculates total correctly with single service', function () {
        $transaksi = Transaksi::factory()->create([
            'subtotal' => 0,
            'total' => 0,
            'total_berat' => 0,
            'total_item' => 0,
            'jumlah_layanan' => 0,
            'diskon' => 0,
        ]);

        $layanan = Layanan::factory()->create([
            'harga_per_kg' => 5000,
            'harga_per_satuan' => null,
        ]);

        TransaksiLayanan::factory()->create([
            'transaksi_id' => $transaksi->id,
            'layanan_id' => $layanan->id,
            'berat_kg' => 3.5,
            'jumlah_satuan' => 0,
            'subtotal' => 17500,
        ]);

        TransaksiHelper::hitungUlangTotal($transaksi);
        $transaksi->refresh();

        expect($transaksi->total_berat)->toBe('0.00');
        expect($transaksi->total_item)->toBe(0);
        expect($transaksi->jumlah_layanan)->toBe(1);
        expect($transaksi->subtotal)->toBe(17500);
        expect($transaksi->total)->toBe(17500);
    });

    test('calculates total correctly with multiple services', function () {
        $transaksi = Transaksi::factory()->create([
            'subtotal' => 0,
            'total' => 0,
            'total_berat' => 0,
            'total_item' => 0,
            'jumlah_layanan' => 0,
            'diskon' => 0,
        ]);

        $layananKg = Layanan::factory()->create([
            'tipe_layanan' => 'per_kg',
            'harga_per_kg' => 5000,
            'harga_per_satuan' => null,
        ]);

        $layananSatuan = Layanan::factory()->create([
            'tipe_layanan' => 'per_satuan',
            'harga_per_kg' => 0,
            'harga_per_satuan' => 10000,
        ]);

        TransaksiLayanan::factory()->create([
            'transaksi_id' => $transaksi->id,
            'layanan_id' => $layananKg->id,
            'berat_kg' => 2.5,
            'jumlah_satuan' => 0,
            'subtotal' => 12500,
        ]);

        TransaksiLayanan::factory()->create([
            'transaksi_id' => $transaksi->id,
            'layanan_id' => $layananSatuan->id,
            'berat_kg' => 0,
            'jumlah_satuan' => 3,
            'subtotal' => 30000,
        ]);

        TransaksiHelper::hitungUlangTotal($transaksi);
        $transaksi->refresh();

        expect($transaksi->total_berat)->toBe('2.50');
        expect($transaksi->total_item)->toBe(3);
        expect($transaksi->jumlah_layanan)->toBe(2);
        expect($transaksi->subtotal)->toBe(42500);
        expect($transaksi->total)->toBe(42500);
    });

    test('applies discount correctly in total calculation', function () {
        $transaksi = Transaksi::factory()->create([
            'subtotal' => 0,
            'total' => 0,
            'total_berat' => 0,
            'total_item' => 0,
            'jumlah_layanan' => 0,
            'diskon' => 5000,
        ]);

        $layanan = Layanan::factory()->create([
            'harga_per_kg' => 5000,
            'harga_per_satuan' => null,
        ]);

        TransaksiLayanan::factory()->create([
            'transaksi_id' => $transaksi->id,
            'layanan_id' => $layanan->id,
            'berat_kg' => 5.0,
            'jumlah_satuan' => 0,
            'subtotal' => 25000,
        ]);

        TransaksiHelper::hitungUlangTotal($transaksi);
        $transaksi->refresh();

        expect($transaksi->subtotal)->toBe(25000);
        expect($transaksi->total)->toBe(20000); // 25000 - 5000
    });

    test('uses database transaction for atomic updates', function () {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $transaksi = Transaksi::factory()->create();

        TransaksiHelper::hitungUlangTotal($transaksi);
    });
});

describe('TransaksiHelper - getTanggalSelesaiTerlama', function () {
    test('returns null when no services', function () {
        $transaksi = Transaksi::factory()->create();

        $result = TransaksiHelper::getTanggalSelesaiTerlama($transaksi);

        expect($result)->toBeNull();
    });

    test('calculates completion date correctly for single service', function () {
        $tanggalMasuk = now();
        $transaksi = Transaksi::factory()->create([
            'tanggal_masuk' => $tanggalMasuk,
        ]);

        $layanan = Layanan::factory()->create(['durasi_jam' => 24]);

        TransaksiLayanan::factory()->create([
            'transaksi_id' => $transaksi->id,
            'layanan_id' => $layanan->id,
        ]);

        $result = TransaksiHelper::getTanggalSelesaiTerlama($transaksi);

        expect($result)->toBeInstanceOf(\Carbon\Carbon::class);
        expect(round($tanggalMasuk->diffInHours($result)))->toBe(24.0);
    });

    test('returns longest duration when multiple services', function () {
        $tanggalMasuk = now();
        $transaksi = Transaksi::factory()->create([
            'tanggal_masuk' => $tanggalMasuk,
        ]);

        $layanan1 = Layanan::factory()->create(['durasi_jam' => 24]);
        $layanan2 = Layanan::factory()->create(['durasi_jam' => 48]);
        $layanan3 = Layanan::factory()->create(['durasi_jam' => 12]);

        TransaksiLayanan::factory()->create([
            'transaksi_id' => $transaksi->id,
            'layanan_id' => $layanan1->id,
        ]);
        TransaksiLayanan::factory()->create([
            'transaksi_id' => $transaksi->id,
            'layanan_id' => $layanan2->id,
        ]);
        TransaksiLayanan::factory()->create([
            'transaksi_id' => $transaksi->id,
            'layanan_id' => $layanan3->id,
        ]);

        $result = TransaksiHelper::getTanggalSelesaiTerlama($transaksi);

        expect($result)->toBeInstanceOf(\Carbon\Carbon::class);
        expect(round($tanggalMasuk->diffInHours($result)))->toBe(48.0);
    });

    test('ignores services with zero duration', function () {
        $tanggalMasuk = now();
        $transaksi = Transaksi::factory()->create([
            'tanggal_masuk' => $tanggalMasuk,
        ]);

        $layanan1 = Layanan::factory()->create(['durasi_jam' => 0]);
        $layanan2 = Layanan::factory()->create(['durasi_jam' => 24]);

        TransaksiLayanan::factory()->create([
            'transaksi_id' => $transaksi->id,
            'layanan_id' => $layanan1->id,
        ]);
        TransaksiLayanan::factory()->create([
            'transaksi_id' => $transaksi->id,
            'layanan_id' => $layanan2->id,
        ]);

        $result = TransaksiHelper::getTanggalSelesaiTerlama($transaksi);

        expect($result)->toBeInstanceOf(\Carbon\Carbon::class);
        expect(round($tanggalMasuk->diffInHours($result)))->toBe(24.0);
    });
});

describe('TransaksiHelper - Validation Rules', function () {
    test('metadataRules returns correct validation rules', function () {
        $rules = TransaksiHelper::metadataRules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey(TransaksiHelper::META_PEMBAYARAN);
        expect($rules)->toHaveKey(TransaksiHelper::META_PROMO);
        expect($rules)->toHaveKey(TransaksiHelper::META_REFERRAL);
        expect($rules)->toHaveKey(TransaksiHelper::META_FOTO_BUKTI_TIMBANGAN);
        expect($rules)->toHaveKey(TransaksiHelper::META_FOTO_BUKTI_PEMBAYARAN);
        expect($rules)->toHaveKey(TransaksiHelper::META_KURIR_JEMPUT);
        expect($rules)->toHaveKey(TransaksiHelper::META_KURIR_ANTAR);
    });
});
