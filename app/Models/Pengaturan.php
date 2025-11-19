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

    // * Ambil nilai setting berdasarkan key dengan auto casting
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    // * Set nilai setting dengan auto detect type
    public static function setValue(string $key, mixed $value, ?string $type = null, ?string $group = 'general', ?string $deskripsi = null): self
    {
        $type = $type ?? static::detectType($value);
        $storedValue = is_array($value) ? json_encode($value) : (string) $value;

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'type' => $type,
                'group' => $group ?? 'general',
                'deskripsi' => $deskripsi,
            ]
        );
    }

    // * Cast nilai sesuai tipe data
    protected static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? (str_contains($value, '.') ? (float) $value : (int) $value) : $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    // * Deteksi tipe data dari nilai
    protected static function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) || is_float($value) => 'number',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    // * Ambil semua setting dalam satu group
    public static function getByGroup(string $group): \Illuminate\Support\Collection
    {
        return static::where('group', $group)->get()->mapWithKeys(function ($setting) {
            return [$setting->key => static::castValue($setting->value, $setting->type)];
        });
    }
}
