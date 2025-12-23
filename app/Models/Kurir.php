<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\Auth\Kurir\ResetPasswordNotification;
use App\Notifications\Auth\Kurir\VerifyEmailNotification;
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
// ? Semua data sudah disimpan di kolom terpisah

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
        // Vehicle info
        'no_kendaraan',
        'jenis_kendaraan',
        // Profile
        'tanggal_bergabung',
        'status',
        // Data Bank
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        // Avatar
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'fcm_token',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tanggal_bergabung' => 'datetime',
            'password' => 'hashed',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
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
        $this->notify(new VerifyEmailNotification());
    }

    /**
     * Send the password reset notification.
     * Override untuk menggunakan notification custom untuk kurir.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
