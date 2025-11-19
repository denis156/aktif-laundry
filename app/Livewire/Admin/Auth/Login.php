<?php

namespace App\Livewire\Admin\Auth;

use Exception;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Title('Masuk')]
class Login extends Component
{
    use Toast;

    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
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

                $this->success('Login berhasil! Selamat datang ' . Auth::user()->name, position: 'toast-bottom');

                // Redirect ke dashboard
                return $this->redirect(route('dashboard'), navigate: false);
            } else {
                $this->error('Email atau password salah!', position: 'toast-bottom');
            }

        } catch (Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    #[Layout('layouts.admin.guest')]
    public function render()
    {
        return view('livewire.admin.auth.login');
    }
}
