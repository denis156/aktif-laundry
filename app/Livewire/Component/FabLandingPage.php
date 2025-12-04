<?php

declare(strict_types=1);

namespace App\Livewire\Component;

use Livewire\Component;

class FabLandingPage extends Component
{
    public bool $isPwaInstalled = false;

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

    public function render()
    {
        return view('livewire.component.fab-landing-page');
    }
}
