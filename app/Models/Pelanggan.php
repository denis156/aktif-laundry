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
// ? Semua data sudah disimpan di kolom terpisah

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
        'password',
        'fcm_token',
        // Alamat lengkap
        'alamat',
        'detail_alamat',
        'kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'latitude',
        'longitude',
        // Data membership & loyalty
        'tanggal_daftar',
        'status',
        'loyalty_points',
        'member_card',
        'avatar_url',
        // Referral
        'direferensikan_oleh',
    ];

    // * Hidden attributes (sensitive data)
    protected $hidden = [
        'password',
        'fcm_token',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tanggal_daftar' => 'datetime',
            'loyalty_points' => 'integer',
            'direferensikan_oleh' => 'integer',
            'password' => 'hashed',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
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
        $this->notify(new PelangganVerifyEmailNotification);
    }
}
