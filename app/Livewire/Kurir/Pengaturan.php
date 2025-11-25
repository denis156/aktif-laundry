<?php

declare(strict_types=1);

namespace App\Livewire\Kurir;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Pengaturan')]
#[Layout('layouts.kurir.app')]
class Pengaturan extends Component
{
    use Toast;

    public bool $modalUbahNama = false;

    public bool $modalKonfirmasiLogout = false;

    public ?string $nama = null;

    public ?string $noHp = null;

    public ?string $email = null;

    public ?string $avatarUrl = null;

    /**
     * Mount component and load kurir data
     */
    public function mount(): void
    {
        $kurir = Auth::guard('kurir')->user();

        if ($kurir) {
            $this->nama = $kurir->nama;
            $this->noHp = $kurir->no_hp;
            $this->email = $kurir->email;
            // TODO: Implement avatar URL from metadata when needed
            $this->avatarUrl = null;
        }
    }

    /**
     * Logout kurir dari sistem
     */
    public function logout(): void
    {
        try {
            Log::info('Kurir logout from pengaturan', [
                'kurir_id' => Auth::guard('kurir')->id(),
                'kurir_nama' => Auth::guard('kurir')->user()->nama,
            ]);

            Auth::guard('kurir')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            $this->success('Berhasil keluar dari sistem', position: 'toast-bottom');
            $this->redirect(route('login.kurir'), navigate: true);
        } catch (Exception $e) {
            Log::error('Error during kurir logout from pengaturan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Terjadi kesalahan saat keluar. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.kurir.pengaturan');
    }
}
