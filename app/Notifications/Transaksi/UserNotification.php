<?php

declare(strict_types=1);

namespace App\Notifications\Transaksi;

use App\Helper\Database\TransaksiHelper;
use App\Helper\FirebaseHelper;
use App\Models\Transaksi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Transaksi $transaksi,
        public string $status,
        public string $type = 'status_change'
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $messages = $this->getMessageForStatus($this->status);
        $deepLink = $this->getDeepLink();

        return [
            'title' => $messages['title'],
            'message' => $messages['message'],
            'type' => $this->type,
            'transaksi_id' => $this->transaksi->id,
            'kode_transaksi' => $this->transaksi->kode_transaksi,
            'status' => $this->status,
            'url' => $deepLink['url'],
            'deeplink' => $deepLink['deeplink'],
            'action' => $deepLink['action'],
        ];
    }

    /**
     * Send Firebase notification to user (admin/staff)
     */
    public function sendFirebase(int $userId): bool
    {
        $messages = $this->getMessageForStatus($this->status);

        return FirebaseHelper::sendToManagement(
            $userId,
            $messages['title'],
            $messages['message'],
            $this->toArray((object) [])
        );
    }

    /**
     * Send Firebase notification to all management users
     */
    public function sendFirebaseToAll(): bool
    {
        $messages = $this->getMessageForStatus($this->status);

        return FirebaseHelper::sendToAllManagement(
            $messages['title'],
            $messages['message'],
            $this->toArray((object) [])
        );
    }

    /**
     * Get message berdasarkan status untuk User/Admin
     */
    protected function getMessageForStatus(string $status): array
    {
        $kode = $this->transaksi->kode_transaksi;
        $pelanggan = $this->transaksi->nama_pelanggan;
        $kurirJemput = $this->transaksi->kurir_jemput_nama;
        $kurirAntar = $this->transaksi->kurir_antar_nama;

        // Handle payment notifications
        if ($this->type === 'payment_verification_needed') {
            return [
                'title' => 'Pembayaran Perlu Verifikasi',
                'message' => "Pembayaran pesanan {$kode} dari {$pelanggan} menunggu verifikasi",
            ];
        }

        return match ($status) {
            TransaksiHelper::STATUS_MENUNGGU => [
                'title' => 'Pesanan Baru Masuk',
                'message' => "Ada pesanan baru {$kode} dari {$pelanggan} yang perlu ditindaklanjuti",
            ],
            TransaksiHelper::STATUS_PROSES => [
                'title' => 'Pesanan Sedang Dijemput',
                'message' => $kurirJemput
                    ? "Pesanan {$kode} dari {$pelanggan} sedang dijemput oleh kurir {$kurirJemput}"
                    : "Pesanan {$kode} dari {$pelanggan} sedang dalam proses penjemputan",
            ],
            TransaksiHelper::STATUS_PENGERJAAN => [
                'title' => 'Pesanan Sedang Dikerjakan',
                'message' => "Pesanan {$kode} dari {$pelanggan} sedang dalam proses pengerjaan",
            ],
            TransaksiHelper::STATUS_SELESAI => [
                'title' => 'Pesanan Selesai Dikerjakan',
                'message' => $kurirAntar
                    ? "Pesanan {$kode} untuk {$pelanggan} akan diantar oleh kurir {$kurirAntar}"
                    : "Pesanan {$kode} untuk {$pelanggan} sudah selesai dan siap untuk diantar",
            ],
            TransaksiHelper::STATUS_DIAMBIL => [
                'title' => 'Pesanan Telah Selesai',
                'message' => "Pesanan {$kode} untuk {$pelanggan} telah selesai dan diambil",
            ],
            TransaksiHelper::STATUS_BATAL => [
                'title' => 'Pesanan Dibatalkan',
                'message' => "Pesanan {$kode} dari {$pelanggan} telah dibatalkan",
            ],
            default => [
                'title' => 'Update Pesanan',
                'message' => "Ada update pada pesanan {$kode} dari {$pelanggan}",
            ],
        };
    }

    /**
     * Get deep link berdasarkan status dan type
     * Admin: Semua status ke /management/transaksi/edit/{id}
     */
    protected function getDeepLink(): array
    {
        $transaksiId = $this->transaksi->id;
        $editUrl = "/management/transaksi/edit/{$transaksiId}";

        return match ($this->type) {
            'new_order' => [
                'url' => '/management/transaksi?status=Menunggu',
                'deeplink' => 'aktiflaundry://management/transaksi?status=Menunggu',
                'action' => 'Lihat Semua Pesanan Baru',
            ],
            'status_change' => [
                'url' => $editUrl,
                'deeplink' => "aktiflaundry://management/transaksi/edit/{$transaksiId}",
                'action' => match ($this->status) {
                    TransaksiHelper::STATUS_MENUNGGU => 'Assign Kurir',
                    TransaksiHelper::STATUS_PROSES => 'Lacak Penjemputan',
                    TransaksiHelper::STATUS_PENGERJAAN => 'Lihat Layanan',
                    TransaksiHelper::STATUS_SELESAI => 'Assign Kurir Antar',
                    TransaksiHelper::STATUS_DIAMBIL => 'Lihat Invoice',
                    default => 'Lihat Detail',
                },
            ],
            'kurir_antar_assigned' => [
                'url' => $editUrl,
                'deeplink' => "aktiflaundry://management/transaksi/edit/{$transaksiId}",
                'action' => 'Lacak Pengiriman',
            ],
            'payment_verification_needed' => [
                'url' => $editUrl,
                'deeplink' => "aktiflaundry://management/transaksi/edit/{$transaksiId}",
                'action' => 'Verifikasi Pembayaran',
            ],
            default => [
                'url' => $editUrl,
                'deeplink' => "aktiflaundry://management/transaksi/edit/{$transaksiId}",
                'action' => 'Buka',
            ],
        };
    }
}
