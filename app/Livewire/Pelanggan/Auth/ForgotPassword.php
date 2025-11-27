<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Auth;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Lupa Password')]
#[Layout('layouts.pelanggan.guest')]
class ForgotPassword extends Component
{
    use Toast;

    public string $email = '';

    /**
     * Send password reset link to email
     */
    public function sendResetLink(): void
    {
        // Validasi
        $this->validate([
            'email' => 'required|email|exists:pelanggan,email',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.exists' => 'Email tidak terdaftar dalam sistem',
        ]);

        try {
            // Kirim reset link menggunakan broker pelanggan
            $status = Password::broker('pelanggan')->sendResetLink(
                ['email' => $this->email]
            );

            if ($status === Password::RESET_LINK_SENT) {
                Log::info('Pelanggan password reset link sent', [
                    'email' => $this->email,
                ]);

                $this->success(
                    'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau spam folder.',
                    position: 'toast-top',
                    timeout: 5000
                );

                // Reset form
                $this->reset('email');
            } else {
                Log::warning('Failed to send pelanggan password reset link', [
                    'email' => $this->email,
                    'status' => $status,
                ]);

                $this->error(
                    'Gagal mengirim link reset password. Silakan coba lagi.',
                    position: 'toast-top'
                );
            }
        } catch (Exception $e) {
            Log::error('Error sending pelanggan password reset link', [
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
        return view('livewire.pelanggan.auth.forgot-password');
    }
}
