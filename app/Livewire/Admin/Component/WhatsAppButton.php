<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Component;

use Livewire\Component;
use App\Models\Transaksi;
use App\Helper\Database\PengaturanHelper;
use App\Helper\Database\TransaksiLayananHelper;
use App\Helper\PhoneNumber;

class WhatsAppButton extends Component
{
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

        if (!$transaksi) {
            $this->dispatch('notify', type: 'error', message: 'Transaksi tidak ditemukan');

            return;
        }

        // Generate WhatsApp message text
        $message = $this->generateReceiptText($transaksi);

        // Normalize phone number menggunakan PhoneNumber Helper
        $normalizedPhone = PhoneNumber::normalize($this->phoneNumber);

        if (!$normalizedPhone) {
            $this->dispatch('notify', type: 'error', message: 'Nomor telepon tidak valid');

            return;
        }

        // Generate WhatsApp URL menggunakan PhoneNumber Helper
        $whatsappUrl = PhoneNumber::getWhatsAppUrl($normalizedPhone, $message);

        if (!$whatsappUrl) {
            $this->dispatch('notify', type: 'error', message: 'Gagal membuat URL WhatsApp');

            return;
        }

        // Dispatch event to open URL in new tab
        $this->dispatch('open-whatsapp', url: $whatsappUrl);
    }

    private function generateReceiptText(Transaksi $transaksi): string
    {
        $namaToko = PengaturanHelper::getValue('nama_toko', 'AKTIF LAUNDRY');
        $whatsapp = PengaturanHelper::getValue('whatsapp', '');
        $email = PengaturanHelper::getValue('email', '');
        $jamBuka = PengaturanHelper::getValue('jam_buka', '08:00');
        $jamTutup = PengaturanHelper::getValue('jam_tutup', '21:00');

        $text = '*'.strtoupper($namaToko)."*\n";
        if (!empty($whatsapp)) {
            $text .= "WA: {$whatsapp}\n";
        }
        if (!empty($email)) {
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
                $text .= 'Harga: Rp '.number_format($item->harga_per_kg, 0, ',', '.')."/Kg\n";

                // Jenis Pakaian
                if (!empty($item->jenis_pakaian)) {
                    $jenisPakaian = is_string($item->jenis_pakaian)
                        ? json_decode($item->jenis_pakaian, true)
                        : $item->jenis_pakaian;

                    if (is_array($jenisPakaian)) {
                        foreach ($jenisPakaian as $jp) {
                            $text .= "  • {$jp['nama']}: {$jp['jumlah']}\n";
                        }
                    }
                }

                $text .= 'Berat: '.number_format($item->berat_kg, 1, '.', '')." Kg\n";
            } elseif (TransaksiLayananHelper::isPerSatuan($item)) {
                // Per Satuan - menggunakan method isPerSatuan() dari Helper
                $satuan = $item->layanan?->satuan ?? 'pcs';
                $text .= 'Harga: Rp '.number_format($item->harga_per_satuan, 0, ',', '.')."/{$satuan}\n";
                $text .= $item->jumlah_satuan.' '.ucfirst($satuan)."\n";
            }

            $text .= '*Rp '.number_format($item->subtotal, 0, ',', '.')."*\n\n";
        }

        $text .= str_repeat('-', 25)."\n";

        // Ringkasan Pembayaran
        $text .= 'Subtotal: Rp '.number_format($transaksi->subtotal, 0, ',', '.')."\n";
        $text .= 'Diskon: Rp '.number_format($transaksi->diskon, 0, ',', '.')."\n";
        $text .= '*TOTAL: Rp '.number_format($transaksi->total, 0, ',', '.')."*\n";
        $text .= str_repeat('-', 25)."\n\n";

        // Status & Pembayaran
        $text .= "Pembayaran: {$transaksi->metode_pembayaran}\n";
        $text .= "Status: *{$transaksi->status}*\n";

        if ($transaksi->tanggal_selesai) {
            $text .= 'Selesai: '.$transaksi->tanggal_selesai->format('d/m/Y H:i')."\n";
        }

        // Catatan
        if (!empty($transaksi->catatan)) {
            $text .= "\nCatatan: {$transaksi->catatan}\n";
        }

        $text .= "\n_Tetap Aktif, Tetap Bersih_\n";
        $text .= 'Terima kasih atas kepercayaan Anda!';

        return $text;
    }

    public function render()
    {
        return view('livewire.admin.component.whats-app-button');
    }
}
