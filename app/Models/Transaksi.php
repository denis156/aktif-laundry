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
// ? Semua data sudah disimpan di kolom terpisah (tidak ada metadata)

class Transaksi extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'transaksi';

    // * Fillable attributes
    protected $fillable = [
        'kode_transaksi',
        'tanggal_masuk',
        'kasir_id',
        'pelanggan_id',
        'referral_id',
        'nama_pelanggan',
        // Summary fields
        'total_berat',
        'total_item',
        'jumlah_layanan',
        // Financial
        'subtotal',
        'total',
        // Payment
        'metode_pembayaran',
        'tipe_bayar',
        'status_bayar',
        'tanggal_bayar',
        'jumlah_bayar',
        // Kurir
        'kurir_jemput_id',
        'kurir_antar_id',
        'kurir_jemput_nama',
        'kurir_antar_nama',
        // Status & Timeline
        'tanggal_selesai',
        'status',
        'catatan',
        'catatan_internal',
        // Bukti (JSON)
        'foto_bukti_timbangan',
        'foto_bukti_pembayaran',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'datetime',
            'tanggal_selesai' => 'datetime',
            'tanggal_bayar' => 'datetime',
            'total_berat' => 'decimal:2',
            'total_item' => 'integer',
            'jumlah_layanan' => 'integer',
            'subtotal' => 'integer',
            'total' => 'integer',
            'jumlah_bayar' => 'integer',
            'kasir_id' => 'integer',
            'pelanggan_id' => 'integer',
            'referral_id' => 'integer',
            'kurir_jemput_id' => 'integer',
            'kurir_antar_id' => 'integer',
            'foto_bukti_timbangan' => 'array',
            'foto_bukti_pembayaran' => 'array',
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

    // * Relasi: Promo yang digunakan
    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class, 'promo_id');
    }

    // * Relasi: Referral yang digunakan
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'referral_id');
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
}
