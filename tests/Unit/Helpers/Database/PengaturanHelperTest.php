<?php

declare(strict_types=1);

use App\Helper\Database\PengaturanHelper;
use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

describe('PengaturanHelper - getValue and setValue', function () {
    test('getValue retrieves string value correctly', function () {
        Pengaturan::create([
            'key' => 'app_name',
            'value' => 'Aktif Laundry',
            'type' => PengaturanHelper::TYPE_STRING,
        ]);

        $result = PengaturanHelper::getValue('app_name');

        expect($result)->toBe('Aktif Laundry');
    });

    test('getValue retrieves number value and casts to int', function () {
        Pengaturan::create([
            'key' => 'max_items',
            'value' => '100',
            'type' => PengaturanHelper::TYPE_NUMBER,
        ]);

        $result = PengaturanHelper::getValue('max_items');

        expect($result)->toBe(100);
        expect($result)->toBeInt();
    });

    test('getValue retrieves number value and casts to float', function () {
        Pengaturan::create([
            'key' => 'tax_rate',
            'value' => '10.5',
            'type' => PengaturanHelper::TYPE_NUMBER,
        ]);

        $result = PengaturanHelper::getValue('tax_rate');

        expect($result)->toBe(10.5);
        expect($result)->toBeFloat();
    });

    test('getValue retrieves boolean value and casts to true', function () {
        Pengaturan::create([
            'key' => 'is_open',
            'value' => '1',
            'type' => PengaturanHelper::TYPE_BOOLEAN,
        ]);

        $result = PengaturanHelper::getValue('is_open');

        expect($result)->toBeTrue();
    });

    test('getValue retrieves boolean value and casts to false', function () {
        Pengaturan::create([
            'key' => 'is_closed',
            'value' => '0',
            'type' => PengaturanHelper::TYPE_BOOLEAN,
        ]);

        $result = PengaturanHelper::getValue('is_closed');

        expect($result)->toBeFalse();
    });

    test('getValue retrieves json value and decodes to array', function () {
        Pengaturan::create([
            'key' => 'config_array',
            'value' => '{"key1":"value1","key2":"value2"}',
            'type' => PengaturanHelper::TYPE_JSON,
        ]);

        $result = PengaturanHelper::getValue('config_array');

        expect($result)->toBeArray();
        expect($result)->toHaveKey('key1');
        expect($result['key1'])->toBe('value1');
    });

    test('getValue returns default when key not found', function () {
        $result = PengaturanHelper::getValue('nonexistent_key', 'default_value');

        expect($result)->toBe('default_value');
    });

    test('getValue returns null when key not found and no default', function () {
        $result = PengaturanHelper::getValue('nonexistent_key');

        expect($result)->toBeNull();
    });

    test('setValue creates new setting with string', function () {
        PengaturanHelper::setValue('app_name', 'Aktif Laundry');

        $setting = Pengaturan::where('key', 'app_name')->first();
        expect($setting)->not->toBeNull();
        expect($setting->value)->toBe('Aktif Laundry');
        expect($setting->type)->toBe(PengaturanHelper::TYPE_STRING);
    });

    test('setValue creates new setting with number', function () {
        PengaturanHelper::setValue('max_items', 100);

        $setting = Pengaturan::where('key', 'max_items')->first();
        expect($setting)->not->toBeNull();
        expect($setting->value)->toBe('100');
        expect($setting->type)->toBe(PengaturanHelper::TYPE_NUMBER);
    });

    test('setValue creates new setting with boolean', function () {
        PengaturanHelper::setValue('is_active', true);

        $setting = Pengaturan::where('key', 'is_active')->first();
        expect($setting)->not->toBeNull();
        expect($setting->type)->toBe(PengaturanHelper::TYPE_BOOLEAN);
    });

    test('setValue creates new setting with array', function () {
        PengaturanHelper::setValue('config', ['key1' => 'value1', 'key2' => 'value2']);

        $setting = Pengaturan::where('key', 'config')->first();
        expect($setting)->not->toBeNull();
        expect($setting->type)->toBe(PengaturanHelper::TYPE_JSON);
    });

    test('setValue updates existing setting', function () {
        Pengaturan::create([
            'key' => 'app_name',
            'value' => 'Old Name',
            'type' => PengaturanHelper::TYPE_STRING,
        ]);

        PengaturanHelper::setValue('app_name', 'New Name');

        $setting = Pengaturan::where('key', 'app_name')->first();
        expect($setting->value)->toBe('New Name');
    });

    test('setValue with explicit type', function () {
        PengaturanHelper::setValue('custom_setting', 'value', PengaturanHelper::TYPE_STRING);

        $setting = Pengaturan::where('key', 'custom_setting')->first();
        expect($setting->type)->toBe(PengaturanHelper::TYPE_STRING);
    });

    test('setValue with group and description', function () {
        PengaturanHelper::setValue('app_name', 'Aktif Laundry', null, 'appearance', 'Application name');

        $setting = Pengaturan::where('key', 'app_name')->first();
        expect($setting->group)->toBe('appearance');
        expect($setting->deskripsi)->toBe('Application name');
    });
});

