<?php

use App\Livewire\Kurir\Auth\ForgotPassword as KurirForgotPassword;
use App\Livewire\Kurir\Auth\Login as KurirLogin;
use App\Livewire\Kurir\Auth\ResetPassword as KurirResetPassword;
use App\Livewire\Kurir\Auth\VerifyEmail as KurirVerifyEmail;
use App\Livewire\Kurir\Beranda;
use App\Livewire\Kurir\Pengaturan as PengaturanKurir;
use App\Livewire\Kurir\Pengiriman;
use App\Livewire\Kurir\Rute;
use App\Livewire\Management\Auth\ForgotPassword;
use App\Livewire\Management\Auth\Login;
use App\Livewire\Management\Auth\ResetPassword;
use App\Livewire\Management\Auth\VerifyEmail;
use App\Livewire\Management\Component\Receipt;
use App\Livewire\Management\Dashboard;
use App\Livewire\Management\JenisPakaian\Create as JenisPakaianCreate;
use App\Livewire\Management\JenisPakaian\Edit as JenisPakaianEdit;
use App\Livewire\Management\JenisPakaian\Index as JenisPakaianIndex;
use App\Livewire\Management\Kasir;
use App\Livewire\Management\Kurir\Create as KurirCreate;
use App\Livewire\Management\Kurir\Edit as KurirEdit;
use App\Livewire\Management\Kurir\Index as KurirIndex;
use App\Livewire\Management\Layanan\Create as LayananCreate;
use App\Livewire\Management\Layanan\Edit as LayananEdit;
use App\Livewire\Management\Layanan\Index as LayananIndex;
use App\Livewire\Management\Pelanggan\Create as PelangganCreate;
use App\Livewire\Management\Pelanggan\Edit as PelangganEdit;
use App\Livewire\Management\Pelanggan\Index as PelangganIndex;
use App\Livewire\Management\Pengaturan;
use App\Livewire\Management\Profile;
use App\Livewire\Management\Promo\Create as PromoCreate;
use App\Livewire\Management\Promo\Edit as PromoEdit;
use App\Livewire\Management\Promo\Index as PromoIndex;
use App\Livewire\Management\Referral\Create as ReferralCreate;
use App\Livewire\Management\Referral\Edit as ReferralEdit;
use App\Livewire\Management\Referral\Index as ReferralIndex;
use App\Livewire\Management\Staf\Create as StafCreate;
use App\Livewire\Management\Staf\Edit as StafEdit;
use App\Livewire\Management\Staf\Index as StafIndex;
use App\Livewire\Management\Transaksi\Create as TransaksiCreate;
use App\Livewire\Management\Transaksi\Edit as TransaksiEdit;
use App\Livewire\Management\Transaksi\Index as TransaksiIndex;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Landing Page Route - Public
Route::view('/', 'pages.landingpage')->name('landing-page');

// Kurir Auth Routes - Guest (tanpa auth)
Route::middleware('guest:kurir')->prefix('kurir')->group(function () {
    // Login Route di /kurir/login
    Route::get('/login', KurirLogin::class)->name('login.kurir');

    // Forgot Password Route di /kurir/forgot-password
    Route::get('/forgot-password', KurirForgotPassword::class)->name('kurir.password.request');

    // Reset Password Route di /kurir/reset-password/{token}
    Route::get('/reset-password/{token}', KurirResetPassword::class)->name('kurir.password.reset');
});

// Kurir Logout Route (authenticated)
Route::middleware('auth:kurir')->prefix('kurir')->group(function () {
    Route::get('/logout', function () {
        Auth::guard('kurir')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    })->name('kurir.logout');
});

// Kurir Email Verification Routes (authenticated)
Route::middleware('auth:kurir')->prefix('kurir')->group(function () {
    // Email Verification Notice Page
    Route::get('/verify-email', KurirVerifyEmail::class)
        ->name('kurir.verification.notice');

    // Email Verification Handler (Laravel built-in)
    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('beranda.kurir');
    })->middleware(['signed'])->name('kurir.verification.verify');
});

// Protected Kurir Routes
Route::middleware(['auth:kurir', 'verified.kurir'])->prefix('kurir')->group(function () {
    Route::get('/', Beranda::class)->name('beranda.kurir');
    Route::get('/pengiriman', Pengiriman::class)->name('pengiriman.kurir');
    Route::get('/rute', Rute::class)->name('rute.kurir');
    Route::get('/pengaturan', PengaturanKurir::class)->name('pengaturan.kurir');
});

