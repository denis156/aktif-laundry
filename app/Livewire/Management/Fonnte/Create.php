<?php

declare(strict_types=1);

namespace App\Livewire\Management\Fonnte;

use App\Helper\FonnteHelper;
use App\Helper\PhoneNumber;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Tambah Device WhatsApp')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;

    public string $deviceName = '';

    public string $devicePhone = '';

    protected FonnteHelper $fonnte;

    public function boot(FonnteHelper $fonnte): void
    {
        $this->fonnte = $fonnte;
    }

    public function save(): void
    {
        $this->validate([
            'deviceName' => 'required|string|max:255',
            'devicePhone' => 'required|string|max:20',
        ]);

        // Normalize phone number using PhoneNumber helper
        $normalizedPhone = PhoneNumber::normalize($this->devicePhone);

        if (! $normalizedPhone || ! PhoneNumber::isValid($this->devicePhone)) {
            $this->error('Format nomor WhatsApp tidak valid. Gunakan format: 08xx, 628xx, atau +628xx', position: 'toast-bottom');

            return;
        }

        // Format phone number for Fonnte API (with 62 prefix)
        $phoneForApi = '62'.$normalizedPhone;

        try {
            $response = $this->fonnte->addDevice($this->deviceName, $phoneForApi);

            if ($response['status']) {
                $this->success('Device berhasil ditambahkan!', position: 'toast-bottom');

                // Redirect to edit page with device token
                if (isset($response['data']['token'])) {
                    $this->redirect(route('fonnte.edit', $response['data']['token']), navigate: true);
                } else {
                    // Fallback to index if no token
                    $this->redirect(route('fonnte.index'), navigate: true);
                }
            } else {
                $this->error($response['error'] ?? 'Gagal menambahkan device', position: 'toast-bottom');
            }
        } catch (Exception $e) {
            Log::error('Failed to add device', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal menambahkan device. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.management.fonnte.create');
    }
}
