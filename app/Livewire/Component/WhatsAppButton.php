<?php

namespace App\Livewire\Component;

use Livewire\Component;
use App\Models\Transaksi;
use App\Models\Setting;

class WhatsAppButton extends Component
{
    public $transaksiId;
    public $phoneNumber;
    public $size = 'sm'; // default sm, bisa di-override dengan md, lg
    public $btnClass = 'btn-success'; // default success, bisa btn-primary, btn-soft-success, dll

    public function mount($transaksiId, $size = 'sm', $btnClass = 'btn-success')
    {
        $this->transaksiId = $transaksiId;
        $this->size = $size;
        $this->btnClass = $btnClass;

        // Get phone number from transaksi pelanggan
        $transaksi = Transaksi::with('pelanggan')->find($transaksiId);
        $this->phoneNumber = $transaksi->pelanggan->no_hp ?? '';
    }

    public function sendWhatsApp()
    {
        $transaksi = Transaksi::with(['pelanggan', 'transaksiLayanan.layanan'])->find($this->transaksiId);

        if (!$transaksi) {
            $this->dispatch('notify', type: 'error', message: 'Transaksi tidak ditemukan');
            return;
        }

        // Generate WhatsApp message text
        $message = $this->generateReceiptText($transaksi);

        // Clean phone number (remove +, spaces, dashes)
        $phone = preg_replace('/[^0-9]/', '', $this->phoneNumber);

        // Add country code if not exists
        if (!str_starts_with($phone, '62')) {
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            } else {
                $phone = '62' . $phone;
            }
        }

        // Encode message for URL
        $encodedMessage = urlencode($message);

        // Create WhatsApp URL
        $whatsappUrl = "https://wa.me/{$phone}?text={$encodedMessage}";

        // Dispatch event to open URL in new tab
        $this->dispatch('open-whatsapp', url: $whatsappUrl);
    }

    private function generateReceiptText($transaksi)
    {
        $namaToko = Setting::get('nama_toko', 'AKTIF LAUNDRY');
        $whatsapp = Setting::get('whatsapp', '');
        $email = Setting::get('email', '');
        $jamBuka = Setting::get('jam_buka', '08:00');
        $jamTutup = Setting::get('jam_tutup', '21:00');

        $text = "*" . strtoupper($namaToko) . "*\n";
        if (!empty($whatsapp)) {
            $text .= "WA: {$whatsapp}\n";
        }
        if (!empty($email)) {
            $text .= "Email: {$email}\n";
        }
        $text .= "Buka: {$jamBuka} - Tutup: {$jamTutup}\n";
        $text .= str_repeat("-", 25) . "\n\n";

        // Info Transaksi
        $text .= "*STRUK TRANSAKSI*\n";
        $text .= "No: *{$transaksi->kode_transaksi}*\n";
        $text .= "Tanggal: " . $transaksi->tanggal_masuk->format('d/m/Y H:i') . "\n\n";

        // Info Pelanggan
        $text .= "*PELANGGAN*\n";
        $text .= "Nama: {$transaksi->nama_pelanggan}\n";
        $pelangganPhone = $transaksi->pelanggan->no_hp ?? '-';
        $text .= "Telp: {$pelangganPhone}\n";
        $text .= str_repeat("-", 25) . "\n\n";

        // Detail Layanan
        $text .= "*DETAIL LAYANAN*\n";
        foreach ($transaksi->transaksiLayanan as $index => $item) {
            if ($index > 0) {
                $text .= str_repeat("-", 25) . "\n";
            }

            $text .= "*{$item->nama_layanan}*\n";

            // Per Kg
            if (!empty($item->berat_kg)) {
                $text .= "Harga: Rp " . number_format($item->harga_per_kg, 0, ',', '.') . "/Kg\n";

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

                $text .= "Berat: " . number_format($item->berat_kg, 1, '.', '') . " Kg\n";
            }
            // Per Satuan
            else if (!empty($item->jumlah_satuan)) {
                $satuan = $item->layanan->satuan ?? 'pcs';
                $text .= "Harga: Rp " . number_format($item->harga_per_satuan, 0, ',', '.') . "/{$satuan}\n";
                $text .= "{$item->jumlah_satuan} " . ucfirst($satuan) . "\n";
            }

            $text .= "*Rp " . number_format($item->subtotal, 0, ',', '.') . "*\n\n";
        }

        $text .= str_repeat("-", 25) . "\n";

        // Ringkasan Pembayaran
        $text .= "Subtotal: Rp " . number_format($transaksi->subtotal, 0, ',', '.') . "\n";
        $text .= "Diskon: Rp " . number_format($transaksi->diskon, 0, ',', '.') . "\n";
        $text .= "*TOTAL: Rp " . number_format($transaksi->total, 0, ',', '.') . "*\n";
        $text .= str_repeat("-", 25) . "\n\n";

        // Status & Pembayaran
        $text .= "Pembayaran: {$transaksi->metode_pembayaran}\n";
        $text .= "Status: *{$transaksi->status}*\n";

        if ($transaksi->tanggal_selesai) {
            $text .= "Selesai: " . $transaksi->tanggal_selesai->format('d/m/Y H:i') . "\n";
        }

        // Catatan
        if (!empty($transaksi->catatan)) {
            $text .= "\nCatatan: {$transaksi->catatan}\n";
        }

        $text .= "\n_Tetap Aktif, Tetap Bersih_\n";
        $text .= "Terima kasih atas kepercayaan Anda!";

        return $text;
    }

    public function render()
    {
        return view('livewire.component.whats-app-button');
    }
}
