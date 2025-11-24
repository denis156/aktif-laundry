<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

// ! Model User - Pegawai/Admin/Kasir
//
// ? Menyimpan data pengguna sistem (bukan pelanggan)
// ? Alamat lengkap disimpan di kolom alamat (auto-generated dari metadata)
// ? Metadata: detail_alamat, kelurahan, kecamatan, kabupaten_kota, provinsi, latitude, longitude, jam_masuk, jam_keluar, gaji, target_bulanan

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
        'alamat',
        'metadata',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'super_admin' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
