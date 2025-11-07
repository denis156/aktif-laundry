<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiLayanan extends Model
{
    use SoftDeletes;

    protected $table = 'transaksi_layanan';

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
    ];

    protected $casts = [
        'jenis_pakaian' => 'array',
        'berat_kg' => 'decimal:2',
        'harga_per_kg' => 'integer',
        'jumlah_satuan' => 'integer',
        'harga_per_satuan' => 'integer',
        'subtotal' => 'integer',
    ];

    /**
     * Relasi ke Transaksi
     */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    /**
     * Relasi ke Layanan
     */
    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    /**
     * Helper: apakah ini layanan per kg?
     */
    public function isPerKg()
    {
        return $this->berat_kg !== null;
    }

    /**
     * Helper: apakah ini layanan per satuan?
     */
    public function isPerSatuan()
    {
        return $this->jumlah_satuan !== null;
    }

    /**
     * Helper: dapatkan label untuk display
     */
    public function getDisplayLabel()
    {
        if ($this->isPerKg()) {
            return "{$this->nama_layanan} ({$this->berat_kg} kg)";
        } else {
            return "{$this->nama_layanan} ({$this->jumlah_satuan} pcs)";
        }
    }

    /**
     * Helper: dapatkan detail perhitungan
     */
    public function getPerhitunganLabel()
    {
        if ($this->isPerKg()) {
            $jenisPakaian = '';
            if (!empty($this->jenis_pakaian)) {
                $items = collect($this->jenis_pakaian)->map(function($item) {
                    return "{$item['nama']} ({$item['jumlah']})";
                })->implode(', ');
                $jenisPakaian = " - {$items}";
            }
            return "{$this->berat_kg} kg × Rp " . number_format($this->harga_per_kg, 0, ',', '.') . $jenisPakaian;
        } else {
            return "{$this->jumlah_satuan} pcs × Rp " . number_format($this->harga_per_satuan, 0, ',', '.');
        }
    }
}
