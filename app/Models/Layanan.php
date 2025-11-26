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
// ? Semua data sudah disimpan di kolom terpisah

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
        'satuan',
        'harga_per_kg',
        'harga_per_satuan',
        'durasi_jam',
        'deskripsi',
        'status',
        // Detail fields
        'min_order',
        'max_order',
        'is_popular',
        'icon',
        // JSON fields
        'include',
        'exclude',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'harga_per_kg' => 'integer',
            'harga_per_satuan' => 'integer',
            'durasi_jam' => 'integer',
            'min_order' => 'integer',
            'max_order' => 'integer',
            'is_popular' => 'boolean',
            'include' => 'array',
            'exclude' => 'array',
        ];
    }

    // * Relasi: Detail transaksi yang menggunakan layanan ini
    public function transaksiLayanan(): HasMany
    {
        return $this->hasMany(TransaksiLayanan::class, 'layanan_id');
    }
}
