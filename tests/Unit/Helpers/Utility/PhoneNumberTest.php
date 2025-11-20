<?php

declare(strict_types=1);

use App\Helper\PhoneNumber;

describe('PhoneNumber - Normalize', function () {
    test('normalizes phone with +62 prefix', function () {
        $result = PhoneNumber::normalize('+62812345678');

        expect($result)->toBe('812345678');
    });

    test('normalizes phone with 62 prefix', function () {
        $result = PhoneNumber::normalize('62812345678');

        expect($result)->toBe('812345678');
    });

    test('normalizes phone with 0 prefix', function () {
        $result = PhoneNumber::normalize('0812345678');

        expect($result)->toBe('812345678');
    });

    test('normalizes phone starting with 8', function () {
        $result = PhoneNumber::normalize('812345678');

        expect($result)->toBe('812345678');
    });

    test('removes spaces and dashes', function () {
        $result = PhoneNumber::normalize('0812-3456-789');

        expect($result)->toBe('8123456789');
    });

    test('removes parentheses and other characters', function () {
        $result = PhoneNumber::normalize('(0812) 345-6789');

        expect($result)->toBe('8123456789');
    });

    test('returns null for invalid format not starting with 8', function () {
        $result = PhoneNumber::normalize('712345678');

        expect($result)->toBeNull();
    });

    test('returns null for empty string', function () {
        $result = PhoneNumber::normalize('');

        expect($result)->toBeNull();
    });

    test('returns null for null input', function () {
        $result = PhoneNumber::normalize(null);

        expect($result)->toBeNull();
    });

    test('returns null for non-numeric string after cleaning', function () {
        $result = PhoneNumber::normalize('abc');

        expect($result)->toBeNull();
    });
});

describe('PhoneNumber - Format International', function () {
    test('formats to international with +62', function () {
        $result = PhoneNumber::formatInternational('0812345678');

        expect($result)->toBe('+62812345678');
    });

    test('returns null for invalid number', function () {
        $result = PhoneNumber::formatInternational('712345678');

        expect($result)->toBeNull();
    });
});

describe('PhoneNumber - Format Local', function () {
    test('formats to local with 0 prefix', function () {
        $result = PhoneNumber::formatLocal('812345678');

        expect($result)->toBe('0812345678');
    });

    test('formats from international to local', function () {
        $result = PhoneNumber::formatLocal('+62812345678');

        expect($result)->toBe('0812345678');
    });

    test('returns null for invalid number', function () {
        $result = PhoneNumber::formatLocal('712345678');

        expect($result)->toBeNull();
    });
});

describe('PhoneNumber - Format Readable', function () {
    test('formats with dashes for better readability', function () {
        $result = PhoneNumber::formatReadable('0812345678');

        expect($result)->toContain('0812-345-678');
    });

    test('formats international readable', function () {
        $result = PhoneNumber::formatReadable('0812345678', true);

        expect($result)->toStartWith('+62');
        expect($result)->toContain('-');
    });

    test('handles different length numbers', function () {
        $result = PhoneNumber::formatReadable('08123456789');

        expect($result)->toContain('-');
    });

    test('returns null for invalid number', function () {
        $result = PhoneNumber::formatReadable('712345678');

        expect($result)->toBeNull();
    });
});

describe('PhoneNumber - Validation', function () {
    test('validates correct phone number', function () {
        expect(PhoneNumber::isValid('0812345678'))->toBeTrue();
        expect(PhoneNumber::isValid('08123456789'))->toBeTrue();
        expect(PhoneNumber::isValid('+62812345678'))->toBeTrue();
    });

    test('rejects phone number too short', function () {
        expect(PhoneNumber::isValid('0812345'))->toBeFalse();
    });

    test('rejects phone number too long', function () {
        expect(PhoneNumber::isValid('081234567890123456'))->toBeFalse();
    });

    test('rejects invalid format', function () {
        expect(PhoneNumber::isValid('712345678'))->toBeFalse();
    });

    test('rejects empty string', function () {
        expect(PhoneNumber::isValid(''))->toBeFalse();
    });

    test('rejects null', function () {
        expect(PhoneNumber::isValid(null))->toBeFalse();
    });
});

