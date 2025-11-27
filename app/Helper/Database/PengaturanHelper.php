<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Pengaturan;
use Illuminate\Support\Facades\Log;

// ! Helper untuk mengelola Pengaturan aplikasi
//
// ? Mendukung berbagai tipe: string, number, boolean, json
// ? Dilengkapi dengan grouping dan auto type casting

class PengaturanHelper
{
    // * Type Constants
    public const TYPE_STRING = 'string';

    public const TYPE_NUMBER = 'number';

    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_JSON = 'json';

    /**
     * Ambil nilai setting berdasarkan key dengan auto casting
     *
     * @param  string  $key  Setting key to retrieve
     * @param  mixed  $default  Default value if setting not found
     * @return mixed The casted value or default
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            $setting = Pengaturan::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        } catch (\Exception $e) {
            Log::error('Failed to get Pengaturan value', [
                'key' => $key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Set nilai setting dengan auto detect type
     *
     * @param  string  $key  Setting key
     * @param  mixed  $value  Value to set
     * @param  string|null  $type  Explicit type (auto-detected if null)
     * @param  string|null  $group  Setting group
     * @param  string|null  $deskripsi  Setting description
     * @return Pengaturan The created or updated setting
     *
     * @throws \Exception
     */
    public static function setValue(
        string $key,
        mixed $value,
        ?string $type = null,
        ?string $group = 'general',
        ?string $deskripsi = null
    ): Pengaturan {
        try {
            $type = $type ?? self::detectType($value);
            $storedValue = is_array($value) ? json_encode($value) : (string) $value;

            return Pengaturan::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $storedValue,
                    'type' => $type,
                    'group' => $group ?? 'general',
                    'deskripsi' => $deskripsi,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to set Pengaturan value', [
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Cast nilai sesuai tipe data
     *
     * @param  string  $value  The string value to cast
     * @param  string  $type  The target type
     * @return mixed The casted value
     */
    public static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            self::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_NUMBER => $value === '' ? null : (is_numeric($value) ? (str_contains($value, '.') ? (float) $value : (int) $value) : $value),
            self::TYPE_JSON => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Deteksi tipe data dari nilai
     *
     * @param  mixed  $value  The value to detect type from
     * @return string The detected type
     */
    public static function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => self::TYPE_BOOLEAN,
            is_int($value) || is_float($value) => self::TYPE_NUMBER,
            is_array($value) => self::TYPE_JSON,
            default => self::TYPE_STRING,
        };
    }

    /**
     * Ambil semua setting dalam satu group
     *
     * @param  string  $group  The group name
     * @return \Illuminate\Support\Collection Collection of key-value pairs
     *
     * @throws \Exception
     */
    public static function getByGroup(string $group): \Illuminate\Support\Collection
    {
        try {
            return Pengaturan::where('group', $group)->get()->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            });
        } catch (\Exception $e) {
            Log::error('Failed to get Pengaturan by group', [
                'group' => $group,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get all available types
     */
    public static function getAllTypes(): array
    {
        return [
            self::TYPE_STRING,
            self::TYPE_NUMBER,
            self::TYPE_BOOLEAN,
            self::TYPE_JSON,
        ];
    }

    /**
     * Check apakah type valid
     */
    public static function isValidType(string $type): bool
    {
        return in_array($type, self::getAllTypes(), true);
    }
}
