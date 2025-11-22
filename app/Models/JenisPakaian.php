<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ! Model JenisPakaian - Jenis/Kategori Pakaian
//
// ? Menyimpan master data jenis pakaian (kemeja, celana, dll)
// ? Metadata: penanganan_khusus (untuk pakaian putih atau baju spesial)

class JenisPakaian extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'jenis_pakaian';

    // * Fillable attributes
    protected $fillable = [
        'kode_jenis',
        'nama_jenis',
        'keterangan',
        'status',
        'metadata',
    ];

    // * Casts
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
