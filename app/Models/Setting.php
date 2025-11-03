<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'deskripsi',
    ];

    /**
     * Helper untuk get setting by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Helper untuk set setting by key
     */
    public static function set($key, $value, $deskripsi = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'deskripsi' => $deskripsi,
            ]
        );
    }
}
