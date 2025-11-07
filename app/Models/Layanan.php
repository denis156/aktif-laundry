<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Layanan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'layanan';

    protected $fillable = [
        'kode_layanan',
        'nama_layanan',
        'tipe_layanan',
        'harga_per_kg',
        'harga_per_satuan',
        'satuan',
        'durasi_jam',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'harga_per_kg' => 'integer',
        'harga_per_satuan' => 'integer',
        'durasi_jam' => 'integer',
    ];

    /**
     * Relasi ke Transaksi
     */
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
