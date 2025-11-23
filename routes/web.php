<?php

use App\Livewire\Kurir\Info;
use App\Livewire\Kurir\Rute;
use App\Livewire\Kurir\Beranda;
use App\Livewire\Kurir\Pengiriman;
use App\Livewire\Management\Kasir;
use App\Livewire\Management\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Livewire\Management\Dashboard;
use App\Livewire\Management\Auth\Login;
use App\Livewire\Management\Pengaturan;
use App\Livewire\Management\Component\Receipt;
use App\Livewire\Management\Staf\Edit as StafEdit;
use App\Livewire\Management\Kurir\Edit as KurirEdit;
use App\Livewire\Management\Staf\Index as StafIndex;
use App\Livewire\Kurir\Pengaturan as PengaturanKurir;
use App\Livewire\Management\Kurir\Index as KurirIndex;
use App\Livewire\Management\Staf\Create as StafCreate;
use App\Livewire\Management\Kurir\Create as KurirCreate;
use App\Livewire\Management\Layanan\Edit as LayananEdit;
use App\Livewire\Management\Layanan\Index as LayananIndex;
use App\Livewire\Management\Layanan\Create as LayananCreate;
use App\Livewire\Management\Pelanggan\Edit as PelangganEdit;
use App\Livewire\Management\Transaksi\Edit as TransaksiEdit;
use App\Livewire\Management\Pelanggan\Index as PelangganIndex;
use App\Livewire\Management\Transaksi\Index as TransaksiIndex;
use App\Livewire\Management\Pelanggan\Create as PelangganCreate;
use App\Livewire\Management\Transaksi\Create as TransaksiCreate;
use App\Livewire\Management\JenisPakaian\Edit as JenisPakaianEdit;
use App\Livewire\Management\JenisPakaian\Index as JenisPakaianIndex;
use App\Livewire\Management\JenisPakaian\Create as JenisPakaianCreate;

// Landing Page Route - Public
Route::view('/', 'pages.landingpage')->name('landing-page');

Route::get('/kurir', Beranda::class)->name('beranda.kurir');
Route::get('/kurir/pengiriman', Pengiriman::class)->name('pengiriman.kurir');
Route::get('/kurir/rute', Rute::class)->name('rute.kurir');
Route::get('/kurir/info', Info::class)->name('info.kurir');
Route::get('/kurir/pengaturan', PengaturanKurir::class)->name('pengaturan.kurir');

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

    // Profile Route di /management/profile
    Route::get('/profile', Profile::class)->name('profile');

    // Receipt Print Route di /management/receipt/print
    Route::get('/receipt/print/{id}', function (int $id) {
        $receiptData = Receipt::generateReceiptData($id);

        return view('livewire.management.component.receipt', $receiptData);
    })->name('receipt.print');

}); // End of auth middleware dan prefix management group
