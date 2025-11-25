<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\PelangganResetPasswordNotification;
use App\Notifications\PelangganVerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// ! Model Pelanggan - Customer/Member
//
// ? Menyimpan data pelanggan laundry
// ? Alamat lengkap disimpan di kolom alamat (auto-generated dari metadata)
// ? Metadata: detail_alamat, kelurahan, kecamatan, kabupaten_kota, provinsi, latitude, longitude, member_card, loyalty_points, preferensi_pengiriman

class Pelanggan extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'pelanggan';

    // * Fillable attributes
    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'no_hp',
        'email',
        'email_verified_at',
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
            'email_verified_at' => 'datetime',
            'tanggal_daftar' => 'datetime',
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

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new PelangganResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new PelangganVerifyEmailNotification());
    }
}
