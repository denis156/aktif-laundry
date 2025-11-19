<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// ! Model Pembayaran - Payment Record
//
// ? Menyimpan data pembayaran transaksi
// ? Mendukung berbagai metode pembayaran (Tunai, Transfer, QRIS, dll)
// ? Metadata: bank_pengirim, rekening_pengirim, nama_pengirim, waktu_transfer, verified_by, verified_at

class Pembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembayaran';

    // * Fillable attributes
    protected $fillable = [
        'kode_pembayaran',
        'transaksi_id',
        'jumlah_bayar',
        'kembalian',
        'metode',
        'status',
        'bukti_transfer',
        'tanggal_bayar',
        'verified_at',
        'verified_by',
        'catatan',
        'metadata',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'jumlah_bayar' => 'integer',
            'kembalian' => 'integer',
            'tanggal_bayar' => 'datetime',
            'verified_at' => 'datetime',
            'verified_by' => 'integer',
            'metadata' => 'array',
        ];
    }

    // * Relasi: Transaksi yang dibayar
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    // * Relasi: User yang verifikasi pembayaran
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
