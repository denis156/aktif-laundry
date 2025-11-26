<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Promo;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola Promo
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Kolom JSON hanya: layanan_ids, exclude_pelanggan_ids
// ? Kolom lain: banner_image, terms_conditions, auto_apply, min_berat, max_berat

class PromoHelper
{
    // * Tipe Diskon Constants
    public const TIPE_PERSEN = 'persen';

    public const TIPE_NOMINAL = 'nominal';

    public const TIPE_GRATIS_KG = 'gratis_kg';

    public const TIPE_GRATIS_HARI = 'gratis_hari';

    public const TIPE_CASHBACK = 'cashback';

    public const TIPE_GRATIS_ONGKIR = 'gratis_ongkir';

    /**
     * Get semua tipe diskon yang tersedia untuk dropdown
     */
    public static function getTipeDiskonOptions(): array
    {
        return [
            ['id' => self::TIPE_PERSEN, 'name' => 'Diskon Persen (%)', 'suffix' => '%', 'hint' => 'Diskon dalam persentase'],
            ['id' => self::TIPE_NOMINAL, 'name' => 'Diskon Nominal (Rp)', 'suffix' => 'Rp', 'hint' => 'Potongan harga langsung'],
            ['id' => self::TIPE_GRATIS_KG, 'name' => 'Gratis Kiloan (Kg)', 'suffix' => 'Kg', 'hint' => 'Gratis cuci X kilogram'],
            ['id' => self::TIPE_GRATIS_HARI, 'name' => 'Gratis Hari', 'suffix' => 'Hari', 'hint' => 'Gratis cuci selama X hari'],
            ['id' => self::TIPE_CASHBACK, 'name' => 'Cashback', 'suffix' => 'Rp', 'hint' => 'Cashback ke saldo/poin'],
            ['id' => self::TIPE_GRATIS_ONGKIR, 'name' => 'Gratis Ongkir', 'suffix' => '', 'hint' => 'Gratis biaya pengiriman'],
        ];
    }

    /**
     * Get suffix untuk nilai diskon berdasarkan tipe
     */
    public static function getSuffixByTipe(string $tipeDiskon): string
    {
        return match ($tipeDiskon) {
            self::TIPE_PERSEN => '%',
            self::TIPE_NOMINAL, self::TIPE_CASHBACK => 'Rp',
            self::TIPE_GRATIS_KG => 'Kg',
            self::TIPE_GRATIS_HARI => 'Hari',
            default => '',
        };
    }

    /**
     * Format nilai diskon untuk tampilan
     */
    public static function formatNilaiDiskon(string $tipeDiskon, ?int $nilaiDiskon): string
    {
        if ($nilaiDiskon === null) {
            return 'Gratis Ongkir';
        }

        return match ($tipeDiskon) {
            self::TIPE_PERSEN => $nilaiDiskon.'%',
            self::TIPE_NOMINAL, self::TIPE_CASHBACK => 'Rp '.number_format($nilaiDiskon, 0, ',', '.'),
            self::TIPE_GRATIS_KG => $nilaiDiskon.' Kg',
            self::TIPE_GRATIS_HARI => $nilaiDiskon.' Hari',
            self::TIPE_GRATIS_ONGKIR => 'Gratis Ongkir',
            default => (string) $nilaiDiskon,
        };
    }

    // * Ambil array ID layanan yang berlaku untuk promo
    public static function getLayananIds(Promo $promo): array
    {
        return $promo->layanan_ids ?? [];
    }

    // * Set array ID layanan yang berlaku untuk promo
    public static function setLayananIds(Promo $promo, array $layananIds): void
    {
        $promo->layanan_ids = $layananIds;
    }

    // ? Cek apakah promo berlaku untuk layanan tertentu
    public static function isBerlakuUntukLayanan(Promo $promo, int $layananId): bool
    {
        $layananIds = self::getLayananIds($promo);

        // Jika empty, berarti berlaku untuk semua layanan
        if (empty($layananIds)) {
            return true;
        }

        return in_array($layananId, $layananIds, true);
    }

    // * Ambil array ID pelanggan yang tidak boleh pakai promo
    public static function getExcludePelangganIds(Promo $promo): array
    {
        return $promo->exclude_pelanggan_ids ?? [];
    }

    // * Set array ID pelanggan yang tidak boleh pakai promo
    public static function setExcludePelangganIds(Promo $promo, array $pelangganIds): void
    {
        $promo->exclude_pelanggan_ids = $pelangganIds;
    }

    // ? Cek apakah pelanggan termasuk yang di-exclude
    public static function isPelangganExcluded(Promo $promo, int $pelangganId): bool
    {
        $excludedIds = self::getExcludePelangganIds($promo);

        return in_array($pelangganId, $excludedIds, true);
    }

