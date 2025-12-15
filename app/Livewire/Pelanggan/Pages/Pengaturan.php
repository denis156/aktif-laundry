<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Pages;

use App\Helper\AvatarPlaceholder;
use App\Helper\Database\PelangganHelper;
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
#[Layout('layouts.pelanggan.app')]
class Pengaturan extends Component
{
    use Toast;

    public bool $modalKonfirmasiLogout = false;

    public bool $modalUbahPassword = false;

    public string $nama = '';

    public string $currentAvatarUrl = '';

    public bool $notifikasi_aktif = false;

    public bool $lokasi_aktif = false;

    // Password fields
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount component and load pelanggan data
     */
    public function mount(): void
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        if ($pelanggan) {
            $this->nama = $pelanggan->nama;
            $this->currentAvatarUrl = $pelanggan->avatar_url ?? '';
            $this->notifikasi_aktif = $pelanggan->notifikasi_aktif ?? false;
            $this->lokasi_aktif = $pelanggan->lokasi_aktif ?? false;
        }
    }

    /**
     * Sync notification status dengan browser permission
     * Dipanggil dari Alpine.js store saat permission berubah
     */
    public function syncNotifikasiStatus(bool $status): void
    {
        try {
            $pelanggan = Auth::guard('pelanggan')->user();
            $pelanggan->update(['notifikasi_aktif' => $status]);
            $this->notifikasi_aktif = $status;
        } catch (Exception $e) {
            Log::error('Pengaturan: Failed to sync notification status', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync location status dengan browser permission
     * Dipanggil dari Alpine.js store saat permission berubah
     */
    public function syncLokasiStatus(bool $status): void
    {
        try {
            $pelanggan = Auth::guard('pelanggan')->user();
            $pelanggan->update(['lokasi_aktif' => $status]);
            $this->lokasi_aktif = $status;
        } catch (Exception $e) {
            Log::error('Pengaturan: Failed to sync location status', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'password' => 'required|min:'.PelangganHelper::PASSWORD_MIN_LENGTH.'|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi',
            'password.required' => 'Password baru wajib diisi',
            'password.min' => 'Password minimal '.PelangganHelper::PASSWORD_MIN_LENGTH.' karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        try {
            DB::transaction(function () {
                $pelanggan = Auth::guard('pelanggan')->user();

                if (! Hash::check($this->current_password, $pelanggan->password)) {
                    throw new Exception('Password lama tidak sesuai');
                }

                $pelanggan->update(['password' => Hash::make($this->password)]);

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
                Log::error('Pengaturan: Failed to update password', [
                    'error' => $e->getMessage(),
                ]);
                $this->error('Gagal memperbarui password. Silakan coba lagi.', position: 'toast-top');
            }
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

            $this->success('Berhasil keluar dari sistem', position: 'toast-top');
            $this->redirect(route('login.pelanggan'), navigate: true);
        } catch (Exception $e) {
            Log::error('Error during pelanggan logout from pengaturan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Terjadi kesalahan saat keluar. Silakan coba lagi.', position: 'toast-top');
        }
    }

    public function render(): mixed
    {
        $avatarUrl = AvatarPlaceholder::getAvatarOrPlaceholder($this->currentAvatarUrl, $this->nama, 256);

        return view('livewire.pelanggan.pages.pengaturan', [
            'avatarUrl' => $avatarUrl,
            'passwordMinLength' => PelangganHelper::PASSWORD_MIN_LENGTH,
        ]);
    }
}
