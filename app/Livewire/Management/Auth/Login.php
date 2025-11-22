<?php

declare(strict_types=1);

namespace App\Livewire\Management\Auth;

use Exception;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
                ]);

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
        return view('livewire.management.auth.login');
    }
}
