<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// ! Model Pengaturan - Application Settings
//
// ? Menyimpan konfigurasi aplikasi dalam bentuk key-value
// ? Mendukung berbagai tipe: string, number, boolean, json
// ? Dilengkapi dengan grouping dan auto type casting

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan';

    // * Fillable attributes
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'deskripsi',
    ];
}