    // * Ambil path gambar banner promo
    public static function getBannerImage(Promo $promo): ?string
    {
        return $promo->banner_image;
    }

    // * Set path gambar banner promo
    public static function setBannerImage(Promo $promo, ?string $imagePath): void
    {
        $promo->banner_image = $imagePath;
    }

    // * Ambil syarat dan ketentuan promo
    public static function getTermsConditions(Promo $promo): ?string
    {
        return $promo->terms_conditions;
    }

    // * Set syarat dan ketentuan promo
    public static function setTermsConditions(Promo $promo, ?string $terms): void
    {
        $promo->terms_conditions = $terms;
    }

    // ? Cek apakah promo otomatis terapply
    public static function isAutoApply(Promo $promo): bool
    {
        return (bool) $promo->auto_apply;
    }

    // * Set apakah promo otomatis terapply
    public static function setAutoApply(Promo $promo, bool $value): void
    {
        $promo->auto_apply = $value;
    }

    // * Ambil minimum berat
    public static function getMinBerat(Promo $promo): ?float
    {
        return $promo->min_berat !== null ? (float) $promo->min_berat : null;
    }

    // * Set minimum berat
    public static function setMinBerat(Promo $promo, ?float $value): void
    {
        $promo->min_berat = $value;
    }

    // * Ambil maksimum berat
    public static function getMaxBerat(Promo $promo): ?float
    {
        return $promo->max_berat !== null ? (float) $promo->max_berat : null;
    }

    // * Set maksimum berat
    public static function setMaxBerat(Promo $promo, ?float $value): void
    {
        $promo->max_berat = $value;
    }

    // ? Cek apakah berat memenuhi syarat promo
    public static function isBeratValid(Promo $promo, float $berat): bool
    {
        $minBerat = self::getMinBerat($promo);
        $maxBerat = self::getMaxBerat($promo);

        if ($minBerat !== null && $berat < $minBerat) {
            return false;
        }

        if ($maxBerat !== null && $berat > $maxBerat) {
            return false;
        }

        return true;
    }

