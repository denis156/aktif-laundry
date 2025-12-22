<?php

declare(strict_types=1);

namespace App\Notifications\Transaksi;

use App\Helper\Database\TransaksiHelper;
use App\Helper\FirebaseHelper;
use App\Models\Transaksi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KurirNotification extends Notification
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
     * Send Firebase notification to kurir
     */
    public function sendFirebase(int $kurirId): bool
    {
        $messages = $this->getMessageForStatus($this->status);

        return FirebaseHelper::sendToKurir(
            $kurirId,
            $messages['title'],
            $messages['message'],
            $this->toArray((object) [])
        );
    }

    /**
     * Send Firebase notification to all kurir
     */
    public function sendFirebaseToAll(): bool
    {
        $messages = $this->getMessageForStatus($this->status);

        return FirebaseHelper::sendToAllKurir(
            $messages['title'],
            $messages['message'],
            $this->toArray((object) [])
        );
    }

    /**
     * Get message berdasarkan status untuk Kurir
     */
    protected function getMessageForStatus(string $status): array
    {
        $kode = $this->transaksi->kode_transaksi;
        $pelanggan = $this->transaksi->nama_pelanggan;
        $jumlahBayar = number_format($this->transaksi->jumlah_bayar ?? $this->transaksi->total, 0, ',', '.');

        // Handle payment notifications
        if ($this->type === 'payment_confirmed') {
            return [
                'title' => 'Pembayaran Dikonfirmasi',
                'message' => "Pembayaran pesanan {$kode} ({$pelanggan}) sebesar Rp {$jumlahBayar} telah dikonfirmasi",
            ];
        }

        return match ($status) {
            TransaksiHelper::STATUS_MENUNGGU => [
                'title' => 'Pesanan Baru Menunggu Penjemputan',
                'message' => "Ada pesanan baru {$kode} dari {$pelanggan} yang menunggu untuk dijemput",
            ],
            TransaksiHelper::STATUS_PROSES => [
                'title' => 'Pesanan Sedang Dijemput',
                'message' => "Pesanan {$kode} dari {$pelanggan} sedang dalam proses penjemputan",
            ],
            TransaksiHelper::STATUS_PENGERJAAN => [
                'title' => 'Pesanan Sedang Dikerjakan',
                'message' => "Pesanan {$kode} dari {$pelanggan} sedang dalam proses pengerjaan",
            ],
            TransaksiHelper::STATUS_SELESAI => [
                'title' => 'Pesanan Siap Diantar',
                'message' => "Pesanan {$kode} untuk {$pelanggan} sudah selesai dan siap untuk diantar",
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
     * Kurir: Menunggu ke /kurir/aktifitas/{id}, Proses & Selesai ke /kurir/rute/{id}
     */
    protected function getDeepLink(): array
    {
        $transaksiId = $this->transaksi->id;

        return match ($this->type) {
            'new_order' => [
                'url' => '/kurir/rute?status=Menunggu',
                'deeplink' => 'aktiflaundry://kurir/rute?status=Menunggu',
                'action' => 'Lihat Pesanan Baru',
            ],
            'ready_for_delivery' => [
                'url' => '/kurir/rute?status=Selesai',
                'deeplink' => 'aktiflaundry://kurir/rute?status=Selesai',
                'action' => 'Lihat Pesanan Siap Antar',
            ],
            'status_change' => match ($this->status) {
                TransaksiHelper::STATUS_MENUNGGU => [
                    'url' => "/kurir/aktifitas/{$transaksiId}",
                    'deeplink' => "aktiflaundry://kurir/aktifitas/{$transaksiId}",
                    'action' => 'Jemput Pesanan',
                ],
                TransaksiHelper::STATUS_PROSES => [
                    'url' => "/kurir/rute/{$transaksiId}",
                    'deeplink' => "aktiflaundry://kurir/rute/{$transaksiId}",
                    'action' => 'Navigasi ke Lokasi',
                ],
                TransaksiHelper::STATUS_SELESAI => [
                    'url' => "/kurir/rute/{$transaksiId}",
                    'deeplink' => "aktiflaundry://kurir/rute/{$transaksiId}",
                    'action' => 'Antar Pesanan',
                ],
                default => [
                    'url' => "/kurir/aktifitas/{$transaksiId}",
                    'deeplink' => "aktiflaundry://kurir/aktifitas/{$transaksiId}",
                    'action' => 'Lihat Detail',
                ],
            },
            'payment_confirmed' => [
                'url' => "/kurir/aktifitas/{$transaksiId}",
                'deeplink' => "aktiflaundry://kurir/aktifitas/{$transaksiId}",
                'action' => 'Lihat Detail',
            ],
            default => [
                'url' => "/kurir/aktifitas/{$transaksiId}",
                'deeplink' => "aktiflaundry://kurir/aktifitas/{$transaksiId}",
                'action' => 'Buka',
            ],
        };
    }
}
