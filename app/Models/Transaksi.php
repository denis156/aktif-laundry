<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaksi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transaksi';

    protected $fillable = [
        'kode_transaksi',
        'tanggal_masuk',
        'kasir_id',
        'pelanggan_id',
        'nama_pelanggan',
        'layanan_id',
        'nama_layanan',
        'jenis_pakaian',
        'berat_kg',
        'harga_per_kg',
        'subtotal',
        'diskon',
        'total',
        'metode_pembayaran',
        'tanggal_selesai',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'jenis_pakaian' => 'array',
        'berat_kg' => 'decimal:2',
        'harga_per_kg' => 'integer',
        'subtotal' => 'integer',
        'diskon' => 'integer',
        'total' => 'integer',
    ];

    /**
     * Relasi ke Kasir (User)
     */
    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    /**
     * Relasi ke Pelanggan
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    /**
     * Relasi ke Layanan
     */
    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }
}
