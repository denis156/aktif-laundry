<?php

declare(strict_types=1);

use App\Helper\ManifestHelper;
use App\Livewire\Kurir\Pages\Aktifitas\Detail as KurirDetailAktifitas;
use App\Livewire\Kurir\Pages\Aktifitas\Index as KurirAktifitas;
use App\Livewire\Kurir\Pages\Auth\ForgotPassword as KurirForgotPassword;
use App\Livewire\Kurir\Pages\Auth\Login as KurirLogin;
use App\Livewire\Kurir\Pages\Auth\ResetPassword as KurirResetPassword;
use App\Livewire\Kurir\Pages\Auth\VerifyEmail as KurirVerifyEmail;
use App\Livewire\Kurir\Pages\Beranda as KurirBeranda;
use App\Livewire\Kurir\Pages\Chat\Index as KurirChatIndex;
use App\Livewire\Kurir\Pages\Chat\Room as KurirChatRoom;
use App\Livewire\Kurir\Pages\Pengaturan as PengaturanKurir;
use App\Livewire\Kurir\Pages\Profile as KurirProfile;
use App\Livewire\Kurir\Pages\Rute\Detail as KurirRuteDetail;
use App\Livewire\Kurir\Pages\Rute\Index as KurirRute;
use App\Livewire\Management\Components\Receipt;
use App\Livewire\Management\Pages\Auth\ForgotPassword;
use App\Livewire\Management\Pages\Auth\Login;
use App\Livewire\Management\Pages\Auth\ResetPassword;
use App\Livewire\Management\Pages\Auth\VerifyEmail;
use App\Livewire\Management\Pages\Chat\Index as ChatIndex;
use App\Livewire\Management\Pages\Chat\Room as ChatRoom;
use App\Livewire\Management\Pages\Dashboard;
use App\Livewire\Management\Pages\Fonnte\Create as FonnteCreate;
use App\Livewire\Management\Pages\Fonnte\Edit as FonnteEdit;
use App\Livewire\Management\Pages\Fonnte\Index as FonnteIndex;
use App\Livewire\Management\Pages\JenisPakaian\Create as JenisPakaianCreate;
use App\Livewire\Management\Pages\JenisPakaian\Edit as JenisPakaianEdit;
use App\Livewire\Management\Pages\JenisPakaian\Index as JenisPakaianIndex;
use App\Livewire\Management\Pages\Kasir;
use App\Livewire\Management\Pages\Kurir\Create as KurirCreate;
use App\Livewire\Management\Pages\Kurir\Edit as KurirEdit;
use App\Livewire\Management\Pages\Kurir\Index as KurirIndex;
use App\Livewire\Management\Pages\Layanan\Create as LayananCreate;
use App\Livewire\Management\Pages\Layanan\Edit as LayananEdit;
use App\Livewire\Management\Pages\Layanan\Index as LayananIndex;
use App\Livewire\Management\Pages\Pelanggan\Create as PelangganCreate;
use App\Livewire\Management\Pages\Pelanggan\Edit as PelangganEdit;
use App\Livewire\Management\Pages\Pelanggan\Index as PelangganIndex;
use App\Livewire\Management\Pages\Pengaturan;
use App\Livewire\Management\Pages\Profile;
use App\Livewire\Management\Pages\Promo\Create as PromoCreate;
use App\Livewire\Management\Pages\Promo\Edit as PromoEdit;
use App\Livewire\Management\Pages\Promo\Index as PromoIndex;
use App\Livewire\Management\Pages\Referral\Edit as ReferralEdit;
use App\Livewire\Management\Pages\Referral\Index as ReferralIndex;
use App\Livewire\Management\Pages\Referral\Pengaturan as ReferralPengaturan;
use App\Livewire\Management\Pages\Staf\Create as StafCreate;
use App\Livewire\Management\Pages\Staf\Edit as StafEdit;
use App\Livewire\Management\Pages\Staf\Index as StafIndex;
use App\Livewire\Management\Pages\Tracking\Index as TrackingIndex;
use App\Livewire\Management\Pages\Transaksi\Create as TransaksiCreate;
use App\Livewire\Management\Pages\Transaksi\Edit as TransaksiEdit;
use App\Livewire\Management\Pages\Transaksi\Index as TransaksiIndex;
use App\Livewire\Pelanggan\Pages\Auth\ForgotPassword as PelangganForgotPassword;
use App\Livewire\Pelanggan\Pages\Auth\Login as PelangganLogin;
use App\Livewire\Pelanggan\Pages\Auth\Register as PelangganRegister;
use App\Livewire\Pelanggan\Pages\Auth\ResetPassword as PelangganResetPassword;
use App\Livewire\Pelanggan\Pages\Auth\VerifyEmail as PelangganVerifyEmail;
use App\Livewire\Pelanggan\Pages\Beranda as PelangganBeranda;
use App\Livewire\Pelanggan\Pages\Chat\Index as PelangganChatIndex;
use App\Livewire\Pelanggan\Pages\Chat\Room as PelangganChatRoom;
use App\Livewire\Pelanggan\Pages\Layanan\Detail as PelangganDetailLayanan;
use App\Livewire\Pelanggan\Pages\Layanan\Index as PelangganPilihLayanan;
use App\Livewire\Pelanggan\Pages\Pengaturan as PelangganPengaturan;
use App\Livewire\Pelanggan\Pages\Pesanan\Create as PelangganBuatPesanan;
use App\Livewire\Pelanggan\Pages\Pesanan\Detail as PelangganDetailPesanan;
use App\Livewire\Pelanggan\Pages\Pesanan\Edit as PelangganEditPesanan;
use App\Livewire\Pelanggan\Pages\Pesanan\Index as PelangganRiwayat;
use App\Livewire\Pelanggan\Pages\Profile as PelangganProfile;
use App\Livewire\Pelanggan\Pages\Promo\Detail as PelangganDetailPromo;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'pages.landingpage')->name('landing-page');

