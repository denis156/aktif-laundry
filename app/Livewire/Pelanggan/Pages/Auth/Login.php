<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Pages\Auth;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Masuk Pelanggan')]
#[Layout('layouts.pelanggan.guest')]
class Login extends Component
{
    use Toast;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public bool $showInAppBrowserModal = false;

    public function mount(): void
    {
        // Check if in-app browser on page load
        $this->js(<<<'JS'
            const detection = window.browserHelper.detect();

            if (detection.isInApp) {
                // Android: Langsung redirect ke Chrome
                if (detection.platform === 'android') {
                    console.log('[Login] In-app browser detected on Android, redirecting to Chrome...');
                    window.browserHelper.redirectToExternal();
                }
                // iOS: Tampilkan modal instruksi
                else if (detection.platform === 'ios') {
                    console.log('[Login] In-app browser detected on iOS, showing instructions...');
                    $wire.showInAppBrowserModal = true;
                }
            }
        JS);
    }

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
            // Attempt to login dengan guard pelanggan
            if (Auth::guard('pelanggan')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
                // Regenerate session to prevent session fixation
                request()->session()->regenerate();

                Log::info('Pelanggan login successful', [
                    'pelanggan_id' => Auth::guard('pelanggan')->id(),
                    'email' => $this->email,
                    'email_verified' => Auth::guard('pelanggan')->user()->hasVerifiedEmail(),
                ]);

                // Check if email needs verification
                if (! Auth::guard('pelanggan')->user()->hasVerifiedEmail()) {
                    Log::info('Pelanggan redirected to email verification', [
                        'pelanggan_id' => Auth::guard('pelanggan')->id(),
                    ]);

                    $this->info(
                        'Silakan verifikasi email Anda terlebih dahulu.',
                        position: 'toast-top'
                    );

                    $this->redirect(route('pelanggan.verification.notice'), navigate: true);

                    return;
                }

                $this->success('Login berhasil! Selamat datang '.Auth::guard('pelanggan')->user()->nama, position: 'toast-top');

                // Redirect ke dashboard pelanggan
                $this->redirect(route('beranda.pelanggan'), navigate: false);

                return;
            } else {
                Log::warning('Pelanggan login attempt failed', [
                    'email' => $this->email,
                ]);
                $this->error('Email atau password salah!', position: 'toast-top');
            }

        } catch (Exception $e) {
            Log::error('Pelanggan login error occurred', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Terjadi kesalahan sistem. Silakan coba lagi.', position: 'toast-top');
        }
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.pages.auth.login');
    }
}
