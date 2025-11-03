<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pelanggan';

    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'no_hp',
        'alamat',
        'email',
        'tanggal_daftar',
        'total_transaksi',
        'status',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'total_transaksi' => 'integer',
    ];

    /**
     * Relasi ke Transaksi
     */
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
