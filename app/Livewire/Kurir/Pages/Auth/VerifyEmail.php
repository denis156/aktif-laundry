<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Pages\Auth;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Verifikasi Email')]
#[Layout('layouts.kurir.guest')]
class VerifyEmail extends Component
{
    use Toast;

    public string $userEmail = '';

    /**
     * Mount component and get kurir email
     */
    public function mount(): void
    {
        // Redirect if already verified
        if (Auth::guard('kurir')->user()->hasVerifiedEmail()) {
            $this->redirect(route('beranda.kurir'), navigate: true);
        }

        $this->userEmail = Auth::guard('kurir')->user()->email;
    }

    /**
     * Resend verification email
     */
    public function resendVerification(): void
    {
        // Rate limiting key
        $key = 'email-verification-kurir:'.Auth::guard('kurir')->id();

        // Check if rate limited (1 per minute)
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('Kurir email verification rate limit exceeded', [
                'kurir_id' => Auth::guard('kurir')->id(),
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
            Auth::guard('kurir')->user()->sendEmailVerificationNotification();

            // Hit the rate limiter
            RateLimiter::hit($key, 60);

            Log::info('Kurir email verification sent', [
                'kurir_id' => Auth::guard('kurir')->id(),
                'email' => Auth::guard('kurir')->user()->email,
            ]);

            $this->success(
                'Email verifikasi telah dikirim. Silakan cek inbox atau spam folder Anda.',
                position: 'toast-top',
                timeout: 5000
            );
        } catch (Exception $e) {
            Log::error('Error sending kurir verification email', [
                'kurir_id' => Auth::guard('kurir')->id(),
                'email' => Auth::guard('kurir')->user()->email,
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
     * Logout kurir
     */
    public function logout(): void
    {
        try {
            Log::info('Kurir logout from verification page', [
                'kurir_id' => Auth::guard('kurir')->id(),
            ]);

            Auth::guard('kurir')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            $this->redirect(route('login.kurir'), navigate: true);
        } catch (Exception $e) {
            Log::error('Error during kurir logout', [
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
        return view('livewire.kurir.pages.auth.verify-email');
    }
}
