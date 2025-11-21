<?php

namespace App\Livewire\LandingPage;

use App\Helper\Database\PengaturanHelper;
use Livewire\Component;

class Pesan extends Component
{
    public string $whatsappNumber = '';

    public function mount()
    {
        // Ambil nomor WhatsApp dari database
        $this->whatsappNumber = PengaturanHelper::getValue('whatsapp', '82156912202');
    }

    /**
     * Generate WhatsApp URL dengan pesan custom
     */
    public function getWhatsappUrl(string $message): string
    {
        // Format nomor dengan kode negara jika belum ada
        $number = $this->whatsappNumber;
        if (! str_starts_with($number, '62')) {
            $number = '62'.ltrim($number, '0');
        }

        return 'https://wa.me/'.$number.'?text='.urlencode($message);
    }

    /**
     * Get WhatsApp URL untuk Paket Lengkap
     */
    public function getWhatsappPaketLengkap(): string
    {
        return $this->getWhatsappUrl('Mimin aktif laundry, saya mau pesan Paket Lengkap Rp 5.000/kg');
    }

    /**
     * Get WhatsApp URL untuk Item Satuan
     */
    public function getWhatsappItemSatuan(): string
    {
        return $this->getWhatsappUrl('Mimin aktif laundry, saya mau pesan Item Satuan (Seprai/Selimut/BedCover)');
    }

    /**
     * Get WhatsApp URL untuk CTA utama
     */
    public function getWhatsappCta(): string
    {
        return $this->getWhatsappUrl('Mimin aktif laundry, saya mau nyuci nih. Jemputin dong...');
    }

    public function render()
    {
        return view('livewire.landing-page.pesan');
    }
}
