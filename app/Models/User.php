<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\Auth\User\ResetPasswordNotification;
use App\Notifications\Auth\User\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// ! Model User - Pegawai/Admin/Kasir
//
// ? Menyimpan data pengguna sistem (bukan pelanggan)
// ? Semua data sudah disimpan di kolom terpisah

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use Notifiable;

    // * Fillable attributes
    protected $fillable = [
        'name',
        'email',
        'no_hp',
        'password',
        'avatar_url',
        'super_admin',
        'email_verified_at',
        'fcm_token',
        // Data Kepegawaian
        'gaji',
        'jam_masuk',
        'jam_keluar',
        // Data Bank
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        // Alamat lengkap
        'alamat',
        'detail_alamat',
        'kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'latitude',
        'longitude',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'fcm_token',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'super_admin' => 'boolean',
            'gaji' => 'integer',
            'jam_masuk' => 'datetime:H:i',
            'jam_keluar' => 'datetime:H:i',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    /**
     * Send the email verification notification.
     * Override untuk menggunakan notification custom untuk user/staf.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    /**
     * Send the password reset notification.
     * Override untuk menggunakan notification custom untuk user/staf.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