// Management Auth Routes - Guest (tanpa auth)
Route::middleware('guest')->prefix('management')->group(function () {
    // Login Route di /management/login
    Route::get('/login', Login::class)->name('login');

    // Forgot Password Route di /management/forgot-password
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');

    // Reset Password Route di /management/reset-password/{token}
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

// Management Logout Route (authenticated)
Route::middleware('auth')->prefix('management')->group(function () {
    Route::get('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});

// Email Verification Routes (authenticated)
Route::middleware('auth')->prefix('management')->group(function () {
    // Email Verification Notice Page
    Route::get('/verify-email', VerifyEmail::class)
        ->name('verification.notice');

    // Email Verification Handler (Laravel built-in)
    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard');
    })->middleware(['signed'])->name('verification.verify');
});

// Protected Routes dengan prefix /management
Route::middleware(['auth', 'verified'])->prefix('management')->group(function () {

    // Dashboard di /management
    Route::get('/', Dashboard::class)->name('dashboard');

    // Kasir Route di /management/kasir
    Route::get('/kasir', Kasir::class)->name('kasir');

    // Layanan Routes di /management/layanan
    Route::get('/layanan', LayananIndex::class)->name('layanan.index');
    Route::get('/layanan/create', LayananCreate::class)->name('layanan.create');
    Route::get('/layanan/edit/{id}', LayananEdit::class)->name('layanan.edit');

    // Pelanggan Routes di /management/pelanggan
    Route::get('/pelanggan', PelangganIndex::class)->name('pelanggan.index');
    Route::get('/pelanggan/create', PelangganCreate::class)->name('pelanggan.create');
    Route::get('/pelanggan/edit/{id}', PelangganEdit::class)->name('pelanggan.edit');

    // Jenis Pakaian Routes di /management/jenis-pakaian
    Route::get('/jenis-pakaian', JenisPakaianIndex::class)->name('jenis-pakaian.index');
    Route::get('/jenis-pakaian/create', JenisPakaianCreate::class)->name('jenis-pakaian.create');
    Route::get('/jenis-pakaian/edit/{id}', JenisPakaianEdit::class)->name('jenis-pakaian.edit');

    // Transaksi Routes di /management/transaksi
    Route::get('/transaksi', TransaksiIndex::class)->name('transaksi.index');
    Route::get('/transaksi/create', TransaksiCreate::class)->name('transaksi.create');
    Route::get('/transaksi/edit/{id}', TransaksiEdit::class)->name('transaksi.edit');

    // Pengaturan Route di /management/pengaturan (Super Admin Only)
    Route::middleware('super_admin')->group(function () {
        Route::get('/pengaturan', Pengaturan::class)->name('pengaturan');
    });

    // Staf Routes di /management/staf (Super Admin Only)
    Route::middleware('super_admin')->group(function () {
        Route::get('/staf', StafIndex::class)->name('staf.index');
        Route::get('/staf/create', StafCreate::class)->name('staf.create');
        Route::get('/staf/edit/{id}', StafEdit::class)->name('staf.edit');
    });

    // Kurir Routes di /management/kurir
    Route::get('/kurir', KurirIndex::class)->name('kurir.index');
    Route::get('/kurir/create', KurirCreate::class)->name('kurir.create');
    Route::get('/kurir/edit/{id}', KurirEdit::class)->name('kurir.edit');

    // Promo Routes di /management/promo
    Route::get('/promo', PromoIndex::class)->name('promo.index');
    Route::get('/promo/create', PromoCreate::class)->name('promo.create');
    Route::get('/promo/edit/{id}', PromoEdit::class)->name('promo.edit');

    // Referral Routes di /management/referral
    Route::get('/referral', ReferralIndex::class)->name('referral.index');
    Route::get('/referral/create', ReferralCreate::class)->name('referral.create');
    Route::get('/referral/edit/{id}', ReferralEdit::class)->name('referral.edit');

    // Profile Route di /management/profile
    Route::get('/profile', Profile::class)->name('profile');

    // Receipt Print Route di /management/receipt/print
    Route::get('/receipt/print/{id}', function (int $id) {
        $receiptData = Receipt::generateReceiptData($id);

        return view('livewire.management.component.receipt', $receiptData);
    })->name('receipt.print');

}); // End of auth middleware dan prefix management group
