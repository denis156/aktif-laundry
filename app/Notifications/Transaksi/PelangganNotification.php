<?php

declare(strict_types=1);

namespace App\Notifications\Transaksi;

use App\Helper\Database\TransaksiHelper;
use App\Helper\FirebaseHelper;
use App\Models\Transaksi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PelangganNotification extends Notification
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
     * Send Firebase notification to pelanggan
     */
    public function sendFirebase(int $pelangganId): bool
    {
        $messages = $this->getMessageForStatus($this->status);

        return FirebaseHelper::sendToPelanggan(
            $pelangganId,
            $messages['title'],
            $messages['message'],
            $this->toArray((object) [])
        );
    }

    /**
     * Get message berdasarkan status untuk Pelanggan
     */
    protected function getMessageForStatus(string $status): array
    {
        $kode = $this->transaksi->kode_transaksi;
        $kurirJemput = $this->transaksi->kurir_jemput_nama;
        $kurirAntar = $this->transaksi->kurir_antar_nama;
        $jumlahBayar = number_format($this->transaksi->jumlah_bayar ?? $this->transaksi->total, 0, ',', '.');

        // Handle payment notifications
        if ($this->type === 'payment_confirmed') {
            return [
                'title' => 'Pembayaran Dikonfirmasi',
                'message' => "Pembayaran pesanan {$kode} sebesar Rp {$jumlahBayar} telah dikonfirmasi. Terima kasih!",
            ];
        }

        return match ($status) {
            TransaksiHelper::STATUS_MENUNGGU => [
                'title' => 'Pesanan Diterima',
                'message' => "Pesanan {$kode} telah diterima dan menunggu untuk dijemput oleh kurir kami",
            ],
            TransaksiHelper::STATUS_PROSES => [
                'title' => 'Kurir Sedang Menuju Lokasi',
                'message' => $kurirJemput
                    ? "Kurir {$kurirJemput} sedang dalam perjalanan untuk menjemput cucian Anda ({$kode})"
                    : "Kurir kami sedang dalam perjalanan untuk menjemput cucian Anda ({$kode})",
            ],
            TransaksiHelper::STATUS_PENGERJAAN => [
                'title' => 'Cucian Sedang Diproses',
                'message' => "Cucian Anda ({$kode}) sedang dalam proses pengerjaan oleh tim kami",
            ],
            TransaksiHelper::STATUS_SELESAI => [
                'title' => 'Cucian Sudah Selesai',
                'message' => $kurirAntar
                    ? "Cucian Anda ({$kode}) akan diantar oleh kurir {$kurirAntar}"
                    : "Cucian Anda ({$kode}) sudah selesai dan siap untuk diantar. Kurir kami akan segera menghubungi Anda",
            ],
            TransaksiHelper::STATUS_DIAMBIL => [
                'title' => 'Pesanan Selesai',
                'message' => "Terima kasih! Pesanan {$kode} telah selesai. Semoga cucian Anda memuaskan",
            ],
            TransaksiHelper::STATUS_BATAL => [
                'title' => 'Pesanan Dibatalkan',
                'message' => "Pesanan {$kode} telah dibatalkan. Jika ada pertanyaan, silakan hubungi kami",
            ],
            default => [
                'title' => 'Status Pesanan Diperbarui',
                'message' => "Status pesanan {$kode} telah diperbarui menjadi {$status}",
            ],
        };
    }

    /**
     * Get deep link berdasarkan status dan type
     * Pelanggan: Status lain ke /pelanggan/riwayat/{id}
     * Proses dengan kurir jemput ke /pelanggan/riwayat/{id}/kurir
     * Selesai dengan kurir antar ke /pelanggan/riwayat/{id}/kurir
     */
    protected function getDeepLink(): array
    {
        $transaksiId = $this->transaksi->id;
        $baseUrl = "/pelanggan/riwayat/{$transaksiId}";
        $kurirUrl = "/pelanggan/riwayat/{$transaksiId}/kurir";

        return match ($this->type) {
            'new_order' => [
                'url' => $baseUrl,
                'deeplink' => "aktiflaundry://pelanggan/riwayat/{$transaksiId}",
                'action' => 'Lihat Pesanan',
            ],
            'status_change' => match ($this->status) {
                TransaksiHelper::STATUS_PROSES => [
                    'url' => $this->transaksi->kurir_jemput_id ? $kurirUrl : $baseUrl,
                    'deeplink' => $this->transaksi->kurir_jemput_id
                        ? "aktiflaundry://pelanggan/riwayat/{$transaksiId}/kurir"
                        : "aktiflaundry://pelanggan/riwayat/{$transaksiId}",
                    'action' => $this->transaksi->kurir_jemput_id ? 'Lacak Kurir' : 'Lihat Detail',
                ],
                TransaksiHelper::STATUS_SELESAI => [
                    'url' => $this->transaksi->kurir_antar_id ? $kurirUrl : $baseUrl,
                    'deeplink' => $this->transaksi->kurir_antar_id
                        ? "aktiflaundry://pelanggan/riwayat/{$transaksiId}/kurir"
                        : "aktiflaundry://pelanggan/riwayat/{$transaksiId}",
                    'action' => $this->transaksi->kurir_antar_id ? 'Lacak Kurir Antar' : 'Lihat Detail',
                ],
                default => [
                    'url' => $baseUrl,
                    'deeplink' => "aktiflaundry://pelanggan/riwayat/{$transaksiId}",
                    'action' => 'Lihat Detail',
                ],
            },
            'kurir_antar_assigned' => [
                'url' => $kurirUrl,
                'deeplink' => "aktiflaundry://pelanggan/riwayat/{$transaksiId}/kurir",
                'action' => 'Lacak Pengiriman',
            ],
            'payment_confirmed' => [
                'url' => $baseUrl,
                'deeplink' => "aktiflaundry://pelanggan/riwayat/{$transaksiId}",
                'action' => 'Lihat Invoice',
            ],
            default => [
                'url' => $baseUrl,
                'deeplink' => "aktiflaundry://pelanggan/riwayat/{$transaksiId}",
                'action' => 'Buka',
            ],
        };
    }
}
