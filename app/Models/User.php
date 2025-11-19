<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// ! Model User - Pegawai/Admin/Kasir
//
// ? Menyimpan data pengguna sistem (bukan pelanggan)
// ? Metadata: alamat, shift, gaji

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // * Fillable attributes
    protected $fillable = [
        'name',
        'email',
        'no_hp',
        'password',
        'avatar_url',
        'super_admin',
        'email_verified_at',
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
