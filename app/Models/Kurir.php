<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\KurirResetPasswordNotification;
use App\Notifications\KurirVerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// ! Model Kurir - Driver/Courier
//
// ? Menyimpan data kurir untuk antar jemput
// ? Extends Authenticatable untuk login di aplikasi courier
// ? Implements MustVerifyEmail untuk email verification
// ? Alamat lengkap disimpan di kolom alamat (auto-generated dari metadata)
// ? Metadata: detail_alamat, kelurahan, kecamatan, kabupaten_kota, provinsi, latitude, longitude, area_coverage, bank_info, emergency_contact

class Kurir extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'kurir';

    // * Fillable attributes
    protected $fillable = [
        'kode_kurir',
        'nama',
        'no_hp',
        'email',
        'email_verified_at',
        'alamat',
        'no_kendaraan',
        'jenis_kendaraan',
        'tanggal_bergabung',
        'status',
        'total_antar',
        'total_jemput',
        'password',
        'device_token',
        'metadata',
    ];

    protected $hidden = [
        'password',
        'device_token',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tanggal_bergabung' => 'date',
            'metadata' => 'array',
            'total_antar' => 'integer',
            'total_jemput' => 'integer',
            'password' => 'hashed',
        ];
    }

    // * Relasi: Semua pengiriman yang ditangani kurir ini
    public function pengiriman(): HasMany
    {
        return $this->hasMany(Pengiriman::class, 'kurir_id');
    }

    // * Relasi: Pengiriman jemput saja
    public function pengirimanJemput(): HasMany
    {
        return $this->pengiriman()->where('tipe', 'Jemput');
    }

    // * Relasi: Pengiriman antar saja
    public function pengirimanAntar(): HasMany
    {
        return $this->pengiriman()->where('tipe', 'Antar');
    }

    /**
     * Send the email verification notification.
     * Override untuk menggunakan notification custom untuk kurir.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new KurirVerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     * Override untuk menggunakan notification custom untuk kurir.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new KurirResetPasswordNotification($token));
    }
}
