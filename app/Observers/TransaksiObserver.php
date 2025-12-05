<?php

declare(strict_types=1);

namespace App\Observers;

use App\Helper\Database\PromoHelper;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Log;

// ! Observer untuk Transaksi
// ? Otomatis handle business logic untuk transaksi
// ? - Tambahkan catatan jika promo tidak valid setelah ditimbang

class TransaksiObserver
{
    /**
     * Handle the Transaksi "saving" event.
     * Tambahkan catatan jika promo tidak valid dan sudah ada transaksi layanan
     */
    public function saving(Transaksi $transaksi): void
    {
        try {
            // Cek kondisi: Status = Proses, Ada kurir jemput, Ada layanan
            $statusProses = $transaksi->status === 'Proses';
            $adaKurirJemput = ! empty($transaksi->kurir_jemput_id);

            // Load transaksi layanan untuk cek apakah sudah ditimbang
            $transaksiLayanan = $transaksi->transaksiLayanan()->exists();

            // Jika belum memenuhi kondisi, skip
            if (! $statusProses || ! $adaKurirJemput || ! $transaksiLayanan) {
                return;
            }

            // Load transaksi promo terbaru
            $transaksiPromo = $transaksi->transaksiPromo()->latest()->first();

            // Jika tidak ada promo, skip
            if (! $transaksiPromo) {
                return;
            }

            // Load promo untuk validasi
            $promo = PromoHelper::getById($transaksiPromo->promo_id);

            if (! $promo) {
                return;
            }

            // Hitung total berat dari transaksi layanan
            $totalBerat = 0.0;
            foreach ($transaksi->transaksiLayanan as $tl) {
                if ($tl->layanan && $tl->layanan->tipe_layanan === 'per_kg') {
                    $totalBerat += (float) ($tl->berat_kg ?? 0);
                }
            }

            // Hitung validasi promo
            $pelangganId = $transaksi->pelanggan_id;
            $subtotal = (int) ($transaksi->subtotal ?? 0);

            $promoResult = PromoHelper::hitungDiskon($promo, $subtotal, $totalBerat, $pelangganId, $transaksi->id);

            // Jika promo valid, skip (tidak perlu tambah catatan)
            if ($promoResult['valid']) {
                return;
            }

            // Promo tidak valid, tambahkan catatan
            $catatanPromo = 'Promo Yang Anda Pilih Tidak Memenuhi Syarat';

            // Cek apakah catatan promo sudah ada, agar tidak duplikat
            $currentCatatan = $transaksi->catatan ?? '';
            if (stripos($currentCatatan, 'Promo Yang Anda Pilih Tidak Memenuhi Syarat') === false) {
                // Jika ada catatan dari pelanggan, pisahkan dengan pipeline
                if (! empty($currentCatatan)) {
                    $transaksi->catatan = $currentCatatan.' | '.$catatanPromo;
                } else {
                    $transaksi->catatan = $catatanPromo;
                }

                Log::info('TransaksiObserver: Catatan promo ditambahkan', [
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'promo_id' => $promo->id,
                    'kode_promo' => $promo->kode_promo,
                    'catatan' => $transaksi->catatan,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('TransaksiObserver: Failed to add promo note', [
                'transaksi_id' => $transaksi->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
