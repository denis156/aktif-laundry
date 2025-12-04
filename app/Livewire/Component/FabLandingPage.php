<?php

declare(strict_types=1);

namespace App\Livewire\Component;

use App\Helper\Database\PengaturanHelper;
use Livewire\Component;

class FabLandingPage extends Component
{
    public bool $isPwaInstalled = false;

    public string $whatsappNumber = '';

    public function mount(): void
    {
        // Ambil nomor WhatsApp dari database
        $this->whatsappNumber = PengaturanHelper::getValue('whatsapp', '82156912202');
    }

    public function redirectToPelanggan(): void
    {
        // Store flag in session to open install modal
        session(['open_install_modal' => true]);

        $this->redirect('/pelanggan', navigate: true);
    }

    public function openInstalledApp(): void
    {
        // Redirect to pelanggan WITHOUT opening install modal
        // This is for users who already have PWA installed
        $this->redirect('/pelanggan', navigate: true);
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
     * Get WhatsApp URL untuk Chat Admin dari FAB
     */
    public function getWhatsappCta(): string
    {
        return $this->getWhatsappUrl('Mimin aktif laundry, saya mau nyuci nih. Jemputin dong...');
    }

    public function render()
    {
        return view('livewire.component.fab-landing-page');
    }
}
