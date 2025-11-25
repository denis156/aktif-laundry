<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan\Auth;

use App\Helper\PhoneNumber;
use App\Models\Pelanggan;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Daftar Pelanggan')]
#[Layout('layouts.pelanggan.guest')]
class Register extends Component
{
    use Toast;

    public string $nama = '';

    public string $email = '';

    public string $no_hp = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $kode_referral = '';

    public bool $agree_terms = false;

    public function register(): void
    {
        // Validasi
        $this->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pelanggan,email',
            'no_hp' => 'required|string|max:15',
            'password' => 'required|min:8|confirmed',
            'agree_terms' => 'accepted',
        ], [
            'nama.required' => 'Nama wajib diisi',
            'nama.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'no_hp.required' => 'Nomor HP wajib diisi',
            'no_hp.max' => 'Nomor HP maksimal 15 karakter',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'agree_terms.accepted' => 'Anda harus menyetujui syarat dan ketentuan',
        ]);

        try {
            DB::transaction(function () {
                // Normalize phone number
                $normalizedPhone = PhoneNumber::normalize($this->no_hp);
                if (! $normalizedPhone) {
                    throw new Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
                }

                // Generate kode pelanggan
                $lastPelanggan = Pelanggan::withTrashed()->latest('id')->first();
                $nextNumber = $lastPelanggan ? ((int) substr($lastPelanggan->kode_pelanggan, 3)) + 1 : 1;
                $kodePelanggan = 'PLG'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

                // Cek kode referral jika diisi
                $referrerId = null;
                if (! empty($this->kode_referral)) {
                    $referrer = Pelanggan::whereHas('referral', function ($query) {
                        $query->where('kode_referral', $this->kode_referral)
                            ->where('status', 'Aktif');
                    })->first();

                    if ($referrer) {
                        $referrerId = $referrer->id;
                    }
                }

                // Create pelanggan
                $pelanggan = Pelanggan::create([
                    'kode_pelanggan' => $kodePelanggan,
                    'nama' => $this->nama,
                    'email' => $this->email,
                    'no_hp' => $normalizedPhone,
                    'password' => $this->password,
                    'tanggal_daftar' => now(),
                    'alamat' => 'Belum diisi',
                    'kode_referral_dipakai' => $this->kode_referral ?: null,
                    'direferensikan_oleh' => $referrerId,
                ]);

                // Trigger registered event for email verification
                event(new Registered($pelanggan));

                // Login otomatis
                Auth::guard('pelanggan')->login($pelanggan);

                Log::info('Pelanggan registered successfully', [
                    'pelanggan_id' => $pelanggan->id,
                    'email' => $pelanggan->email,
                    'kode_pelanggan' => $pelanggan->kode_pelanggan,
                ]);
            });

            $this->success('Pendaftaran berhasil! Selamat datang di '.config('app.name'), position: 'toast-bottom');

            // Redirect ke beranda atau email verification
            $pelanggan = Auth::guard('pelanggan')->user();
            if ($pelanggan && ! $pelanggan->hasVerifiedEmail()) {
                $this->info('Silakan verifikasi email Anda terlebih dahulu.', position: 'toast-bottom');
                $this->redirect(route('pelanggan.verification.notice'), navigate: true);
            } else {
                $this->redirect(route('beranda.pelanggan'), navigate: false);
            }

        } catch (Exception $e) {
            Log::error('Pelanggan registration error occurred', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (str_contains($e->getMessage(), 'Format nomor HP tidak valid')) {
                $this->error($e->getMessage(), position: 'toast-bottom');
            } else {
                $this->error('Terjadi kesalahan sistem. Silakan coba lagi.', position: 'toast-bottom');
            }
        }
    }

    public function render(): mixed
    {
        return view('livewire.pelanggan.auth.register');
    }
}
