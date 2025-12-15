<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Pages\Auth;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Verifikasi Email')]
#[Layout('layouts.pelanggan.guest')]
class VerifyEmail extends Component
{
    use Toast;

    public string $userEmail = '';

    /**
     * Mount component and get pelanggan email
     */
    public function mount(): void
    {
        // Redirect if already verified
        if (Auth::guard('pelanggan')->user()->hasVerifiedEmail()) {
            $this->redirect(route('beranda.pelanggan'), navigate: true);
        }

        $this->userEmail = Auth::guard('pelanggan')->user()->email;
    }

    /**
     * Resend verification email
     */
    public function resendVerification(): void
    {
        // Rate limiting key
        $key = 'email-verification-pelanggan:'.Auth::guard('pelanggan')->id();

        // Check if rate limited (1 per minute)
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('Pelanggan email verification rate limit exceeded', [
                'pelanggan_id' => Auth::guard('pelanggan')->id(),
                'seconds_remaining' => $seconds,
            ]);

            $this->error(
                "Tunggu {$seconds} detik sebelum mengirim ulang email verifikasi.",
                position: 'toast-top'
            );

            return;
        }

        try {
            // Send verification notification
            Auth::guard('pelanggan')->user()->sendEmailVerificationNotification();

            // Hit the rate limiter
            RateLimiter::hit($key, 60);

            Log::info('Pelanggan email verification sent', [
                'pelanggan_id' => Auth::guard('pelanggan')->id(),
                'email' => Auth::guard('pelanggan')->user()->email,
            ]);

            $this->success(
                'Email verifikasi telah dikirim. Silakan cek inbox atau spam folder Anda.',
                position: 'toast-top',
                timeout: 5000
            );
        } catch (Exception $e) {
            Log::error('Error sending pelanggan verification email', [
                'pelanggan_id' => Auth::guard('pelanggan')->id(),
                'email' => Auth::guard('pelanggan')->user()->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error(
                'Terjadi kesalahan sistem. Silakan coba lagi.',
                position: 'toast-top'
            );
        }
    }

    /**
     * Logout pelanggan
     */
    public function logout(): void
    {
        try {
            Log::info('Pelanggan logout from verification page', [
                'pelanggan_id' => Auth::guard('pelanggan')->id(),
            ]);

            Auth::guard('pelanggan')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            $this->redirect(route('login.pelanggan'), navigate: true);
        } catch (Exception $e) {
            Log::error('Error during pelanggan logout', [
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
        return view('livewire.pelanggan.pages.auth.verify-email');
    }
}
