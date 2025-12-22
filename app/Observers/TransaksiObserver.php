<?php

declare(strict_types=1);

namespace App\Observers;

use App\Helper\Database\PromoHelper;
use App\Helper\Database\TransaksiHelper;
use App\Models\Transaksi;
use App\Notifications\Transaksi\KurirNotification;
use App\Notifications\Transaksi\PelangganNotification;
use App\Notifications\Transaksi\UserNotification;
use Illuminate\Support\Facades\Log;

// ! Observer untuk Transaksi
// ? Otomatis handle business logic untuk transaksi
// ? - Tambahkan catatan jika promo tidak valid setelah ditimbang
// ? - Kirim notifikasi ke kurir dan admin untuk pesanan baru dan perubahan status

class TransaksiObserver
{
    /**
     * Handle the Transaksi "created" event.
     * Kirim notifikasi ke semua kurir dan admin jika pesanan baru tanpa kasir
     */
    public function created(Transaksi $transaksi): void
    {
        try {
            // Jika pesanan baru tanpa kasir (dari pelanggan) dan status Menunggu
            if (! $transaksi->kasir_id && $transaksi->status === TransaksiHelper::STATUS_MENUNGGU) {
                $this->sendNewOrderNotification($transaksi);
            }
        } catch (\Exception $e) {
            Log::error('TransaksiObserver: Failed to send created notification', [
                'transaksi_id' => $transaksi->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the Transaksi "saving" event.
     * - Ubah status ke Proses jika ada kurir jemput
     * - Tambahkan catatan jika promo tidak valid dan sudah ada transaksi layanan
     */
    public function saving(Transaksi $transaksi): void
    {
        try {
            // Otomatis ubah status dari Menunggu ke Proses jika ada kurir jemput
            if ($transaksi->status === 'Menunggu' && ! empty($transaksi->kurir_jemput_id)) {
                $transaksi->status = 'Proses';

                Log::info('TransaksiObserver: Status changed to Proses', [
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'kurir_jemput_id' => $transaksi->kurir_jemput_id,
                    'kurir_jemput_nama' => $transaksi->kurir_jemput_nama,
                ]);
            }

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

    /**
     * Handle the Transaksi "updated" event.
     * Kirim notifikasi saat ada perubahan status, kurir antar, atau status pembayaran
     */
    public function updated(Transaksi $transaksi): void
    {
        try {
            // Cek apakah status berubah
            if ($transaksi->isDirty('status')) {
                $oldStatus = $transaksi->getOriginal('status');
                $newStatus = $transaksi->status;

                Log::info('TransaksiObserver: Status changed', [
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);

                // Kirim notifikasi perubahan status
                $this->sendStatusChangeNotification($transaksi, $oldStatus, $newStatus);

                // Jika status berubah ke Selesai (Siap Antar), kirim notif ke semua kurir
                if ($newStatus === TransaksiHelper::STATUS_SELESAI) {
                    $this->sendReadyForDeliveryNotification($transaksi);
                }
            }

            // Cek apakah kurir antar ditambahkan dan status Selesai
            if ($transaksi->isDirty('kurir_antar_id') &&
                $transaksi->kurir_antar_id &&
                $transaksi->status === TransaksiHelper::STATUS_SELESAI) {
                $this->sendKurirAntarAssignedNotification($transaksi);
            }

            // Cek apakah status pembayaran berubah
            if ($transaksi->isDirty('status_bayar')) {
                $oldStatusBayar = $transaksi->getOriginal('status_bayar');
                $newStatusBayar = $transaksi->status_bayar;

                Log::info('TransaksiObserver: Payment status changed', [
                    'transaksi_id' => $transaksi->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'old_status_bayar' => $oldStatusBayar,
                    'new_status_bayar' => $newStatusBayar,
                ]);

                $this->sendPaymentStatusChangeNotification($transaksi, $oldStatusBayar, $newStatusBayar);
            }
        } catch (\Exception $e) {
            Log::error('TransaksiObserver: Failed to send updated notification', [
                'transaksi_id' => $transaksi->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Kirim notifikasi pesanan baru ke semua kurir dan admin
     */
    protected function sendNewOrderNotification(Transaksi $transaksi): void
    {
        // Kirim ke semua admin via notification
        $userNotification = new UserNotification($transaksi, TransaksiHelper::STATUS_MENUNGGU, 'new_order');
        $sentToAdmin = $userNotification->sendFirebaseToAll();

        // Kirim ke semua kurir via notification
        $kurirNotification = new KurirNotification($transaksi, TransaksiHelper::STATUS_MENUNGGU, 'new_order');
        $sentToKurir = $kurirNotification->sendFirebaseToAll();

        Log::info('TransaksiObserver: New order notification sent', [
            'transaksi_id' => $transaksi->id,
            'kode_transaksi' => $transaksi->kode_transaksi,
            'sent_to_admin' => $sentToAdmin,
            'sent_to_kurir' => $sentToKurir,
        ]);
    }

    /**
     * Kirim notifikasi perubahan status ke pelanggan dan pihak terkait
     */
    protected function sendStatusChangeNotification(Transaksi $transaksi, string $oldStatus, string $newStatus): void
    {
        // Kirim ke pelanggan
        if ($transaksi->pelanggan_id) {
            $pelangganNotification = new PelangganNotification($transaksi, $newStatus, 'status_change');
            $sent = $pelangganNotification->sendFirebase($transaksi->pelanggan_id);

            Log::info('TransaksiObserver: Status change notification sent to pelanggan', [
                'transaksi_id' => $transaksi->id,
                'pelanggan_id' => $transaksi->pelanggan_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'sent' => $sent,
            ]);
        }
    }

    /**
     * Kirim notifikasi ke semua kurir saat pesanan siap untuk diantar
     */
    protected function sendReadyForDeliveryNotification(Transaksi $transaksi): void
    {
        // Kirim ke semua kurir via notification
        $kurirNotification = new KurirNotification($transaksi, TransaksiHelper::STATUS_SELESAI, 'ready_for_delivery');
        $sent = $kurirNotification->sendFirebaseToAll();

        Log::info('TransaksiObserver: Ready for delivery notification sent to all kurir', [
            'transaksi_id' => $transaksi->id,
            'kode_transaksi' => $transaksi->kode_transaksi,
            'sent' => $sent,
        ]);
    }

    /**
     * Kirim notifikasi saat kurir antar ditambahkan pada pesanan yang sudah selesai
     */
    protected function sendKurirAntarAssignedNotification(Transaksi $transaksi): void
    {
        // Kirim ke pelanggan
        if ($transaksi->pelanggan_id) {
            $pelangganNotification = new PelangganNotification($transaksi, TransaksiHelper::STATUS_SELESAI, 'kurir_antar_assigned');
            $sentToPelanggan = $pelangganNotification->sendFirebase($transaksi->pelanggan_id);

            Log::info('TransaksiObserver: Kurir antar assigned notification sent to pelanggan', [
                'transaksi_id' => $transaksi->id,
                'kode_transaksi' => $transaksi->kode_transaksi,
                'pelanggan_id' => $transaksi->pelanggan_id,
                'kurir_antar_nama' => $transaksi->kurir_antar_nama,
                'sent' => $sentToPelanggan,
            ]);
        }

        // Kirim ke semua admin/user
        $userNotification = new UserNotification($transaksi, TransaksiHelper::STATUS_SELESAI, 'kurir_antar_assigned');
        $sentToAdmin = $userNotification->sendFirebaseToAll();

        Log::info('TransaksiObserver: Kurir antar assigned notification sent to admin', [
            'transaksi_id' => $transaksi->id,
            'kode_transaksi' => $transaksi->kode_transaksi,
            'kurir_antar_nama' => $transaksi->kurir_antar_nama,
            'sent' => $sentToAdmin,
        ]);
    }

    /**
     * Kirim notifikasi saat status pembayaran berubah
     */
    protected function sendPaymentStatusChangeNotification(Transaksi $transaksi, ?string $oldStatus, ?string $newStatus): void
    {
        // Jika status berubah ke Menunggu Verifikasi, kirim notif ke admin
        if ($newStatus === TransaksiHelper::STATUS_MENUNGGU_VERIFIKASI) {
            $userNotification = new UserNotification($transaksi, $transaksi->status, 'payment_verification_needed');
            $sentToAdmin = $userNotification->sendFirebaseToAll();

            Log::info('TransaksiObserver: Payment verification notification sent to admin', [
                'transaksi_id' => $transaksi->id,
                'kode_transaksi' => $transaksi->kode_transaksi,
                'status_bayar' => $newStatus,
                'sent' => $sentToAdmin,
            ]);
        }

        // Jika status berubah ke Sudah Bayar, kirim notif ke pelanggan dan kurir
        if ($newStatus === TransaksiHelper::STATUS_SUDAH_BAYAR) {
            // Kirim ke pelanggan
            if ($transaksi->pelanggan_id) {
                $pelangganNotification = new PelangganNotification($transaksi, $transaksi->status, 'payment_confirmed');
                $sentToPelanggan = $pelangganNotification->sendFirebase($transaksi->pelanggan_id);

                Log::info('TransaksiObserver: Payment confirmed notification sent to pelanggan', [
                    'transaksi_id' => $transaksi->id,
                    'pelanggan_id' => $transaksi->pelanggan_id,
                    'sent' => $sentToPelanggan,
                ]);
            }

            // Kirim ke kurir jemput jika ada
            if ($transaksi->kurir_jemput_id) {
                $kurirNotification = new KurirNotification($transaksi, $transaksi->status, 'payment_confirmed');
                $sentToKurir = $kurirNotification->sendFirebase($transaksi->kurir_jemput_id);

                Log::info('TransaksiObserver: Payment confirmed notification sent to kurir jemput', [
                    'transaksi_id' => $transaksi->id,
                    'kurir_jemput_id' => $transaksi->kurir_jemput_id,
                    'sent' => $sentToKurir,
                ]);
            }

            // Kirim ke kurir antar jika ada
            if ($transaksi->kurir_antar_id) {
                $kurirNotification = new KurirNotification($transaksi, $transaksi->status, 'payment_confirmed');
                $sentToKurir = $kurirNotification->sendFirebase($transaksi->kurir_antar_id);

                Log::info('TransaksiObserver: Payment confirmed notification sent to kurir antar', [
                    'transaksi_id' => $transaksi->id,
                    'kurir_antar_id' => $transaksi->kurir_antar_id,
                    'sent' => $sentToKurir,
                ]);
            }
        }
    }
}
