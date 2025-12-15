<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Pages\Auth;

use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Reset Password')]
#[Layout('layouts.pelanggan.guest')]
class ResetPassword extends Component
{
    use Toast;

    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount component with token and email from URL
     */
    public function mount(string $token, ?string $email = null): void
    {
        $this->token = $token;
        $this->email = $email ?? request()->query('email', '');
    }

    /**
     * Reset password
     */
    public function resetPassword(): void
    {
        // Validasi
        $this->validate([
            'email' => 'required|email|exists:pelanggan,email',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.exists' => 'Email tidak terdaftar dalam sistem',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi',
        ]);

        try {
            // Reset password menggunakan broker pelanggan
            $status = Password::broker('pelanggan')->reset(
                [
                    'email' => $this->email,
                    'password' => $this->password,
                    'password_confirmation' => $this->password_confirmation,
                    'token' => $this->token,
                ],
                function ($pelanggan, $password) {
                    $pelanggan->forceFill([
                        'password' => Hash::make($password),
                    ])->save();

                    event(new PasswordReset($pelanggan));

                    Log::info('Pelanggan password reset successful', [
                        'pelanggan_id' => $pelanggan->id,
                        'email' => $pelanggan->email,
                    ]);
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                $this->success(
                    'Password berhasil direset. Silakan login dengan password baru Anda.',
                    position: 'toast-top',
                    timeout: 5000,
                    redirectTo: route('login.pelanggan')
                );
            } else {
                Log::warning('Failed to reset pelanggan password', [
                    'email' => $this->email,
                    'status' => $status,
                ]);

                $this->error(
                    'Link reset password tidak valid atau sudah kadaluarsa. Silakan minta link baru.',
                    position: 'toast-top'
                );
            }
        } catch (Exception $e) {
            Log::error('Error resetting pelanggan password', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error(
                'Terjadi kesalahan sistem. Silakan coba lagi.',
                position: 'toast-top'
            );
        }
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.pages.auth.reset-password');
    }
}
