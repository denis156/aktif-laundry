<?php

use App\Livewire\Admin\Kasir;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Pengaturan;
use App\Livewire\Admin\Auth\Login;
use App\Livewire\Admin\Component\Receipt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Staf\Edit as StafEdit;
use App\Livewire\Admin\Staf\Index as StafIndex;
use App\Livewire\Admin\Staf\Create as StafCreate;
use App\Livewire\Admin\Layanan\Edit as LayananEdit;
use App\Livewire\Admin\Layanan\Index as LayananIndex;
use App\Livewire\Admin\Layanan\Create as LayananCreate;
use App\Livewire\Admin\Pelanggan\Edit as PelangganEdit;
use App\Livewire\Admin\Transaksi\Edit as TransaksiEdit;
use App\Livewire\Admin\Pelanggan\Index as PelangganIndex;
use App\Livewire\Admin\Transaksi\Index as TransaksiIndex;
use App\Livewire\Admin\Pelanggan\Create as PelangganCreate;
use App\Livewire\Admin\Transaksi\Create as TransaksiCreate;
use App\Livewire\Admin\JenisPakaian\Edit as JenisPakaianEdit;
use App\Livewire\Admin\JenisPakaian\Index as JenisPakaianIndex;
use App\Livewire\Admin\JenisPakaian\Create as JenisPakaianCreate;

// Landing Page Route - Public
Route::view('/', 'pages.landingpage')->name('landing-page');

// Public Routes - tanpa auth
Route::prefix('admin')->group(function() {
    // Login Route di /admin/login
    Route::get('/login', Login::class)->name('login');

    // Logout Route di /admin/logout
    Route::get('/logout', function() {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

// Protected Routes dengan prefix /admin
Route::middleware('auth')->prefix('admin')->group(function() {

    // Dashboard di /admin
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/dashboard', Dashboard::class)->name('dashboard.alternative');

    // Kasir Route di /admin/kasir
    Route::get('/kasir', Kasir::class)->name('kasir');

    // Layanan Routes di /admin/layanan
    Route::get('/layanan', LayananIndex::class)->name('layanan.index');
    Route::get('/layanan/create', LayananCreate::class)->name('layanan.create');
    Route::get('/layanan/edit/{id}', LayananEdit::class)->name('layanan.edit');

    // Pelanggan Routes di /admin/pelanggan
    Route::get('/pelanggan', PelangganIndex::class)->name('pelanggan.index');
    Route::get('/pelanggan/create', PelangganCreate::class)->name('pelanggan.create');
    Route::get('/pelanggan/edit/{id}', PelangganEdit::class)->name('pelanggan.edit');

    // Jenis Pakaian Routes di /admin/jenis-pakaian
    Route::get('/jenis-pakaian', JenisPakaianIndex::class)->name('jenis-pakaian.index');
    Route::get('/jenis-pakaian/create', JenisPakaianCreate::class)->name('jenis-pakaian.create');
    Route::get('/jenis-pakaian/edit/{id}', JenisPakaianEdit::class)->name('jenis-pakaian.edit');

    // Transaksi Routes di /admin/transaksi
    Route::get('/transaksi', TransaksiIndex::class)->name('transaksi.index');
    Route::get('/transaksi/create', TransaksiCreate::class)->name('transaksi.create');
    Route::get('/transaksi/edit/{id}', TransaksiEdit::class)->name('transaksi.edit');

    // Pengaturan Route di /admin/pengaturan (Super Admin Only)
    Route::middleware('super_admin')->group(function() {
        Route::get('/pengaturan', Pengaturan::class)->name('pengaturan');
    });

    // Staf Routes di /admin/staf (Super Admin Only)
    Route::middleware('super_admin')->group(function() {
        Route::get('/staf', StafIndex::class)->name('staf.index');
        Route::get('/staf/create', StafCreate::class)->name('staf.create');
        Route::get('/staf/edit/{id}', StafEdit::class)->name('staf.edit');
    });

    // Profile Route di /admin/profile
    Route::get('/profile', Profile::class)->name('profile');

    // Receipt Print Route di /admin/receipt/print
    Route::get('/receipt/print/{id}', function($id) {
        $receiptData = Receipt::generateReceiptData($id);

        return view('livewire.admin.component.receipt', $receiptData);
    })->name('receipt.print');

}); // End of auth middleware dan prefix admin group
