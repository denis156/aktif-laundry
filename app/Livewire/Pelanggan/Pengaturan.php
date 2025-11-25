<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Pengaturan')]
#[Layout('layouts.pelanggan.app')]
class Pengaturan extends Component
{
    use Toast;

    public bool $modalKonfirmasiLogout = false;

    public ?string $nama = null;

    public ?string $noHp = null;

    public ?string $email = null;

    public ?string $avatarUrl = null;

    /**
     * Mount component and load pelanggan data
     */
    public function mount(): void
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        if ($pelanggan) {
            $this->nama = $pelanggan->nama;
            $this->noHp = $pelanggan->no_hp;
            $this->email = $pelanggan->email;
            // TODO: Implement avatar URL from metadata when needed
            $this->avatarUrl = null;
        }
    }

    /**
     * Logout pelanggan dari sistem
     */
    public function logout(): void
    {
        try {
            Log::info('Pelanggan logout from pengaturan', [
                'pelanggan_id' => Auth::guard('pelanggan')->id(),
                'pelanggan_nama' => Auth::guard('pelanggan')->user()->nama,
            ]);

            Auth::guard('pelanggan')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            $this->success('Berhasil keluar dari sistem', position: 'toast-bottom');
            $this->redirect(route('login.pelanggan'), navigate: true);
        } catch (Exception $e) {
            Log::error('Error during pelanggan logout from pengaturan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Terjadi kesalahan saat keluar. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.pengaturan');
    }
}