describe('PhoneNumber - Get Operator', function () {
    test('detects Telkomsel', function () {
        expect(PhoneNumber::getOperator('0811234567'))->toBe('Telkomsel');
        expect(PhoneNumber::getOperator('0812345678'))->toBe('Telkomsel');
        expect(PhoneNumber::getOperator('0813456789'))->toBe('Telkomsel');
        expect(PhoneNumber::getOperator('0821234567'))->toBe('Telkomsel');
        expect(PhoneNumber::getOperator('0851234567'))->toBe('Telkomsel');
    });

    test('detects Indosat', function () {
        expect(PhoneNumber::getOperator('0814234567'))->toBe('Indosat');
        expect(PhoneNumber::getOperator('0815345678'))->toBe('Indosat');
        expect(PhoneNumber::getOperator('0816456789'))->toBe('Indosat');
        expect(PhoneNumber::getOperator('0855234567'))->toBe('Indosat');
    });

    test('detects XL', function () {
        expect(PhoneNumber::getOperator('0817234567'))->toBe('XL');
        expect(PhoneNumber::getOperator('0818345678'))->toBe('XL');
        expect(PhoneNumber::getOperator('0819456789'))->toBe('XL');
        expect(PhoneNumber::getOperator('0877234567'))->toBe('XL');
    });

    test('detects Axis', function () {
        expect(PhoneNumber::getOperator('0831234567'))->toBe('Axis');
        expect(PhoneNumber::getOperator('0832345678'))->toBe('Axis');
        expect(PhoneNumber::getOperator('0838456789'))->toBe('Axis');
    });

    test('detects Three', function () {
        expect(PhoneNumber::getOperator('0895234567'))->toBe('Three');
        expect(PhoneNumber::getOperator('0896345678'))->toBe('Three');
        expect(PhoneNumber::getOperator('0899456789'))->toBe('Three');
    });

    test('detects Smartfren', function () {
        expect(PhoneNumber::getOperator('0881234567'))->toBe('Smartfren');
        expect(PhoneNumber::getOperator('0885345678'))->toBe('Smartfren');
        expect(PhoneNumber::getOperator('0889456789'))->toBe('Smartfren');
    });

    test('returns Unknown for unrecognized prefix', function () {
        expect(PhoneNumber::getOperator('0870234567'))->toBe('Unknown');
    });

    test('returns null for invalid number', function () {
        expect(PhoneNumber::getOperator('712345678'))->toBeNull();
    });
});

describe('PhoneNumber - URL Generation', function () {
    test('generates WhatsApp URL without message', function () {
        $result = PhoneNumber::getWhatsAppUrl('0812345678');

        expect($result)->toBe('https://wa.me/62812345678');
    });

    test('generates WhatsApp URL with message', function () {
        $result = PhoneNumber::getWhatsAppUrl('0812345678', 'Hello World');

        expect($result)->toStartWith('https://wa.me/62812345678?text=');
        expect($result)->toContain('Hello');
    });

    test('returns null for invalid phone in WhatsApp URL', function () {
        $result = PhoneNumber::getWhatsAppUrl('712345678');

        expect($result)->toBeNull();
    });

    test('generates tel URL', function () {
        $result = PhoneNumber::getTelUrl('0812345678');

        expect($result)->toBe('tel:+62812345678');
    });

    test('returns null for invalid phone in tel URL', function () {
        $result = PhoneNumber::getTelUrl('712345678');

        expect($result)->toBeNull();
    });
});

describe('PhoneNumber - Masking', function () {
    test('masks phone number showing first 3 and last 3 digits', function () {
        $result = PhoneNumber::mask('0812345678');

        expect($result)->toStartWith('0812');
        expect($result)->toEndWith('678');
        expect($result)->toContain('****');
    });

    test('masks phone number in international format', function () {
        $result = PhoneNumber::mask('0812345678', true);

        expect($result)->toStartWith('+62');
        expect($result)->toContain('****');
    });

    test('returns null for invalid number', function () {
        $result = PhoneNumber::mask('712345678');

        expect($result)->toBeNull();
    });
});

describe('PhoneNumber - Sanitize', function () {
    test('sanitizes input keeping only valid characters', function () {
        $result = PhoneNumber::sanitize('0812-345-678 abc !@#');

        expect($result)->toBe('0812-345-678  ');
    });

    test('keeps plus sign and parentheses', function () {
        $result = PhoneNumber::sanitize('+62 (812) 345-678');

        expect($result)->toBe('+62 (812) 345-678');
    });

    test('returns null for empty string', function () {
        $result = PhoneNumber::sanitize('');

        expect($result)->toBeNull();
    });

    test('returns null for null input', function () {
        $result = PhoneNumber::sanitize(null);

        expect($result)->toBeNull();
    });
});

describe('PhoneNumber - Compare', function () {
    test('compares same numbers in different formats as equal', function () {
        expect(PhoneNumber::isSame('0812345678', '+62812345678'))->toBeTrue();
        expect(PhoneNumber::isSame('62812345678', '812345678'))->toBeTrue();
    });

    test('compares different numbers as not equal', function () {
        expect(PhoneNumber::isSame('0812345678', '0823456789'))->toBeFalse();
    });

    test('returns false when one number is invalid', function () {
        expect(PhoneNumber::isSame('0812345678', '712345678'))->toBeFalse();
    });

    test('returns false when both numbers are invalid', function () {
        expect(PhoneNumber::isSame('712345678', '612345678'))->toBeFalse();
    });

    test('returns false for null inputs', function () {
        expect(PhoneNumber::isSame(null, '0812345678'))->toBeFalse();
        expect(PhoneNumber::isSame('0812345678', null))->toBeFalse();
    });
});
