<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// ! Model TransaksiPromo - Promo Applied to Transaction
//
// ? Menyimpan snapshot promo yang digunakan dalam transaksi (audit trail)
// ? Mendukung multiple promo per transaksi dengan urutan apply
// ? Semua data sudah disimpan di kolom terpisah

class TransaksiPromo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'transaksi_promo';

    // * Fillable attributes
    protected $fillable = [
        'transaksi_id',
        'promo_id',
        'layanan_id',
        // Snapshot data promo
        'kode_promo',
        'nama_promo',
        'tipe_diskon',
        'nilai_diskon_persen',
        'nilai_diskon_nominal',
        'diskon_maksimal',
        'gratis_kg',
        'gratis_hari',
        // Applied settings
        'diterapkan_ke',
        'urutan_apply',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'transaksi_id' => 'integer',
            'promo_id' => 'integer',
            'layanan_id' => 'integer',
            'nilai_diskon_persen' => 'integer',
            'nilai_diskon_nominal' => 'integer',
            'diskon_maksimal' => 'integer',
            'gratis_kg' => 'integer',
            'gratis_hari' => 'integer',
            'urutan_apply' => 'integer',
        ];
    }

    // * Relasi: Transaksi induk
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    // * Relasi: Master promo yang digunakan
    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class, 'promo_id');
    }

    // * Relasi: Layanan tertentu jika promo khusus untuk 1 layanan
    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }
}
