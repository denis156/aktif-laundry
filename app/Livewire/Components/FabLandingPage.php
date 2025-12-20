<?php

declare(strict_types=1);

namespace App\Livewire\Components;

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
        // Check dulu apakah in-app browser
        $this->js(<<<'JS'
            const detection = window.browserHelper.detect();

            if (detection.isInApp) {
                // Android: Langsung redirect ke Chrome
                if (detection.platform === 'android') {
                    window.browserHelper.redirectToExternal();
                }
                // iOS: Redirect ke halaman pelanggan, nanti di mount akan tampilkan instruksi
                else {
                    $wire.proceedToPelanggan();
                }
            } else {
                // Normal browser: redirect dan tampilkan modal install PWA
                $wire.proceedToPelanggan();
            }
        JS);
    }

    public function proceedToPelanggan(): void
    {
        // Store flag in session to open install modal
        session(['open_install_modal' => true]);

        $this->redirect('/pelanggan', navigate: true);
    }

    public function openInstalledApp(): void
    {
        // Try to open PWA using Intent URL (Android)
        // If not Android or failed, fallback to normal redirect
        $this->js(<<<'JS'
            const detection = window.browserHelper.detect();
            const pelangganUrl = window.location.origin + '/pelanggan';

            // Android: Try Intent URL to open installed PWA
            if (detection.platform === 'android') {
                try {
                    // Generate Intent URL for pelanggan page
                    const intentUrl = window.browserHelper.generateChromeIntent(pelangganUrl);

                    console.log('[FAB] Opening installed PWA via Intent URL:', intentUrl);
                    window.location.href = intentUrl;
                } catch (error) {
                    console.error('[FAB] Failed to open PWA via Intent, using fallback:', error);
                    // Fallback: normal redirect
                    window.location.href = pelangganUrl;
                }
            } else {
                // iOS or other: normal redirect will open PWA if installed
                console.log('[FAB] Opening installed PWA via normal redirect');
                window.location.href = pelangganUrl;
            }
        JS);
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
        return view('livewire.components.fab-landing-page');
    }
}
