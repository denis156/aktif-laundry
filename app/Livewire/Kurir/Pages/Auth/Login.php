<?php

declare(strict_types=1);

namespace App\Livewire\Kurir\Pages\Auth;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Masuk Kurir')]
#[Layout('layouts.kurir.guest')]
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
            // Attempt to login dengan guard kurir
            if (Auth::guard('kurir')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
                // Regenerate session to prevent session fixation
                request()->session()->regenerate();

                Log::info('Kurir login successful', [
                    'kurir_id' => Auth::guard('kurir')->id(),
                    'email' => $this->email,
                    'email_verified' => Auth::guard('kurir')->user()->hasVerifiedEmail(),
                ]);

                // Check if email needs verification
                if (! Auth::guard('kurir')->user()->hasVerifiedEmail()) {
                    Log::info('Kurir redirected to email verification', [
                        'kurir_id' => Auth::guard('kurir')->id(),
                    ]);

                    $this->info(
                        'Silakan verifikasi email Anda terlebih dahulu.',
                        position: 'toast-top'
                    );

                    $this->redirect(route('kurir.verification.notice'), navigate: true);

                    return;
                }

                $this->success('Login berhasil! Selamat datang '.Auth::guard('kurir')->user()->nama, position: 'toast-top');

                // Redirect ke dashboard kurir
                $this->redirect(route('beranda.kurir'), navigate: false);

                return;
            } else {
                Log::warning('Kurir login attempt failed', [
                    'email' => $this->email,
                ]);
                $this->error('Email atau password salah!', position: 'toast-top');
            }

        } catch (Exception $e) {
            Log::error('Kurir login error occurred', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Terjadi kesalahan sistem. Silakan coba lagi.', position: 'toast-top');
        }
    }

    public function render(): mixed
    {
        return view('livewire.kurir.pages.auth.login');
    }
}
