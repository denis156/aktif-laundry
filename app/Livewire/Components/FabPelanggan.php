<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Livewire\Component;
use Mary\Traits\Toast;

class FabPelanggan extends Component
{
    use Toast;

    public bool $showInstallModal = false;

    public bool $showManualInstallModal = false;

    public function mount(): void
    {
        // Check if redirected from landing page with flag to open install modal
        if (session()->has('open_install_modal') && session('open_install_modal') === true) {
            $this->showInstallModal = true;

            // Clear the session flag after using it
            session()->forget('open_install_modal');
        }
    }

    public function openInstallModal(): void
    {
        $this->showInstallModal = true;
    }

    public function installPWA(): void
    {
        $this->js(<<<'JS'
            (async () => {
                // Check if PWA install is available
                if (typeof window.pwaInstall === 'undefined' || typeof window.pwaInstall.prompt !== 'function') {
                    console.error('[PWA] window.pwaInstall not ready');
                    $wire.handleInstallUnavailable();
                    return;
                }

                // Trigger PWA installation
                const outcome = await window.pwaInstall.prompt();

                if (outcome === 'accepted') {
                    $wire.handleInstallSuccess();
                } else if (outcome === 'dismissed') {
                    $wire.handleInstallDismissed();
                } else {
                    $wire.handleInstallUnavailable();
                }
            })();
        JS);
    }

    public function handleInstallSuccess(): void
    {
        $this->showInstallModal = false;

        $this->success(
            title: 'Aplikasi Berhasil Diinstall!',
            description: 'Cek home screen Anda untuk membuka aplikasi.',
            position: 'toast-top toast-end',
            timeout: 6000
        );

        $this->dispatch('pwa-installed-pelanggan');
    }

    public function handleInstallDismissed(): void
    {
        $this->showInstallModal = false;

        $this->info(
            title: 'Install Dibatalkan',
            description: 'Anda bisa install aplikasi kapan saja.',
            position: 'toast-top toast-end',
            timeout: 6000
        );
    }

    public function handleInstallUnavailable(): void
    {
        $this->showInstallModal = false;
        $this->showManualInstallModal = true;
    }

    public function render()
    {
        return view('livewire.components.fab-pelanggan');
    }
}