Route::get('/manifest-kurir.json', fn () => response()->json(ManifestHelper::kurirManifest()))->name('manifest.kurir');
Route::get('/manifest-pelanggan.json', fn () => response()->json(ManifestHelper::pelangganManifest()))->name('manifest.pelanggan');

/*
|--------------------------------------------------------------------------
| Kurir Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware(['guest:kurir'])->prefix('kurir')->group(function () {
    Route::get('/login', KurirLogin::class)->name('login.kurir');
    Route::get('/forgot-password', KurirForgotPassword::class)->name('kurir.password.request');
    Route::get('/reset-password/{token}', KurirResetPassword::class)->name('kurir.password.reset');
});

// Authenticated Routes
Route::middleware('auth:kurir')->prefix('kurir')->group(function () {
    // Logout
    Route::get('/logout', function () {
        Auth::guard('kurir')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login.kurir');
    })->name('kurir.logout');

    // Email Verification
    Route::get('/verify-email', KurirVerifyEmail::class)->name('kurir.verification.notice');
    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('beranda.kurir');
    })->middleware(['signed'])->name('kurir.verification.verify');
});

// Protected Routes
Route::middleware(['auth:kurir', 'verified.kurir'])->prefix('kurir')->group(function () {
    Route::get('/', KurirBeranda::class)->name('beranda.kurir');
    Route::get('/aktifitas', KurirAktifitas::class)->name('aktifitas.kurir');
    Route::get('/aktifitas/{id}', KurirDetailAktifitas::class)->name('detail-aktifitas.kurir');
    Route::get('/rute', KurirRute::class)->name('rute.kurir');
    Route::get('/rute/{id}', KurirRuteDetail::class)->name('rute-detail.kurir');
    Route::get('/chat', KurirChatIndex::class)->name('chat.kurir');
    Route::get('/chat/{conversation}', KurirChatRoom::class)->name('chat-room.kurir');
    Route::get('/pengaturan', PengaturanKurir::class)->name('pengaturan.kurir');
    Route::get('/profile', KurirProfile::class)->name('profile.kurir');
});

/*
|--------------------------------------------------------------------------
| Management Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware(['guest:web'])->prefix('management')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

// Authenticated Routes
Route::middleware('auth')->prefix('management')->group(function () {
    // Logout
    Route::get('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    // Email Verification
    Route::get('/verify-email', VerifyEmail::class)->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard');
    })->middleware(['signed'])->name('verification.verify');
});

// Protected Routes
Route::middleware(['auth', 'verified'])->prefix('management')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/kasir', Kasir::class)->name('kasir');

    // Layanan
    Route::get('/layanan', LayananIndex::class)->name('layanan.index');
    Route::get('/layanan/create', LayananCreate::class)->name('layanan.create');
    Route::get('/layanan/edit/{id}', LayananEdit::class)->name('layanan.edit');

    // Pelanggan
    Route::get('/pelanggan', PelangganIndex::class)->name('pelanggan.index');
    Route::get('/pelanggan/create', PelangganCreate::class)->name('pelanggan.create');
    Route::get('/pelanggan/edit/{id}', PelangganEdit::class)->name('pelanggan.edit');

    // Jenis Pakaian
    Route::get('/jenis-pakaian', JenisPakaianIndex::class)->name('jenis-pakaian.index');
    Route::get('/jenis-pakaian/create', JenisPakaianCreate::class)->name('jenis-pakaian.create');
    Route::get('/jenis-pakaian/edit/{id}', JenisPakaianEdit::class)->name('jenis-pakaian.edit');

    // Transaksi
    Route::get('/transaksi', TransaksiIndex::class)->name('transaksi.index');
    Route::get('/transaksi/create', TransaksiCreate::class)->name('transaksi.create');
    Route::get('/transaksi/edit/{id}', TransaksiEdit::class)->name('transaksi.edit');

    // Kurir
    Route::get('/kurir', KurirIndex::class)->name('kurir.index');
    Route::get('/kurir/create', KurirCreate::class)->name('kurir.create');
    Route::get('/kurir/edit/{id}', KurirEdit::class)->name('kurir.edit');

    // Tracking
    Route::get('/tracking', TrackingIndex::class)->name('tracking.index');

    // Promo
    Route::get('/promo', PromoIndex::class)->name('promo.index');
    Route::get('/promo/create', PromoCreate::class)->name('promo.create');
    Route::get('/promo/edit/{id}', PromoEdit::class)->name('promo.edit');

    // Referral
    Route::get('/referral', ReferralIndex::class)->name('referral.index');
    Route::get('/referral/pengaturan', ReferralPengaturan::class)->name('referral.pengaturan');
    Route::get('/referral/edit/{id}', ReferralEdit::class)->name('referral.edit');

    // Chat
    Route::get('/chat', ChatIndex::class)->name('chat.index');
    Route::get('/chat/{conversation}', ChatRoom::class)->name('chat.room');

    // Profile
    Route::get('/profile', Profile::class)->name('profile');

    // Receipt
    Route::get('/receipt/print/{id}', function (int $id) {
        $receiptData = Receipt::generateReceiptData($id);

        return view('livewire.management.components.receipt', $receiptData);
    })->name('receipt.print');

    // Super Admin Only Routes
    Route::middleware('super_admin')->group(function () {
        Route::get('/pengaturan', Pengaturan::class)->name('pengaturan');

        Route::get('/staf', StafIndex::class)->name('staf.index');
        Route::get('/staf/create', StafCreate::class)->name('staf.create');
        Route::get('/staf/edit/{id}', StafEdit::class)->name('staf.edit');

        Route::get('/fonnte', FonnteIndex::class)->name('fonnte.index');
        Route::get('/fonnte/create', FonnteCreate::class)->name('fonnte.create');
        Route::get('/fonnte/edit/{token}', FonnteEdit::class)->name('fonnte.edit');
    });
});

/*
|--------------------------------------------------------------------------
| Pelanggan Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware(['guest:pelanggan'])->prefix('pelanggan')->group(function () {
    Route::get('/login', PelangganLogin::class)->name('login.pelanggan');
    Route::get('/register', PelangganRegister::class)->name('register.pelanggan');
    Route::get('/forgot-password', PelangganForgotPassword::class)->name('pelanggan.password.request');
    Route::get('/reset-password/{token}', PelangganResetPassword::class)->name('pelanggan.password.reset');
});

// Authenticated Routes
Route::middleware('auth:pelanggan')->prefix('pelanggan')->group(function () {
    // Logout
    Route::get('/logout', function () {
        Auth::guard('pelanggan')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login.pelanggan');
    })->name('pelanggan.logout');

    // Email Verification
    Route::get('/verify-email', PelangganVerifyEmail::class)->name('pelanggan.verification.notice');
    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('beranda.pelanggan');
    })->middleware(['signed'])->name('pelanggan.verification.verify');
});

// Protected Routes
Route::middleware(['auth:pelanggan', 'verified.pelanggan'])->prefix('pelanggan')->group(function () {
    Route::get('/', PelangganBeranda::class)->name('beranda.pelanggan');

    // Pesanan
    Route::get('/pesan', PelangganPilihLayanan::class)->name('pesanan.pelanggan');
    Route::get('/pesan/form', PelangganBuatPesanan::class)->name('pesanan-form.pelanggan');
    Route::get('/pesan/{id}/edit', PelangganEditPesanan::class)->name('edit-pesanan.pelanggan');

    // Promo & Layanan
    Route::get('/promo/{id}', PelangganDetailPromo::class)->name('detail-promo.pelanggan');
    Route::get('/layanan/{id}', PelangganDetailLayanan::class)->name('detail-layanan.pelanggan');

    // Riwayat
    Route::get('/riwayat', PelangganRiwayat::class)->name('riwayat.pelanggan');
    Route::get('/riwayat/{id}', PelangganDetailPesanan::class)->name('detail-pesanan.pelanggan');

    // Chat
    Route::get('/chat', PelangganChatIndex::class)->name('chat.pelanggan');
    Route::get('/chat/{conversation}', PelangganChatRoom::class)->name('chat-room.pelanggan');

    // Pengaturan & Profile
    Route::get('/pengaturan', PelangganPengaturan::class)->name('pengaturan.pelanggan');
    Route::get('/pengaturan/profile', PelangganProfile::class)->name('profile.pelanggan');
});