describe('PengaturanHelper - Type Detection', function () {
    test('detectType returns STRING for string value', function () {
        $result = PengaturanHelper::detectType('hello world');

        expect($result)->toBe(PengaturanHelper::TYPE_STRING);
    });

    test('detectType returns NUMBER for integer value', function () {
        $result = PengaturanHelper::detectType(123);

        expect($result)->toBe(PengaturanHelper::TYPE_NUMBER);
    });

    test('detectType returns NUMBER for float value', function () {
        $result = PengaturanHelper::detectType(123.45);

        expect($result)->toBe(PengaturanHelper::TYPE_NUMBER);
    });

    test('detectType returns BOOLEAN for boolean value', function () {
        $result = PengaturanHelper::detectType(true);

        expect($result)->toBe(PengaturanHelper::TYPE_BOOLEAN);
    });

    test('detectType returns JSON for array value', function () {
        $result = PengaturanHelper::detectType(['key' => 'value']);

        expect($result)->toBe(PengaturanHelper::TYPE_JSON);
    });
});

describe('PengaturanHelper - Cast Value', function () {
    test('castValue casts string type correctly', function () {
        $result = PengaturanHelper::castValue('hello', PengaturanHelper::TYPE_STRING);

        expect($result)->toBe('hello');
    });

    test('castValue casts number to int correctly', function () {
        $result = PengaturanHelper::castValue('100', PengaturanHelper::TYPE_NUMBER);

        expect($result)->toBe(100);
        expect($result)->toBeInt();
    });

    test('castValue casts number to float correctly', function () {
        $result = PengaturanHelper::castValue('100.5', PengaturanHelper::TYPE_NUMBER);

        expect($result)->toBe(100.5);
        expect($result)->toBeFloat();
    });

    test('castValue casts boolean true correctly', function () {
        $result = PengaturanHelper::castValue('1', PengaturanHelper::TYPE_BOOLEAN);

        expect($result)->toBeTrue();
    });

    test('castValue casts boolean false correctly', function () {
        $result = PengaturanHelper::castValue('0', PengaturanHelper::TYPE_BOOLEAN);

        expect($result)->toBeFalse();
    });

    test('castValue casts json correctly', function () {
        $result = PengaturanHelper::castValue('{"key":"value"}', PengaturanHelper::TYPE_JSON);

        expect($result)->toBeArray();
        expect($result['key'])->toBe('value');
    });
});

describe('PengaturanHelper - Get By Group', function () {
    test('getByGroup retrieves all settings in group', function () {
        Pengaturan::create(['key' => 'app_name', 'value' => 'Aktif Laundry', 'type' => PengaturanHelper::TYPE_STRING, 'group' => 'general']);
        Pengaturan::create(['key' => 'app_version', 'value' => '1.0.0', 'type' => PengaturanHelper::TYPE_STRING, 'group' => 'general']);
        Pengaturan::create(['key' => 'theme_color', 'value' => 'blue', 'type' => PengaturanHelper::TYPE_STRING, 'group' => 'appearance']);

        $result = PengaturanHelper::getByGroup('general');

        expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
        expect($result->count())->toBe(2);
        expect($result)->toHaveKey('app_name');
        expect($result)->toHaveKey('app_version');
        expect($result)->not->toHaveKey('theme_color');
    });

    test('getByGroup returns empty collection when no settings in group', function () {
        $result = PengaturanHelper::getByGroup('nonexistent');

        expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
        expect($result->count())->toBe(0);
    });

    test('getByGroup casts values correctly', function () {
        Pengaturan::create(['key' => 'max_items', 'value' => '100', 'type' => PengaturanHelper::TYPE_NUMBER, 'group' => 'limits']);
        Pengaturan::create(['key' => 'is_active', 'value' => '1', 'type' => PengaturanHelper::TYPE_BOOLEAN, 'group' => 'limits']);

        $result = PengaturanHelper::getByGroup('limits');

        expect($result['max_items'])->toBe(100);
        expect($result['is_active'])->toBeTrue();
    });
});

describe('PengaturanHelper - Type Validation', function () {
    test('getAllTypes returns all available types', function () {
        $types = PengaturanHelper::getAllTypes();

        expect($types)->toBeArray();
        expect($types)->toHaveCount(4);
        expect($types)->toContain(PengaturanHelper::TYPE_STRING);
        expect($types)->toContain(PengaturanHelper::TYPE_NUMBER);
        expect($types)->toContain(PengaturanHelper::TYPE_BOOLEAN);
        expect($types)->toContain(PengaturanHelper::TYPE_JSON);
    });

    test('isValidType returns true for valid types', function () {
        expect(PengaturanHelper::isValidType(PengaturanHelper::TYPE_STRING))->toBeTrue();
        expect(PengaturanHelper::isValidType(PengaturanHelper::TYPE_NUMBER))->toBeTrue();
        expect(PengaturanHelper::isValidType(PengaturanHelper::TYPE_BOOLEAN))->toBeTrue();
        expect(PengaturanHelper::isValidType(PengaturanHelper::TYPE_JSON))->toBeTrue();
    });

    test('isValidType returns false for invalid types', function () {
        expect(PengaturanHelper::isValidType('invalid'))->toBeFalse();
        expect(PengaturanHelper::isValidType('array'))->toBeFalse();
        expect(PengaturanHelper::isValidType(''))->toBeFalse();
    });
});
