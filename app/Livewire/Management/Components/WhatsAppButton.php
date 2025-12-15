<?php

declare(strict_types=1);

namespace App\Livewire\Management\Components;

use App\Helper\Database\PengaturanHelper;
use App\Helper\Database\TransaksiLayananHelper;
use App\Helper\FonnteHelper;
use App\Helper\PhoneNumber;
use App\Models\Message;
use App\Models\Transaksi;
use Livewire\Component;
use Mary\Traits\Toast;

class WhatsAppButton extends Component
{
    use Toast;

    public int $transaksiId;

    public string $phoneNumber = '';

    public string $size = 'sm'; // default sm, bisa di-override dengan md, lg

    public string $btnClass = 'btn-success'; // default success, bisa btn-primary, btn-soft-success, dll

    public function mount(int $transaksiId, string $size = 'sm', string $btnClass = 'btn-success'): void
    {
        $this->transaksiId = $transaksiId;
        $this->size = $size;
        $this->btnClass = $btnClass;

        // Get phone number from transaksi pelanggan
        $transaksi = Transaksi::with('pelanggan')->find($transaksiId);
        $this->phoneNumber = $transaksi?->pelanggan?->no_hp ?? '';
    }

    public function sendWhatsApp(): void
    {
        $transaksi = Transaksi::with(['pelanggan', 'transaksiLayanan.layanan'])
            ->find($this->transaksiId);

        if (! $transaksi) {
            $this->error('Transaksi tidak ditemukan', position: 'toast-bottom');

            return;
        }

        // Generate WhatsApp message text
        $message = $this->generateReceiptText($transaksi);

        // Normalize phone number menggunakan PhoneNumber Helper
        $normalizedPhone = PhoneNumber::normalize($this->phoneNumber);

        if (! $normalizedPhone) {
            $this->error('Nomor telepon tidak valid', position: 'toast-bottom');

            return;
        }

        try {
            // Get connected device from Fonnte
            $fonnte = new FonnteHelper();
            $devicesResponse = $fonnte->getAllDevices();

            if (! $devicesResponse['status']) {
                $this->error('Gagal mengambil daftar device: '.($devicesResponse['error'] ?? 'Unknown error'), position: 'toast-bottom');

                return;
            }

            // Find first connected device
            $connectedDevice = null;
            if (isset($devicesResponse['data']['data'])) {
                foreach ($devicesResponse['data']['data'] as $device) {
                    if ($device['status'] === 'connect') {
                        $connectedDevice = $device;
                        break;
                    }
                }
            }

            if (! $connectedDevice) {
                $this->error('Tidak ada device WhatsApp yang terhubung. Silakan hubungkan device terlebih dahulu.', position: 'toast-bottom');

                return;
            }

            // Send message via Fonnte using connected device token
            $response = $fonnte->sendMessage(
                phoneNumber: $normalizedPhone,
                message: $message,
                deviceToken: $connectedDevice['token']
            );

            if (! $response['status']) {
                $errorMessage = $response['error'] ?? 'Gagal mengirim pesan WhatsApp';
                $this->error($errorMessage, position: 'toast-bottom');

                return;
            }

            // Save message to database
            Message::create([
                'target' => $normalizedPhone,
                'message' => $message,
                'url' => null,
                'filename' => null,
                'schedule' => '0',
                'typing' => 'false',
                'delay' => '0',
                'countryCode' => '62',
                'file' => null,
                'location' => null,
                'followup' => '0',
            ]);

            $this->success('Pesan WhatsApp berhasil dikirim!', position: 'toast-bottom');

            // Dispatch event untuk reset Alpine.js state
            $this->dispatch('whatsapp-sent');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: '.$e->getMessage(), position: 'toast-bottom');
        }
    }

