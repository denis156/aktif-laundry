<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaksi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transaksi';

    protected $fillable = [
        'kode_transaksi',
        'tanggal_masuk',
        'kasir_id',
        'pelanggan_id',
        'nama_pelanggan',
        'total_berat',
        'total_item',
        'jumlah_layanan',
        'subtotal',
        'diskon',
        'total',
        'metode_pembayaran',
        'tanggal_selesai',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'total_berat' => 'decimal:2',
        'total_item' => 'integer',
        'jumlah_layanan' => 'integer',
        'subtotal' => 'integer',
        'diskon' => 'integer',
        'total' => 'integer',
    ];

    /**
     * Relasi ke Kasir (User)
     */
    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    /**
     * Relasi ke Pelanggan
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    /**
     * Relasi ke TransaksiLayanan (breakdown layanan)
     */
    public function transaksiLayanan()
    {
        return $this->hasMany(TransaksiLayanan::class);
    }

    /**
     * Helper: Hitung ulang total dari transaksi_layanan
     */
    public function hitungUlangTotal()
    {
        $totalBerat = 0;
        $totalItem = 0;
        $subtotal = 0;

        foreach ($this->transaksiLayanan as $tl) {
            if ($tl->isPerKg()) {
                $totalBerat += $tl->berat_kg;
            } else {
                $totalItem += $tl->jumlah_satuan;
            }
            $subtotal += $tl->subtotal;
        }

        $this->update([
            'total_berat' => $totalBerat,
            'total_item' => $totalItem,
            'jumlah_layanan' => $this->transaksiLayanan()->count(),
            'subtotal' => $subtotal,
            'total' => $subtotal - $this->diskon,
        ]);
    }

    /**
     * Helper: Dapatkan layanan dengan tanggal selesai paling lama
     */
    public function getTanggalSelesaiTerlama()
    {
        $tanggalTerlama = null;

        foreach ($this->transaksiLayanan as $tl) {
            if ($tl->layanan && $tl->layanan->durasi_jam > 0) {
                $tanggalSelesai = \Carbon\Carbon::parse($this->tanggal_masuk)
                    ->addHours($tl->layanan->durasi_jam);

                if (!$tanggalTerlama || $tanggalSelesai > $tanggalTerlama) {
                    $tanggalTerlama = $tanggalSelesai;
                }
            }
        }

        return $tanggalTerlama;
    }
}
