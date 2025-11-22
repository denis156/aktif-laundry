<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// ! Model Layanan - Jenis Layanan Laundry
//
// ? Menyimpan master data layanan (cuci kering, setrika, dll)
// ? Mendukung pricing per_kg atau per_satuan
// ? Metadata: include, exclude, min_order, max_order, popular, icon, deskripsi_detail

class Layanan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'layanan';

    // * Fillable attributes
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
        'metadata',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'harga_per_kg' => 'integer',
            'harga_per_satuan' => 'integer',
            'durasi_jam' => 'integer',
            'metadata' => 'array',
        ];
    }

    // * Relasi: Detail transaksi yang menggunakan layanan ini
    public function transaksiLayanan(): HasMany
    {
        return $this->hasMany(TransaksiLayanan::class, 'layanan_id');
    }
}
