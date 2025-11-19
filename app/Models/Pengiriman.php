<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// ! Model Pengiriman - Delivery/Pickup
//
// ? Menyimpan data pengiriman (jemput atau antar cucian)
// ? Dilengkapi GPS tracking dan bukti delivery
// ? Metadata: lokasi_pengambilan, tracking, review_customer

class Pengiriman extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengiriman';

    // * Fillable attributes
    protected $fillable = [
        'kode_pengiriman',
        'transaksi_id',
        'kurir_id',
        'tipe',
        'alamat_tujuan',
        'nama_penerima',
        'no_hp_penerima',
        'latitude',
        'longitude',
        'jadwal_waktu',
        'waktu_mulai',
        'waktu_selesai',
        'biaya_antar',
        'jarak_km',
        'status',
        'catatan',
        'foto_bukti',
        'metadata',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'jadwal_waktu' => 'datetime',
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
            'biaya_antar' => 'integer',
            'jarak_km' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'metadata' => 'array',
        ];
    }

    // * Relasi: Transaksi yang dikirim
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    // * Relasi: Kurir yang mengantar
    public function kurir(): BelongsTo
    {
        return $this->belongsTo(Kurir::class, 'kurir_id');
    }
}
