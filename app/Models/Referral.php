<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ! Model Referral - Kode Referral
//
// ? Menyimpan kode referral milik pelanggan
// ? Tracking reward dan statistik penggunaan
// ? Metadata: reward_history, special_reward

class Referral extends Model
{
    use HasFactory;

    protected $table = 'referral';

    // * Fillable attributes
    protected $fillable = [
        'pelanggan_id',
        'kode_referral',
        'poin_referrer',
        'diskon_referee',
        'min_transaksi_referee',
        'total_referral',
        'total_poin',
        'total_berhasil',
        'status',
        'metadata',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'poin_referrer' => 'integer',
            'diskon_referee' => 'integer',
            'min_transaksi_referee' => 'integer',
            'total_referral' => 'integer',
            'total_poin' => 'integer',
            'total_berhasil' => 'integer',
            'metadata' => 'array',
        ];
    }

    // * Relasi: Pelanggan pemilik kode referral
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    // * Tambah counter total referral (saat ada yang pakai kode)
    public function incrementReferral(): void
    {
        $this->increment('total_referral');
    }

    // * Tambah successful referral dan poin (saat referee sudah transaksi)
    public function addSuccessfulReferral(): void
    {
        $this->increment('total_berhasil');
        $this->increment('total_poin', $this->poin_referrer);
    }
}
