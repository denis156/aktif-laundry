<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Auth;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Verifikasi Email')]
#[Layout('layouts.management.guest')]
class VerifyEmail extends Component
{
    use Toast;

    public string $userEmail = '';

    /**
     * Mount component and get user email
     */
    public function mount(): void
    {
        // Redirect if already verified
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirect(route('dashboard'), navigate: true);
        }

        $this->userEmail = Auth::user()->email;
    }

    /**
     * Resend verification email
     */
    public function resendVerification(): void
    {
        // Rate limiting key
        $key = 'email-verification:'.Auth::id();

        // Check if rate limited (1 per minute)
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('Email verification rate limit exceeded', [
                'user_id' => Auth::id(),
                'seconds_remaining' => $seconds,
            ]);

            $this->error(
                "Tunggu {$seconds} detik sebelum mengirim ulang email verifikasi.",
                position: 'toast-bottom'
            );

            return;
        }

        try {
            // Send verification notification
            Auth::user()->sendEmailVerificationNotification();

            // Hit the rate limiter
            RateLimiter::hit($key, 60);

            Log::info('Email verification sent', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
            ]);

            $this->success(
                'Email verifikasi telah dikirim. Silakan cek inbox atau spam folder Anda.',
                position: 'toast-bottom',
                timeout: 5000
            );
        } catch (Exception $e) {
            Log::error('Error sending verification email', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error(
                'Terjadi kesalahan sistem. Silakan coba lagi.',
                position: 'toast-bottom'
            );
        }
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        try {
            Log::info('User logout from verification page', [
                'user_id' => Auth::id(),
            ]);

            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            $this->redirect(route('login'), navigate: true);
        } catch (Exception $e) {
            Log::error('Error during logout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error(
                'Terjadi kesalahan sistem. Silakan coba lagi.',
                position: 'toast-bottom'
            );
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.pages.auth.verify-email');
    }
}
