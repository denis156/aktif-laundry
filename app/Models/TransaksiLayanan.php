<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// ! Model TransaksiLayanan - Transaction Service Detail
//
// ? Menyimpan detail layanan dalam transaksi
// ? Satu transaksi bisa punya banyak layanan (multi-service)
// ? Mendukung per_kg (jenis_pakaian array) atau per_satuan
// ? Metadata: catatan, petugas

class TransaksiLayanan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transaksi_layanan';

    // * Fillable attributes
    protected $fillable = [
        'transaksi_id',
        'layanan_id',
        'nama_layanan',
        'jenis_pakaian',
        'berat_kg',
        'harga_per_kg',
        'jumlah_satuan',
        'harga_per_satuan',
        'subtotal',
        'metadata',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'jenis_pakaian' => 'array',
            'berat_kg' => 'decimal:2',
            'harga_per_kg' => 'integer',
            'jumlah_satuan' => 'integer',
            'harga_per_satuan' => 'integer',
            'subtotal' => 'integer',
            'transaksi_id' => 'integer',
            'layanan_id' => 'integer',
            'metadata' => 'array',
        ];
    }

    // * Relasi: Transaksi induk
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    // * Relasi: Master layanan
    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    // ? Cek apakah layanan per kilogram
    public function isPerKg(): bool
    {
        return $this->berat_kg !== null;
    }

    // ? Cek apakah layanan per satuan
    public function isPerSatuan(): bool
    {
        return $this->jumlah_satuan !== null;
    }

    // * Dapatkan label untuk tampilan
    // * Contoh: "Cuci Kering (3.5 kg)" atau "Setrika (10 pcs)"
    public function getDisplayLabel(): string
    {
        if ($this->isPerKg()) {
            return "{$this->nama_layanan} ({$this->berat_kg} kg)";
        }

        return "{$this->nama_layanan} ({$this->jumlah_satuan} pcs)";
    }

    // * Dapatkan label perhitungan untuk display
    // * Contoh: "3.5 kg × Rp 7.000 - Kemeja (5), Celana (3)"
    public function getPerhitunganLabel(): string
    {
        if ($this->isPerKg()) {
            $jenisPakaian = '';
            if (! empty($this->jenis_pakaian)) {
                $items = collect($this->jenis_pakaian)->map(function ($item) {
                    return "{$item['nama']} ({$item['jumlah']})";
                })->implode(', ');
                $jenisPakaian = " - {$items}";
            }

            return "{$this->berat_kg} kg × Rp ".number_format($this->harga_per_kg, 0, ',', '.').$jenisPakaian;
        }

        return "{$this->jumlah_satuan} pcs × Rp ".number_format($this->harga_per_satuan, 0, ',', '.');
    }
}
