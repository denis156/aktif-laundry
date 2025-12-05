<?php

declare(strict_types=1);

namespace App\Livewire\Kurir;

use App\Helper\AvatarPlaceholder;
use App\Helper\Database\KurirHelper;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public bool $modalKonfirmasiLogout = false;

    public bool $modalUbahPassword = false;

    public string $nama = '';

    public string $currentAvatarUrl = '';

    // Password fields
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount component and load kurir data
     */
    public function mount(): void
    {
        $kurir = Auth::guard('kurir')->user();

        if ($kurir) {
            $this->nama = $kurir->nama;
            $this->currentAvatarUrl = $kurir->avatar_url ?? '';
        }
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'password' => 'required|min:'.KurirHelper::PASSWORD_MIN_LENGTH.'|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi',
            'password.required' => 'Password baru wajib diisi',
            'password.min' => 'Password minimal '.KurirHelper::PASSWORD_MIN_LENGTH.' karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        try {
            DB::transaction(function () {
                $kurir = Auth::guard('kurir')->user();

                if (! Hash::check($this->current_password, $kurir->password)) {
                    throw new Exception('Password lama tidak sesuai');
                }

                $kurir->update(['password' => Hash::make($this->password)]);

                $this->current_password = '';
                $this->password = '';
                $this->password_confirmation = '';
            });

            $this->modalUbahPassword = false;
            $this->success('Password berhasil diperbarui!', position: 'toast-top');
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'Password lama tidak sesuai')) {
                $this->error($errorMessage, position: 'toast-top');
            } else {
                Log::error('Kurir Pengaturan: Failed to update password', [
                    'error' => $e->getMessage(),
                ]);
                $this->error('Gagal memperbarui password. Silakan coba lagi.', position: 'toast-top');
            }
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

            $this->success('Berhasil keluar dari sistem', position: 'toast-top');
            $this->redirect(route('login.kurir'), navigate: true);
        } catch (Exception $e) {
            Log::error('Error during kurir logout from pengaturan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Terjadi kesalahan saat keluar. Silakan coba lagi.', position: 'toast-top');
        }
    }

    public function render(): mixed
    {
        $avatarUrl = AvatarPlaceholder::getAvatarOrPlaceholder($this->currentAvatarUrl, $this->nama, 256);

        return view('livewire.kurir.pengaturan', [
            'avatarUrl' => $avatarUrl,
            'passwordMinLength' => KurirHelper::PASSWORD_MIN_LENGTH,
        ]);
    }
}
