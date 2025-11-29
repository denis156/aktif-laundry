<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helper\Database\PelangganHelper;
use App\Models\Pelanggan;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class GoogleOAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Google OAuth routes
        Route::middleware(['web'])->prefix('pelanggan/auth')->group(function () {
            Route::get('google', function () {
                return Socialite::driver('google')->redirect();
            })->name('auth.google')->middleware('guest:pelanggan');

            Route::get('google/callback', function () {
                return GoogleOAuthServiceProvider::handleGoogleCallback();
            })->name('auth.google.callback');
        });
    }

    /**
     * Handle callback dari Google
     * Dipanggil dari route setelah user authorize di Google
     */
    public static function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Cari pelanggan berdasarkan email
            $pelanggan = Pelanggan::where('email', $googleUser->getEmail())->first();

            if ($pelanggan) {
                // Pelanggan sudah ada, update nama dan avatar jika kosong
                if (empty($pelanggan->nama)) {
                    $pelanggan->nama = $googleUser->getName();
                }

                if (empty($pelanggan->avatar_url)) {
                    $pelanggan->avatar_url = $googleUser->getAvatar();
                }

                // Auto-verify email jika belum terverifikasi
                if (! $pelanggan->hasVerifiedEmail()) {
                    $pelanggan->markEmailAsVerified();
                }

                $pelanggan->save();

                Log::info('Existing pelanggan logged in via Google OAuth', [
                    'pelanggan_id' => $pelanggan->id,
                    'google_id' => $googleUser->getId(),
                    'email' => $googleUser->getEmail(),
                ]);
            } else {
                // Pelanggan belum ada, buat account baru otomatis
                $avatarUrl = $googleUser->getAvatar();

                $pelanggan = PelangganHelper::createPelanggan([
                    'nama' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => uniqid(), // Random password karena login via Google
                    'avatar_url' => $avatarUrl,
                ]);

                // Auto-verify email untuk akun baru dari Google OAuth
                $pelanggan->markEmailAsVerified();

                Log::info('New pelanggan registered via Google OAuth', [
                    'pelanggan_id' => $pelanggan->id,
                    'google_id' => $googleUser->getId(),
                    'email' => $googleUser->getEmail(),
                    'name' => $googleUser->getName(),
                    'avatar_from_google' => $avatarUrl,
                    'avatar_saved_to_db' => $pelanggan->avatar_url,
                ]);
            }

            // Login pelanggan dengan remember
            Auth::guard('pelanggan')->login($pelanggan, true);

            // Regenerate session untuk keamanan
            request()->session()->regenerate();

            // Debug logging
            Log::info('GoogleOAuth: After login attempt', [
                'is_authenticated' => Auth::guard('pelanggan')->check(),
                'pelanggan_id' => Auth::guard('pelanggan')->id(),
                'session_id' => session()->getId(),
            ]);

            // Cek apakah data pelanggan lengkap (harus punya alamat lengkap)
            $isProfileIncomplete = empty($pelanggan->alamat) || empty($pelanggan->detail_alamat);

            // Get pelanggan name
            $pelangganName = $pelanggan->nama ?? 'Pelanggan';

            if ($isProfileIncomplete) {
                // Simpan data modal ke session untuk ditampilkan di halaman beranda
                session()->flash('show_lengkapi_profil_modal', [
                    'pelanggan_name' => $pelangganName,
                    'redirect_from' => 'google-auth',
                ]);
            }

            // Redirect langsung ke beranda pelanggan dengan flash message
            return redirect()->route('beranda.pelanggan')
                ->with('success', 'Selamat datang kembali, '.$pelangganName.'!');

        } catch (Exception $e) {
            Log::error('Google OAuth Error: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login.pelanggan')
                ->with('error', 'Login dengan Google gagal: '.$e->getMessage());
        }
    }
}
