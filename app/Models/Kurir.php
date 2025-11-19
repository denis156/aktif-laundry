<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

// ! Model Kurir - Driver/Courier
//
// ? Menyimpan data kurir untuk antar jemput
// ? Extends Authenticatable untuk login di aplikasi courier
// ? Metadata: area_coverage, bank_info, emergency_contact

class Kurir extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'kurir';

    // * Fillable attributes
    protected $fillable = [
        'kode_kurir',
        'nama',
        'no_hp',
        'email',
        'alamat',
        'no_kendaraan',
        'jenis_kendaraan',
        'foto_profil',
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
}
