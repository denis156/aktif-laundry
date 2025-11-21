<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola metadata Transaksi
//
// ? Metadata yang didukung:
// * - pembayaran: Info pembayaran (metode, status, dll)
// * - promo: Data promo yang digunakan
// * - referral: Data referral yang digunakan
// * - foto_bukti_timbangan: Array path foto timbangan (multiple)
// * - foto_bukti_pembayaran: Array path foto bukti bayar (multiple)
// * - kurir_jemput: Nama kurir yang jemput
// * - kurir_antar: Nama kurir yang antar

class TransaksiHelper
{
    // * Status Transaksi Constants
    public const STATUS_MENUNGGU = 'Menunggu';

    public const STATUS_PROSES = 'Proses';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUS_DIAMBIL = 'Diambil';

    public const STATUS_BATAL = 'Batal';

    // * Metode Pembayaran Constants
    public const METODE_TUNAI = 'Tunai';

    public const METODE_NON_TUNAI = 'Non-Tunai';

    public const METODE_TRANSFER = 'Transfer';

    public const METODE_QRIS = 'QRIS';

    public const METODE_EWALLET = 'E-Wallet';

    public const METODE_DEBIT = 'Debit';

    public const METODE_KREDIT = 'Kredit';

    // * Metadata Keys
    public const META_PEMBAYARAN = 'pembayaran';

    public const META_PROMO = 'promo';

    public const META_REFERRAL = 'referral';

    public const META_FOTO_BUKTI_TIMBANGAN = 'foto_bukti_timbangan';

    public const META_FOTO_BUKTI_PEMBAYARAN = 'foto_bukti_pembayaran';

    public const META_KURIR_JEMPUT = 'kurir_jemput';

    public const META_KURIR_ANTAR = 'kurir_antar';

