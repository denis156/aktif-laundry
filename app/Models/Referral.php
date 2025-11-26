<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ! Model Referral - Kode Referral
//
// ? Menyimpan kode referral milik pelanggan
// ? Terhubung dengan Promo untuk reward referrer dan referee
// ? Tracking statistik penggunaan
// ? Semua data sudah disimpan di kolom terpisah

class Referral extends Model
{
    use HasFactory;

    protected $table = 'referral';

    // * Fillable attributes
    protected $fillable = [
        'pelanggan_id',
        'promo_referrer_id',
        'promo_referee_id',
        'kode_referral',
        'total_referral',
        'total_berhasil',
        'campaign_source',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'total_referral' => 'integer',
            'total_berhasil' => 'integer',
        ];
    }

    // * Relasi: Pelanggan pemilik kode referral
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    // * Relasi: Promo untuk referrer (yang mengajak)
    public function promoReferrer(): BelongsTo
    {
        return $this->belongsTo(Promo::class, 'promo_referrer_id');
    }

    // * Relasi: Promo untuk referee (yang diajak)
    public function promoReferee(): BelongsTo
    {
        return $this->belongsTo(Promo::class, 'promo_referee_id');
    }
}
