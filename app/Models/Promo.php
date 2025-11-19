<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ! Model Promo - Kode Promo/Diskon
//
// ? Menyimpan data promo dengan sistem kuota dan validasi periode
// ? Mendukung diskon persen atau nominal
// ? Metadata: layanan_id, exclude_pelanggan_id, banner_image, terms_conditions, auto_apply

class Promo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'promo';

    // * Fillable attributes
    protected $fillable = [
        'kode_promo',
        'nama_promo',
        'deskripsi',
        'tipe_diskon',
        'nilai_diskon',
        'diskon_maksimal',
        'min_transaksi',
        'tanggal_mulai',
        'tanggal_berakhir',
        'kuota_total',
        'kuota_terpakai',
        'max_per_user',
        'berlaku_untuk',
        'status',
        'metadata',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'nilai_diskon' => 'integer',
            'diskon_maksimal' => 'integer',
            'min_transaksi' => 'integer',
            'tanggal_mulai' => 'datetime',
            'tanggal_berakhir' => 'datetime',
            'kuota_total' => 'integer',
            'kuota_terpakai' => 'integer',
            'max_per_user' => 'integer',
            'metadata' => 'array',
        ];
    }

    // ? Cek apakah promo masih valid (aktif, dalam periode, ada kuota)
    public function isValid(): bool
    {
        $now = now();

        return $this->status === 'Aktif'
            && $this->tanggal_mulai <= $now
            && $this->tanggal_berakhir >= $now
            && ($this->kuota_total === null || $this->kuota_terpakai < $this->kuota_total);
    }

    // ? Cek apakah masih ada kuota
    public function hasQuota(): bool
    {
        return $this->kuota_total === null || $this->kuota_terpakai < $this->kuota_total;
    }

    // * Tambah counter penggunaan dan update status jika habis
    public function incrementUsage(): void
    {
        $this->increment('kuota_terpakai');

        if ($this->kuota_total && $this->kuota_terpakai >= $this->kuota_total) {
            $this->update(['status' => 'Habis']);
        }
    }
}
