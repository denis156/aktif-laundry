<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

// ! Model Pelanggan - Customer/Member
//
// ? Menyimpan data pelanggan laundry
// ? Dilengkapi dengan wilayah (kelurahan, kecamatan, kabupaten_kota, provinsi)
// ? GPS coordinates dan sistem referral
// ? Metadata: member_card, loyalty_points, preferensi_pengiriman, villages, district, regency, province, detail_alamat

class Pelanggan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pelanggan';

    // * Fillable attributes
    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'no_hp',
        'email',
        'kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'latitude',
        'longitude',
        'tanggal_daftar',
        'total_transaksi',
        'total_belanja',
        'status',
        'kode_referral_dipakai',
        'direferensikan_oleh',
        'metadata',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'tanggal_daftar' => 'date',
            'total_transaksi' => 'integer',
            'total_belanja' => 'integer',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'direferensikan_oleh' => 'integer',
            'metadata' => 'array',
        ];
    }

    // * Relasi: Transaksi milik pelanggan ini
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'pelanggan_id');
    }

    // * Relasi: Kode referral yang dimiliki pelanggan ini
    public function referral(): HasOne
    {
        return $this->hasOne(Referral::class, 'pelanggan_id');
    }

    // * Relasi: Pelanggan yang mereferensikan (referrer)
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'direferensikan_oleh');
    }

    // * Relasi: Pelanggan yang direferensikan oleh pelanggan ini
    public function referrals(): HasMany
    {
        return $this->hasMany(Pelanggan::class, 'direferensikan_oleh');
    }
}
