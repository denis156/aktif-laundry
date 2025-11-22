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
// ? Alamat lengkap disimpan di kolom alamat (auto-generated dari metadata)
// ? Metadata: detail_alamat, kelurahan, kecamatan, kabupaten_kota, provinsi, latitude, longitude, member_card, loyalty_points, preferensi_pengiriman

class Pelanggan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pelanggan';

    // * Fillable attributes
    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'no_hp',
        'email',
        'alamat',
        'password',
        'device_token',
        'tanggal_daftar',
        'total_transaksi',
        'status',
        'kode_referral_dipakai',
        'direferensikan_oleh',
        'metadata',
    ];

    // * Hidden attributes (sensitive data)
    protected $hidden = [
        'password',
        'device_token',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'tanggal_daftar' => 'date',
            'total_transaksi' => 'integer',
            'direferensikan_oleh' => 'integer',
            'password' => 'hashed',
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
