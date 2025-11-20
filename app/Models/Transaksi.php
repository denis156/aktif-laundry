<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// ! Model Transaksi - Transaction Header
//
// ? Menyimpan header transaksi laundry
// ? Mendukung multi-service (satu transaksi bisa punya banyak layanan)
// ? Metadata: pembayaran, promo, referral, foto_bukti_timbangan, foto_bukti_pembayaran, kurir_jemput, kurir_antar

class Transaksi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transaksi';

    // * Fillable attributes
    protected $fillable = [
        'kode_transaksi',
        'tanggal_masuk',
        'kasir_id',
        'pelanggan_id',
        'nama_pelanggan',
        'total_berat',
        'total_item',
        'jumlah_layanan',
        'subtotal',
        'diskon',
        'total',
        'metode_pembayaran',
        'tanggal_selesai',
        'status',
        'catatan',
        'metadata',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'datetime',
            'tanggal_selesai' => 'datetime',
            'total_berat' => 'decimal:2',
            'total_item' => 'integer',
            'jumlah_layanan' => 'integer',
            'subtotal' => 'integer',
            'diskon' => 'integer',
            'total' => 'integer',
            'kasir_id' => 'integer',
            'pelanggan_id' => 'integer',
            'metadata' => 'array',
        ];
    }

    // * Relasi: Kasir yang input transaksi
    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    // * Relasi: Pelanggan yang transaksi
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    // * Relasi: Detail layanan transaksi
    public function transaksiLayanan(): HasMany
    {
        return $this->hasMany(TransaksiLayanan::class, 'transaksi_id');
    }

    // * Relasi: Data pengiriman (jemput/antar)
    public function pengiriman(): HasMany
    {
        return $this->hasMany(Pengiriman::class, 'transaksi_id');
    }

    // * Relasi: Data pembayaran
    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'transaksi_id');
    }
}
