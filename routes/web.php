<?php

use App\Livewire\Kasir;
use App\Livewire\Pengaturan;
use App\Livewire\Welcome;
use App\Livewire\Component\Receipt;
use Illuminate\Support\Facades\Route;
use App\Livewire\Layanan\Edit as LayananEdit;
use App\Livewire\Layanan\Index as LayananIndex;
use App\Livewire\Layanan\Create as LayananCreate;
use App\Livewire\Pelanggan\Edit as PelangganEdit;
use App\Livewire\Transaksi\Edit as TransaksiEdit;
use App\Livewire\Pelanggan\Index as PelangganIndex;
use App\Livewire\Transaksi\Index as TransaksiIndex;
use App\Livewire\Pelanggan\Create as PelangganCreate;
use App\Livewire\Transaksi\Create as TransaksiCreate;
use App\Livewire\JenisPakaian\Edit as JenisPakaianEdit;
use App\Livewire\JenisPakaian\Index as JenisPakaianIndex;
use App\Livewire\JenisPakaian\Create as JenisPakaianCreate;
use App\Livewire\Admin\Edit as AdminEdit;
use App\Livewire\Admin\Index as AdminIndex;
use App\Livewire\Admin\Create as AdminCreate;
use App\Livewire\Login;
use Illuminate\Support\Facades\Auth;

// Login Route
Route::get('/login', Login::class)->name('login');

// Logout Route
Route::get('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Protected Routes
Route::middleware('auth')->group(function() {
    Route::get('/', Welcome::class);

// Kasir Route
Route::get('/kasir', Kasir::class)->name('kasir');

// Layanan Routes
Route::get('/layanan', LayananIndex::class)->name('layanan.index');
Route::get('/layanan/create', LayananCreate::class)->name('layanan.create');
Route::get('/layanan/edit/{id}', LayananEdit::class)->name('layanan.edit');

// Pelanggan Routes
Route::get('/pelanggan', PelangganIndex::class)->name('pelanggan.index');
Route::get('/pelanggan/create', PelangganCreate::class)->name('pelanggan.create');
Route::get('/pelanggan/edit/{id}', PelangganEdit::class)->name('pelanggan.edit');

// Jenis Pakaian Routes
Route::get('/jenis-pakaian', JenisPakaianIndex::class)->name('jenis-pakaian.index');
Route::get('/jenis-pakaian/create', JenisPakaianCreate::class)->name('jenis-pakaian.create');
Route::get('/jenis-pakaian/edit/{id}', JenisPakaianEdit::class)->name('jenis-pakaian.edit');

// Transaksi Routes
Route::get('/transaksi', TransaksiIndex::class)->name('transaksi.index');
Route::get('/transaksi/create', TransaksiCreate::class)->name('transaksi.create');
Route::get('/transaksi/edit/{id}', TransaksiEdit::class)->name('transaksi.edit');

// Pengaturan Route
Route::get('/pengaturan', Pengaturan::class)->name('pengaturan');

// Admin Routes
Route::get('/admin', AdminIndex::class)->name('admin.index');
Route::get('/admin/create', AdminCreate::class)->name('admin.create');
Route::get('/admin/edit/{id}', AdminEdit::class)->name('admin.edit');

// Receipt Print Route
Route::get('/receipt/print/{id}', function($id) {
    $receipt = new Receipt();
    $receipt->mount($id);

    // Format nomor HP dengan prefix 0 jika belum ada
    $noHp = $receipt->pelangganNoHp;
    if (!empty($noHp) && !str_starts_with($noHp, '0')) {
        $noHp = '0' . $noHp;
    }

    return view('livewire.component.receipt', [
        'transaksiData' => $receipt->transaksiData,
        'setting' => $receipt->setting,
        'pelangganAlamat' => $receipt->pelangganAlamat,
        'pelangganNoHp' => $noHp,
    ]);
})->name('receipt.print');

}); // End of auth middleware group
