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
        'harga_per_kg',
        'durasi_jam',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'harga_per_kg' => 'integer',
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
