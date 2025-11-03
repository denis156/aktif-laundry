<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisPakaian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jenis_pakaian';

    protected $fillable = [
        'kode_jenis',
        'nama_jenis',
        'keterangan',
        'status',
    ];
}
