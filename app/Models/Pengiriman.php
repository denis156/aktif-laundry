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
// ? Semua data sudah disimpan di kolom terpisah

class Pengiriman extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pengiriman';

    // * Fillable attributes
    protected $fillable = [
        'kode_pengiriman',
        'transaksi_id',
        'kurir_id',
        'tipe',
        // Destination info
        'alamat_tujuan',
        'nama_penerima',
        'no_hp_penerima',
        'latitude',
        'longitude',
        // Timeline
        'jadwal_waktu',
        'waktu_mulai',
        'waktu_selesai',
        // Status
        'status',
        'catatan',
        'foto_bukti',
        // Lokasi pickup
        'lokasi_pickup_latitude',
        'lokasi_pickup_longitude',
        'lokasi_pickup_address',
        // Review
        'review_rating',
        'review_text',
        // Tracking (JSON)
        'tracking',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'jadwal_waktu' => 'datetime',
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'lokasi_pickup_latitude' => 'decimal:8',
            'lokasi_pickup_longitude' => 'decimal:8',
            'review_rating' => 'integer',
            'tracking' => 'array',
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
