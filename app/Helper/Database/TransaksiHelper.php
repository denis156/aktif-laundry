<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola Transaksi
//
// ? Semua data sudah disimpan di kolom terpisah (tidak pakai metadata JSON lagi)
// ? Kolom JSON hanya: foto_bukti_timbangan, foto_bukti_pembayaran
// ? Kolom kurir: kurir_jemput_id, kurir_antar_id, kurir_jemput_nama, kurir_antar_nama
// ? Kolom payment: metode_pembayaran, tipe_bayar, status_bayar, tanggal_bayar, jumlah_bayar

class TransaksiHelper
{
    // * Status Transaksi Constants
    public const STATUS_MENUNGGU = 'Menunggu';

    public const STATUS_PROSES = 'Proses';

    public const STATUS_PENGERJAAN = 'Pengerjaan';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUS_DIAMBIL = 'Diambil';

    public const STATUS_BATAL = 'Batal';

    // * Metode Pembayaran Constants (KAPAN bayar)
    public const METODE_BAYAR_SAAT_JEMPUT = 'Bayar Saat Jemput';

    public const METODE_BAYAR_SAAT_ANTAR = 'Bayar Saat Antar';

    // * Tipe Bayar Constants (BAGAIMANA cara bayar)
    public const TIPE_TUNAI = 'Tunai';

    public const TIPE_NON_TUNAI = 'Non-Tunai';

    // * Status Bayar Constants
    public const STATUS_BELUM_BAYAR = 'Belum Bayar';

    public const STATUS_MENUNGGU_VERIFIKASI = 'Menunggu Verifikasi';

    public const STATUS_SUDAH_BAYAR = 'Sudah Bayar';

    public const STATUS_DITOLAK = 'Ditolak';

    // * Ambil info kurir yang jemput cucian
    public static function getKurirJemput(Transaksi $transaksi): ?array
    {
        if ($transaksi->kurir_jemput_id || $transaksi->kurir_jemput_nama) {
            return [
                'id' => $transaksi->kurir_jemput_id,
                'nama' => $transaksi->kurir_jemput_nama,
            ];
        }

        return null;
    }

    // * Set info kurir yang jemput cucian
    public static function setKurirJemput(Transaksi $transaksi, ?int $kurirId, ?string $kurirNama = null): void
    {
        $transaksi->kurir_jemput_id = $kurirId;
        $transaksi->kurir_jemput_nama = $kurirNama;
    }

    // * Ambil info kurir yang antar cucian
    public static function getKurirAntar(Transaksi $transaksi): ?array
    {
        if ($transaksi->kurir_antar_id || $transaksi->kurir_antar_nama) {
            return [
                'id' => $transaksi->kurir_antar_id,
                'nama' => $transaksi->kurir_antar_nama,
            ];
        }

        return null;
    }

    // * Set info kurir yang antar cucian
    public static function setKurirAntar(Transaksi $transaksi, ?int $kurirId, ?string $kurirNama = null): void
    {
        $transaksi->kurir_antar_id = $kurirId;
        $transaksi->kurir_antar_nama = $kurirNama;
    }

    // * Ambil info referral yang digunakan
    public static function getReferral(Transaksi $transaksi): ?array
    {
        if ($transaksi->referral_id) {
            return [
                'id' => $transaksi->referral_id,
                'kode_referral' => $transaksi->referral?->kode_referral,
            ];
        }

        return null;
    }

    // * Set info referral yang digunakan
    public static function setReferral(Transaksi $transaksi, ?int $referralId): void
    {
        $transaksi->referral_id = $referralId;
    }

    // * Ambil snapshot nama pelanggan
    public static function getNamaPelanggan(Transaksi $transaksi): string
    {
        return $transaksi->nama_pelanggan;
    }

    // * Set snapshot nama pelanggan (saat transaksi dibuat)
    public static function setNamaPelanggan(Transaksi $transaksi, string $namaPelanggan): void
    {
        $transaksi->nama_pelanggan = $namaPelanggan;
    }

    // * Ambil info kasir yang input transaksi
    public static function getKasir(Transaksi $transaksi): ?array
    {
        if ($transaksi->kasir_id) {
            return [
                'id' => $transaksi->kasir_id,
                'nama' => $transaksi->kasir?->name,
                'email' => $transaksi->kasir?->email,
            ];
        }

        return null;
    }

    // * Set info kasir yang input transaksi
    public static function setKasir(Transaksi $transaksi, int $kasirId): void
    {
        $transaksi->kasir_id = $kasirId;
    }

