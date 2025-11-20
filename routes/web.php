<?php

use App\Livewire\Management\Auth\Login;
use App\Livewire\Management\Component\Receipt;
use App\Livewire\Management\Dashboard;
use App\Livewire\Management\JenisPakaian\Create as JenisPakaianCreate;
use App\Livewire\Management\JenisPakaian\Edit as JenisPakaianEdit;
use App\Livewire\Management\JenisPakaian\Index as JenisPakaianIndex;
use App\Livewire\Management\Kasir;
use App\Livewire\Management\Layanan\Create as LayananCreate;
use App\Livewire\Management\Layanan\Edit as LayananEdit;
use App\Livewire\Management\Layanan\Index as LayananIndex;
use App\Livewire\Management\Pelanggan\Create as PelangganCreate;
use App\Livewire\Management\Pelanggan\Edit as PelangganEdit;
use App\Livewire\Management\Pelanggan\Index as PelangganIndex;
use App\Livewire\Management\Pengaturan;
use App\Livewire\Management\Profile;
use App\Livewire\Management\Staf\Create as StafCreate;
use App\Livewire\Management\Staf\Edit as StafEdit;
use App\Livewire\Management\Staf\Index as StafIndex;
use App\Livewire\Management\Transaksi\Create as TransaksiCreate;
use App\Livewire\Management\Transaksi\Edit as TransaksiEdit;
use App\Livewire\Management\Transaksi\Index as TransaksiIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Landing Page Route - Public
Route::view('/', 'pages.landingpage')->name('landing-page');

Route::get('/kurir', function () {
    return view('layouts/kurir/app');
});

// Public Routes - tanpa auth
Route::prefix('management')->group(function () {
    // Login Route di /management/login
    Route::get('/login', Login::class)->name('login');

    // Logout Route di /management/logout
    Route::get('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});

// Protected Routes dengan prefix /management
Route::middleware('auth')->prefix('management')->group(function () {

    // Dashboard di /management
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/dashboard', Dashboard::class)->name('dashboard.alternative');

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

    // Profile Route di /management/profile
    Route::get('/profile', Profile::class)->name('profile');

    // Receipt Print Route di /management/receipt/print
    Route::get('/receipt/print/{id}', function (int $id) {
        $receiptData = Receipt::generateReceiptData($id);

        return view('livewire.management.component.receipt', $receiptData);
    })->name('receipt.print');

}); // End of auth middleware dan prefix management group