    private function generateReceiptText(Transaksi $transaksi): string
    {
        $namaToko = PengaturanHelper::getValue('nama_toko', 'AKTIF LAUNDRY');
        $whatsapp = PengaturanHelper::getValue('whatsapp', '');
        $email = PengaturanHelper::getValue('email', '');
        $jamBuka = PengaturanHelper::getValue('jam_buka', '08:00');
        $jamTutup = PengaturanHelper::getValue('jam_tutup', '21:00');

        $text = '*'.strtoupper($namaToko)."*\n";
        if (! empty($whatsapp)) {
            $text .= "WA: {$whatsapp}\n";
        }
        if (! empty($email)) {
            $text .= "Email: {$email}\n";
        }
        $text .= "Buka: {$jamBuka} - Tutup: {$jamTutup}\n";
        $text .= str_repeat('-', 25)."\n\n";

        // Info Transaksi
        $text .= "*STRUK TRANSAKSI*\n";
        $text .= "No: *{$transaksi->kode_transaksi}*\n";
        $text .= 'Tanggal: '.$transaksi->tanggal_masuk->format('d/m/Y H:i')."\n\n";

        // Info Pelanggan menggunakan PhoneNumber Helper untuk format
        $text .= "*PELANGGAN*\n";
        $text .= "Nama: {$transaksi->nama_pelanggan}\n";
        $pelangganPhone = $transaksi->pelanggan?->no_hp
            ? (PhoneNumber::formatLocal($transaksi->pelanggan->no_hp) ?? '-')
            : '-';
        $text .= "Telp: {$pelangganPhone}\n";
        $text .= str_repeat('-', 25)."\n\n";

        // Detail Layanan menggunakan Model methods
        $text .= "*DETAIL LAYANAN*\n";
        foreach ($transaksi->transaksiLayanan as $index => $item) {
            if ($index > 0) {
                $text .= str_repeat('-', 25)."\n";
            }

            $text .= "*{$item->nama_layanan}*\n";

            // Per Kg - menggunakan method isPerKg() dari Helper
            if (TransaksiLayananHelper::isPerKg($item)) {
                $text .= 'Harga: Rp '.number_format((float) $item->harga_per_kg, 0, ',', '.')."/Kg\n";

                // Jenis Pakaian
                if (! empty($item->jenis_pakaian)) {
                    $jenisPakaian = is_string($item->jenis_pakaian)
                        ? json_decode($item->jenis_pakaian, true)
                        : $item->jenis_pakaian;

                    if (is_array($jenisPakaian)) {
                        foreach ($jenisPakaian as $jp) {
                            $text .= "  • {$jp['nama']}: {$jp['jumlah']}\n";
                        }
                    }
                }

                $text .= 'Berat: '.number_format((float) $item->berat_kg, 1, '.', '')." Kg\n";
            } elseif (TransaksiLayananHelper::isPerSatuan($item)) {
                // Per Satuan - menggunakan method isPerSatuan() dari Helper
                $satuan = $item->layanan?->satuan ?? 'pcs';
                $text .= 'Harga: Rp '.number_format((float) $item->harga_per_satuan, 0, ',', '.')."/{$satuan}\n";
                $text .= ((int) $item->jumlah_satuan).' '.ucfirst($satuan)."\n";
            }

            $text .= '*Rp '.number_format((float) $item->subtotal, 0, ',', '.')."*\n\n";
        }

        $text .= str_repeat('-', 25)."\n";

        // Ringkasan Pembayaran
        $text .= 'Subtotal: Rp '.number_format((float) $transaksi->subtotal, 0, ',', '.')."\n";
        $text .= 'Diskon: Rp '.number_format((float) $transaksi->diskon, 0, ',', '.')."\n";
        $text .= '*TOTAL: Rp '.number_format((float) $transaksi->total, 0, ',', '.')."*\n";
        $text .= str_repeat('-', 25)."\n\n";

        // Status & Pembayaran
        $text .= "Pembayaran: {$transaksi->metode_pembayaran}\n";
        $text .= "Status: *{$transaksi->status}*\n";

        // Catatan
        if (! empty($transaksi->catatan)) {
            $text .= "\nCatatan: {$transaksi->catatan}\n";
        }

        // Tambahkan link website
        $appUrl = config('app.url');
        if (! empty($appUrl)) {
            $text .= "\n🌐 Website: {$appUrl}\n";
        }

        $text .= "\n_Tetap Aktif, Tetap Bersih_\n";
        $text .= 'Terima kasih atas kepercayaan Anda!';

        return $text;
    }

    public function render()
    {
        return view('livewire.management.components.whats-app-button');
    }
}