    // * Ambil nilai dari metadata
    public static function getMetadata(Transaksi $transaksi, string $key, mixed $default = null): mixed
    {
        return data_get($transaksi->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Transaksi $transaksi, string $key, mixed $value): void
    {
        try {
            $metadata = $transaksi->metadata ?? [];
            data_set($metadata, $key, $value);
            $transaksi->metadata = $metadata;
        } catch (\Exception $e) {
            Log::error('Failed to set Transaksi metadata', [
                'transaksi_id' => $transaksi->id,
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Transaksi $transaksi, array $data): void
    {
        try {
            $transaksi->metadata = array_merge($transaksi->metadata ?? [], $data);
        } catch (\Exception $e) {
            Log::error('Failed to merge Transaksi metadata', [
                'transaksi_id' => $transaksi->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Ambil informasi promo yang digunakan
    public static function getPromoInfo(Transaksi $transaksi): ?array
    {
        return self::getMetadata($transaksi, self::META_PROMO);
    }

    // * Set informasi promo yang digunakan
    public static function setPromoInfo(Transaksi $transaksi, array $promoData): void
    {
        self::setMetadata($transaksi, self::META_PROMO, $promoData);
    }

    // * Ambil informasi referral yang digunakan
    public static function getReferralInfo(Transaksi $transaksi): ?array
    {
        return self::getMetadata($transaksi, self::META_REFERRAL);
    }

    // * Set informasi referral yang digunakan
    public static function setReferralInfo(Transaksi $transaksi, array $referralData): void
    {
        self::setMetadata($transaksi, self::META_REFERRAL, $referralData);
    }

    // * Ambil nama kurir yang jemput cucian
    public static function getKurirJemput(Transaksi $transaksi): ?string
    {
        return self::getMetadata($transaksi, self::META_KURIR_JEMPUT);
    }

    // * Set nama kurir yang jemput cucian
    public static function setKurirJemput(Transaksi $transaksi, string $kurirName): void
    {
        self::setMetadata($transaksi, self::META_KURIR_JEMPUT, $kurirName);
    }

    // * Ambil nama kurir yang antar cucian
    public static function getKurirAntar(Transaksi $transaksi): ?string
    {
        return self::getMetadata($transaksi, self::META_KURIR_ANTAR);
    }

    // * Set nama kurir yang antar cucian
    public static function setKurirAntar(Transaksi $transaksi, string $kurirName): void
    {
        self::setMetadata($transaksi, self::META_KURIR_ANTAR, $kurirName);
    }

    // * Ambil informasi pembayaran transaksi
    public static function getPembayaranInfo(Transaksi $transaksi): ?array
    {
        return self::getMetadata($transaksi, self::META_PEMBAYARAN);
    }

    // * Set informasi pembayaran transaksi
    public static function setPembayaranInfo(Transaksi $transaksi, array $pembayaranData): void
    {
        self::setMetadata($transaksi, self::META_PEMBAYARAN, $pembayaranData);
    }

    // * Ambil semua foto bukti timbangan
    public static function getFotoBuktiTimbangan(Transaksi $transaksi): array
    {
        return (array) self::getMetadata($transaksi, self::META_FOTO_BUKTI_TIMBANGAN, []);
    }

    // * Tambah foto bukti timbangan ke array yang sudah ada
    public static function addFotoBuktiTimbangan(Transaksi $transaksi, string $fotoPath): void
    {
        try {
            $fotos = self::getFotoBuktiTimbangan($transaksi);
            $fotos[] = $fotoPath;
            self::setMetadata($transaksi, self::META_FOTO_BUKTI_TIMBANGAN, $fotos);
        } catch (\Exception $e) {
            Log::error('Failed to add Transaksi foto bukti timbangan', [
                'transaksi_id' => $transaksi->id,
                'foto_path' => $fotoPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Ambil semua foto bukti pembayaran
    public static function getFotoBuktiPembayaran(Transaksi $transaksi): array
    {
        return (array) self::getMetadata($transaksi, self::META_FOTO_BUKTI_PEMBAYARAN, []);
    }

    // * Tambah foto bukti pembayaran ke array yang sudah ada
    public static function addFotoBuktiPembayaran(Transaksi $transaksi, string $fotoPath): void
    {
        try {
            $fotos = self::getFotoBuktiPembayaran($transaksi);
            $fotos[] = $fotoPath;
            self::setMetadata($transaksi, self::META_FOTO_BUKTI_PEMBAYARAN, $fotos);
        } catch (\Exception $e) {
            Log::error('Failed to add Transaksi foto bukti pembayaran', [
                'transaksi_id' => $transaksi->id,
                'foto_path' => $fotoPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // * Get semua status transaksi yang tersedia
    public static function getAllStatus(): array
    {
        return [
            self::STATUS_MENUNGGU,
            self::STATUS_PROSES,
            self::STATUS_SELESAI,
            self::STATUS_DIAMBIL,
            self::STATUS_BATAL,
        ];
    }

    // * Get semua metode pembayaran yang tersedia
    public static function getAllMetodePembayaran(): array
    {
        return [
            self::METODE_TUNAI,
            self::METODE_NON_TUNAI,
            self::METODE_TRANSFER,
            self::METODE_QRIS,
            self::METODE_EWALLET,
            self::METODE_DEBIT,
            self::METODE_KREDIT,
        ];
    }

    // * Check apakah status valid
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::getAllStatus(), true);
    }

    // * Check apakah metode pembayaran valid
    public static function isValidMetodePembayaran(string $metode): bool
    {
        return in_array($metode, self::getAllMetodePembayaran(), true);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_PEMBAYARAN => 'nullable|array',
            self::META_PROMO => 'nullable|array',
            self::META_REFERRAL => 'nullable|array',
            self::META_FOTO_BUKTI_TIMBANGAN => 'nullable|array',
            self::META_FOTO_BUKTI_PEMBAYARAN => 'nullable|array',
            self::META_KURIR_JEMPUT => 'nullable|string|max:100',
            self::META_KURIR_ANTAR => 'nullable|string|max:100',
        ];
    }

    /**
     * Hitung ulang total dari detail layanan
     * Digunakan saat ada perubahan di transaksi_layanan
     *
     * @throws \Exception
     */
    public static function hitungUlangTotal(Transaksi $transaksi): void
    {
        try {
            DB::beginTransaction();

            $totalBerat = 0;
            $totalItem = 0;
            $subtotal = 0;

            foreach ($transaksi->transaksiLayanan as $tl) {
                if (TransaksiLayananHelper::isPerKg($tl)) {
                    $totalBerat += $tl->berat_kg;
                } else {
                    $totalItem += $tl->jumlah_satuan;
                }
                $subtotal += $tl->subtotal;
            }

            $transaksi->update([
                'total_berat' => $totalBerat,
                'total_item' => $totalItem,
                'jumlah_layanan' => $transaksi->transaksiLayanan()->count(),
                'subtotal' => $subtotal,
                'total' => $subtotal - $transaksi->diskon,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to recalculate transaksi total', [
                'transaksi_id' => $transaksi->id,
                'kode_transaksi' => $transaksi->kode_transaksi,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Dapatkan estimasi tanggal selesai berdasarkan durasi layanan terlama
     */
    public static function getTanggalSelesaiTerlama(Transaksi $transaksi): ?\Carbon\Carbon
    {
        $tanggalTerlama = null;

        foreach ($transaksi->transaksiLayanan as $tl) {
            if ($tl->layanan && $tl->layanan->durasi_jam > 0) {
                $tanggalSelesai = \Carbon\Carbon::parse($transaksi->tanggal_masuk)
                    ->addHours($tl->layanan->durasi_jam);

                if (! $tanggalTerlama || $tanggalSelesai > $tanggalTerlama) {
                    $tanggalTerlama = $tanggalSelesai;
                }
            }
        }

        return $tanggalTerlama;
    }

    /**
     * Generate kode transaksi otomatis dengan prefix dari pengaturan
     * Menghindari gap numbering dan duplicate kode
     *
     * @throws \Exception
     */
    public static function generateKodeTransaksi(): string
    {
        try {
            $prefix = PengaturanHelper::getValue('format_id_transaksi', 'TRX');
            $prefixLength = strlen($prefix);

            $lastTransaksi = Transaksi::withTrashed()->orderBy('kode_transaksi', 'desc')->first();

            if (! $lastTransaksi) {
                return $prefix.'001';
            }

            $lastNumber = (int) substr($lastTransaksi->kode_transaksi, $prefixLength);

            // Check if there are any gaps in the numbering by finding the next available number
            $nextNumber = $lastNumber + 1;

            // Verify if this number is already used (in case of deletions)
            while (Transaksi::where('kode_transaksi', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                $nextNumber++;
            }

            return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Failed to generate kode transaksi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
