<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Fonnte;

use App\Services\FonnteService;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Kelola Device WhatsApp')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;

    public string $token = '';

    public ?array $device = null;

    public string $qrCode = '';

    public bool $showQR = false;

    // Edit form
    public string $name = '';

    public string $phoneNumber = '';

    // Modals
    public bool $disconnectModal = false;

    protected FonnteService $fonnteService;

    public function boot(FonnteService $fonnteService): void
    {
        $this->fonnteService = $fonnteService;
    }

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->loadDevice();
    }

    public function loadDevice(): void
    {
        try {
            // Clear cache untuk mendapatkan data device terbaru
            $this->fonnteService->clearDeviceProfileCache($this->token);

            $response = $this->fonnteService->getDeviceProfile($this->token);

            if ($response['status']) {
                $this->device = $response['data'];
                // Set form values dari device data
                $this->name = $this->device['name'] ?? '';
                $this->phoneNumber = $this->device['device'] ?? '';
            } else {
                $this->error($response['error'] ?? 'Gagal memuat detail device', position: 'toast-bottom');
                $this->redirect(route('fonnte.index'), navigate: true);
            }
        } catch (Exception $e) {
            Log::error('Failed to load device detail', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal memuat detail device. Silakan coba lagi.', position: 'toast-bottom');
            $this->redirect(route('fonnte.index'), navigate: true);
        }
    }

    public function saveDevice(): void
    {
        $this->validate([
            'name' => 'required|string|min:2|max:30',
        ]);

        if (! $this->device) {
            return;
        }

        try {
            $response = $this->fonnteService->updateDevice($this->token, $this->name);

            if ($response['status']) {
                $this->success('Device berhasil diupdate!', position: 'toast-bottom');
                $this->loadDevice();
            } else {
                $this->error($response['error'] ?? 'Gagal update device', position: 'toast-bottom');
            }
        } catch (Exception $e) {
            Log::error('Failed to update device', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal update device. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function requestQR(): void
    {
        if (! $this->device) {
            $this->error('Device tidak valid', position: 'toast-bottom');

            return;
        }

        try {
            $response = $this->fonnteService->requestQRActivation(
                $this->device['device'],
                $this->token
            );

            if ($response['status'] && isset($response['data']['qrcode'])) {
                $this->qrCode = $response['data']['qrcode'];
                $this->showQR = true;
                $this->success('QR Code berhasil dimuat!', position: 'toast-bottom');
            } else {
                $this->error($response['error'] ?? 'Gagal mendapatkan QR Code', position: 'toast-bottom');
            }
        } catch (Exception $e) {
            Log::error('Failed to request QR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal mendapatkan QR Code. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function confirmDisconnect(): void
    {
        $this->disconnectModal = true;
    }

    public function disconnectDevice(): void
    {
        if (! $this->device) {
            return;
        }

        try {
            $response = $this->fonnteService->disconnectDevice($this->token);

            if ($response['status']) {
                $this->success('Device berhasil didisconnect!', position: 'toast-bottom');
                $this->disconnectModal = false;
                $this->loadDevice();
            } else {
                $this->error($response['error'] ?? 'Gagal disconnect device', position: 'toast-bottom');
            }
        } catch (Exception $e) {
            Log::error('Failed to disconnect device', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal disconnect device. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.management.pages.fonnte.edit');
    }
}