    // ! Rules validasi
    public static function validationRules(): array
    {
        return [
            'layanan_ids' => 'nullable|array',
            'exclude_pelanggan_ids' => 'nullable|array',
            'banner_image' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'auto_apply' => 'nullable|boolean',
            'min_berat' => 'nullable|numeric|min:0',
            'max_berat' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Cek apakah promo masih valid (aktif, dalam periode, ada kuota)
     */
    public static function isValid(Promo $promo): bool
    {
        $now = now();

        return $promo->status === 'Aktif'
            && $promo->tanggal_mulai <= $now
            && $promo->tanggal_berakhir >= $now
            && ($promo->kuota_total === null || $promo->kuota_terpakai < $promo->kuota_total);
    }

    /**
     * Cek apakah masih ada kuota
     */
    public static function hasQuota(Promo $promo): bool
    {
        return $promo->kuota_total === null || $promo->kuota_terpakai < $promo->kuota_total;
    }

    /**
     * Tambah counter penggunaan dan update status jika habis
     *
     * @throws \Exception
     */
    public static function incrementUsage(Promo $promo): void
    {
        try {
            $promo->increment('kuota_terpakai');

            if ($promo->kuota_total && $promo->kuota_terpakai >= $promo->kuota_total) {
                $promo->update(['status' => 'Habis']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to increment Promo usage', [
                'promo_id' => $promo->id,
                'kode_promo' => $promo->kode_promo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate kode promo unik
     */
    public static function generateKodePromo(): string
    {
        try {
            $prefix = PengaturanHelper::getValue('format_id_promo', 'PROMO');
            $prefixLength = strlen($prefix);

            $lastPromo = Promo::withTrashed()->orderBy('kode_promo', 'desc')->first();

            if (! $lastPromo) {
                return $prefix.'001';
            }

            $lastNumber = (int) substr($lastPromo->kode_promo, $prefixLength);
            $nextNumber = $lastNumber + 1;

            // Check if there are any gaps in the numbering
            while (Promo::where('kode_promo', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                $nextNumber++;
            }

            return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Failed to generate kode promo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get promo options untuk dropdown (hanya yang valid)
     */
    public static function getPromoOptions(): array
    {
        try {
            return Promo::where('status', 'Aktif')
                ->where('tanggal_mulai', '<=', now())
                ->where('tanggal_berakhir', '>=', now())
                ->orderBy('kode_promo')
                ->get()
                ->filter(fn ($promo) => self::hasQuota($promo))
                ->map(fn ($promo) => [
                    'id' => $promo->id,
                    'name' => $promo->kode_promo.' - '.$promo->nama_promo.' ('.self::formatNilaiDiskon($promo->tipe_diskon, $promo->nilai_diskon).')',
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get promo options', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Hitung nilai diskon dari promo berdasarkan subtotal dan berat
     *
     * @param  Promo  $promo  Promo yang digunakan
     * @param  int  $subtotal  Subtotal transaksi dalam Rupiah
     * @param  float  $totalBerat  Total berat dalam kg (untuk tipe gratis_kg)
     * @param  int|null  $pelangganId  ID pelanggan untuk validasi exclude
     * @return array{valid: bool, diskon: int, pesan: string, tipe: string}
     */
    public static function hitungDiskon(Promo $promo, int $subtotal, float $totalBerat = 0, ?int $pelangganId = null): array
    {
        // Validasi promo masih valid
        if (! self::isValid($promo)) {
            return [
                'valid' => false,
                'diskon' => 0,
                'pesan' => 'Promo tidak aktif atau sudah berakhir',
                'tipe' => $promo->tipe_diskon,
            ];
        }

        // Validasi min transaksi
        if ($promo->min_transaksi && $subtotal < $promo->min_transaksi) {
            return [
                'valid' => false,
                'diskon' => 0,
                'pesan' => 'Minimum transaksi Rp '.number_format($promo->min_transaksi, 0, ',', '.'),
                'tipe' => $promo->tipe_diskon,
            ];
        }

        // Validasi pelanggan tidak di-exclude
        if ($pelangganId && self::isPelangganExcluded($promo, $pelangganId)) {
            return [
                'valid' => false,
                'diskon' => 0,
                'pesan' => 'Promo tidak berlaku untuk pelanggan ini',
                'tipe' => $promo->tipe_diskon,
            ];
        }

        // Validasi berat jika ada syarat
        if (! self::isBeratValid($promo, $totalBerat)) {
            $minBerat = self::getMinBerat($promo);
            $maxBerat = self::getMaxBerat($promo);
            $pesan = 'Berat tidak memenuhi syarat';
            if ($minBerat !== null) {
                $pesan = "Minimum berat {$minBerat} kg";
            }
            if ($maxBerat !== null) {
                $pesan = "Maksimum berat {$maxBerat} kg";
            }

            return [
                'valid' => false,
                'diskon' => 0,
                'pesan' => $pesan,
                'tipe' => $promo->tipe_diskon,
            ];
        }

        // Hitung diskon berdasarkan tipe
        $diskon = 0;
        $pesan = '';

        switch ($promo->tipe_diskon) {
            case self::TIPE_PERSEN:
                $diskon = (int) (($promo->nilai_diskon / 100) * $subtotal);
                // Apply diskon maksimal jika ada
                if ($promo->diskon_maksimal && $diskon > $promo->diskon_maksimal) {
                    $diskon = $promo->diskon_maksimal;
                }
                $pesan = "Diskon {$promo->nilai_diskon}%";
                break;

            case self::TIPE_NOMINAL:
                $diskon = $promo->nilai_diskon;
                // Diskon tidak boleh melebihi subtotal
                if ($diskon > $subtotal) {
                    $diskon = $subtotal;
                }
                $pesan = 'Potongan Rp '.number_format($promo->nilai_diskon, 0, ',', '.');
                break;

            case self::TIPE_GRATIS_KG:
                // Hitung harga per kg rata-rata dari subtotal dan berat
                if ($totalBerat > 0) {
                    $hargaPerKg = (int) ($subtotal / $totalBerat);
                    $diskon = $hargaPerKg * $promo->nilai_diskon;
                    // Diskon tidak boleh melebihi subtotal
                    if ($diskon > $subtotal) {
                        $diskon = $subtotal;
                    }
                }
                $pesan = "Gratis {$promo->nilai_diskon} kg";
                break;

            case self::TIPE_GRATIS_HARI:
                // Untuk gratis hari, diskon biasanya 100% atau disimpan sebagai catatan
                $diskon = $subtotal;
                $pesan = "Gratis cuci {$promo->nilai_diskon} hari";
                break;

            case self::TIPE_CASHBACK:
                // Cashback diberikan setelah transaksi selesai, bukan sebagai diskon langsung
                $diskon = 0;
                $pesan = 'Cashback Rp '.number_format($promo->nilai_diskon, 0, ',', '.').' akan diberikan';
                break;

            case self::TIPE_GRATIS_ONGKIR:
                // Gratis ongkir tidak mengurangi subtotal
                $diskon = 0;
                $pesan = 'Gratis biaya pengiriman';
                break;

            default:
                $pesan = 'Tipe diskon tidak dikenali';
                break;
        }

        return [
            'valid' => true,
            'diskon' => $diskon,
            'pesan' => $pesan,
            'tipe' => $promo->tipe_diskon,
        ];
    }

    /**
     * Get promo by ID dengan validasi
     */
    public static function getById(int $id): ?Promo
    {
        try {
            return Promo::find($id);
        } catch (\Exception $e) {
            Log::error('Failed to get promo by ID', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