    // * Ambil catatan transaksi (untuk customer)
    public static function getCatatan(Transaksi $transaksi): ?string
    {
        return $transaksi->catatan;
    }

    // * Set catatan transaksi (untuk customer)
    public static function setCatatan(Transaksi $transaksi, ?string $catatan): void
    {
        $transaksi->catatan = $catatan;
    }

    // * Ambil catatan internal (untuk staff)
    public static function getCatatanInternal(Transaksi $transaksi): ?string
    {
        return $transaksi->catatan_internal;
    }

    // * Set catatan internal (untuk staff)
    public static function setCatatanInternal(Transaksi $transaksi, ?string $catatan): void
    {
        $transaksi->catatan_internal = $catatan;
    }

    // * Ambil semua foto bukti timbangan
    public static function getFotoBuktiTimbangan(Transaksi $transaksi): array
    {
        return $transaksi->foto_bukti_timbangan ?? [];
    }

    // * Tambah foto bukti timbangan ke array yang sudah ada
    public static function addFotoBuktiTimbangan(Transaksi $transaksi, string $fotoPath): void
    {
        try {
            $fotos = self::getFotoBuktiTimbangan($transaksi);
            $fotos[] = $fotoPath;
            $transaksi->foto_bukti_timbangan = $fotos;
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
        return $transaksi->foto_bukti_pembayaran ?? [];
    }

    // * Tambah foto bukti pembayaran ke array yang sudah ada
    public static function addFotoBuktiPembayaran(Transaksi $transaksi, string $fotoPath): void
    {
        try {
            $fotos = self::getFotoBuktiPembayaran($transaksi);
            $fotos[] = $fotoPath;
            $transaksi->foto_bukti_pembayaran = $fotos;
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
            self::STATUS_PENGERJAAN,
            self::STATUS_SELESAI,
            self::STATUS_DIAMBIL,
            self::STATUS_BATAL,
        ];
    }

    // * Get semua metode pembayaran yang tersedia (KAPAN bayar)
    public static function getAllMetodePembayaran(): array
    {
        return [
            self::METODE_BAYAR_SAAT_JEMPUT,
            self::METODE_BAYAR_SAAT_ANTAR,
        ];
    }

    // * Get semua tipe bayar yang tersedia (BAGAIMANA cara bayar)
    public static function getAllTipeBayar(): array
    {
        return [
            self::TIPE_TUNAI,
            self::TIPE_NON_TUNAI,
        ];
    }

    // * Get semua status bayar yang tersedia
    public static function getAllStatusBayar(): array
    {
        return [
            self::STATUS_BELUM_BAYAR,
            self::STATUS_MENUNGGU_VERIFIKASI,
            self::STATUS_SUDAH_BAYAR,
            self::STATUS_DITOLAK,
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

    // * Check apakah tipe bayar valid
    public static function isValidTipeBayar(?string $tipe): bool
    {
        if ($tipe === null) {
            return true; // Nullable
        }

        return in_array($tipe, self::getAllTipeBayar(), true);
    }

    // * Check apakah status bayar valid
    public static function isValidStatusBayar(string $status): bool
    {
        return in_array($status, self::getAllStatusBayar(), true);
    }

    // ! Rules validasi
    public static function validationRules(): array
    {
        return [
            'kasir_id' => 'required|integer|exists:users,id',
            'pelanggan_id' => 'required|integer|exists:pelanggan,id',
            'referral_id' => 'nullable|integer|exists:referral,id',
            'nama_pelanggan' => 'required|string|max:255',
            'kurir_jemput_id' => 'nullable|integer|exists:kurir,id',
            'kurir_antar_id' => 'nullable|integer|exists:kurir,id',
            'kurir_jemput_nama' => 'nullable|string|max:100',
            'kurir_antar_nama' => 'nullable|string|max:100',
            'foto_bukti_timbangan' => 'nullable|array',
            'foto_bukti_pembayaran' => 'nullable|array',
            'metode_pembayaran' => 'nullable|string|max:50',
            'tipe_bayar' => 'nullable|string|max:50',
            'status_bayar' => 'nullable|string|max:50',
            'catatan' => 'nullable|string',
            'catatan_internal' => 'nullable|string',
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
                'total' => $subtotal, // Total akan dikurangi diskon jika ada
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

            // Gunakan raw query untuk mendapatkan nomor tertinggi dari kode transaksi
            // CAST ke unsigned untuk sorting numerik yang benar
            $lastTransaksi = Transaksi::withTrashed()
                ->where('kode_transaksi', 'LIKE', $prefix.'%')
                ->orderByRaw('CAST(SUBSTRING(kode_transaksi, '.($prefixLength + 1).') AS UNSIGNED) DESC')
                ->first();

            if (! $lastTransaksi) {
                return $prefix.'001';
            }

            // Ekstrak nomor dari kode transaksi terakhir
            $lastKode = $lastTransaksi->kode_transaksi;
            $lastNumber = (int) substr($lastKode, $prefixLength);

            // Mulai dari nomor berikutnya
            $nextNumber = $lastNumber + 1;

            // Pastikan kode belum digunakan (cek ulang untuk menghindari race condition)
            $attempts = 0;
            $maxAttempts = 100; // Batasi untuk mencegah infinite loop

            while (Transaksi::where('kode_transaksi', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                $nextNumber++;
                $attempts++;

                if ($attempts >= $maxAttempts) {
                    Log::error('TransaksiHelper: Max attempts reached for generating kode transaksi', [
                        'last_attempt' => $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT),
                        'prefix' => $prefix,
                    ]);
                    throw new \Exception('Gagal generate kode transaksi setelah '.$maxAttempts.' percobaan');
                }
            }

            $newKode = $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

            Log::info('TransaksiHelper: Generated new kode transaksi', [
                'new_kode' => $newKode,
                'last_kode' => $lastTransaksi->kode_transaksi ?? null,
                'last_number' => $lastNumber,
                'next_number' => $nextNumber,
            ]);

            return $newKode;
        } catch (\Exception $e) {
            Log::error('Failed to generate kode transaksi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get status options untuk dropdown
     */
    public static function getStatusOptions(): array
    {
        return [
            ['id' => self::STATUS_MENUNGGU, 'name' => 'Menunggu'],
            ['id' => self::STATUS_PROSES, 'name' => 'Proses'],
            ['id' => self::STATUS_PENGERJAAN, 'name' => 'Pengerjaan'],
            ['id' => self::STATUS_SELESAI, 'name' => 'Selesai'],
            ['id' => self::STATUS_DIAMBIL, 'name' => 'Diambil'],
            ['id' => self::STATUS_BATAL, 'name' => 'Batal'],
        ];
    }

    /**
     * Get metode pembayaran options untuk dropdown (KAPAN bayar)
     */
    public static function getMetodePembayaranOptions(): array
    {
        return [
            ['id' => self::METODE_BAYAR_SAAT_JEMPUT, 'name' => 'Bayar Saat Jemput'],
            ['id' => self::METODE_BAYAR_SAAT_ANTAR, 'name' => 'Bayar Saat Antar'],
        ];
    }

    /**
     * Get tipe bayar options untuk dropdown (BAGAIMANA cara bayar)
     */
    public static function getTipeBayarOptions(): array
    {
        return [
            ['id' => self::TIPE_TUNAI, 'name' => 'Tunai'],
            ['id' => self::TIPE_NON_TUNAI, 'name' => 'Non-Tunai (Transfer/QRIS/E-Wallet)'],
        ];
    }

    /**
     * Get status bayar options untuk dropdown
     */
    public static function getStatusBayarOptions(): array
    {
        return [
            ['id' => self::STATUS_BELUM_BAYAR, 'name' => 'Belum Bayar'],
            ['id' => self::STATUS_MENUNGGU_VERIFIKASI, 'name' => 'Menunggu Verifikasi'],
            ['id' => self::STATUS_SUDAH_BAYAR, 'name' => 'Sudah Bayar'],
            ['id' => self::STATUS_DITOLAK, 'name' => 'Ditolak'],
        ];
    }

    /**
     * Get badge class untuk status transaksi
     * Menunggu=Secondary, Proses=Warning, Pengerjaan=Info, Selesai=Success, Diambil=Neutral, Batal=Error
     */
    public static function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_MENUNGGU => 'badge-secondary',
            self::STATUS_PROSES => 'badge-warning',
            self::STATUS_PENGERJAAN => 'badge-info',
            self::STATUS_SELESAI => 'badge-success',
            self::STATUS_DIAMBIL => 'badge-neutral',
            self::STATUS_BATAL => 'badge-error',
            default => 'badge-ghost',
        };
    }

    /**
     * Get badge class untuk status pembayaran
     */
    public static function getStatusBayarBadgeClass(string $statusBayar): string
    {
        return match ($statusBayar) {
            self::STATUS_BELUM_BAYAR => 'badge-error',
            self::STATUS_MENUNGGU_VERIFIKASI => 'badge-warning',
            self::STATUS_SUDAH_BAYAR => 'badge-success',
            self::STATUS_DITOLAK => 'badge-error',
            default => 'badge-ghost',
        };
    }
}
