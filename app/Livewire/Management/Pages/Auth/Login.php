<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Auth;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Masuk')]
#[Layout('layouts.management.guest')]
class Login extends Component
{
    use Toast;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        // Validasi
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
        ]);

        try {
            // Attempt to login
            if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
                // Regenerate session to prevent session fixation
                request()->session()->regenerate();

                Log::info('User login successful', [
                    'user_id' => Auth::id(),
                    'email' => $this->email,
                    'email_verified' => Auth::user()->hasVerifiedEmail(),
                ]);

                // Check if email needs verification
                if (! Auth::user()->hasVerifiedEmail()) {
                    Log::info('User redirected to email verification', [
                        'user_id' => Auth::id(),
                    ]);

                    $this->info(
                        'Silakan verifikasi email Anda terlebih dahulu.',
                        position: 'toast-bottom'
                    );

                    $this->redirect(route('verification.notice'), navigate: true);

                    return;
                }

                $this->success('Login berhasil! Selamat datang '.Auth::user()->name, position: 'toast-bottom');

                // Redirect ke dashboard
                $this->redirect(route('dashboard'), navigate: false);

                return;
            } else {
                Log::warning('Login attempt failed', [
                    'email' => $this->email,
                ]);
                $this->error('Email atau password salah!', position: 'toast-bottom');
            }

        } catch (Exception $e) {
            Log::error('Login error occurred', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Terjadi kesalahan sistem. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.pages.auth.login');
    }
}
