<?php

declare(strict_types=1);

namespace App\Helper;

use App\Helper\Database\PelangganHelper;
use App\Models\ConversationSession;
use App\Models\Pelanggan;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConversationHelper
{
    /**
     * Process incoming message and handle conversation flow
     *
     * @return array{should_use_ai: bool, response: string|null, session: ConversationSession|null}
     */
    public static function processMessage(string $phoneNumber, string $message, string $messageType = 'text'): array
    {
        // Check if customer already exists
        $existingCustomer = self::findCustomerByPhone($phoneNumber);

        if ($existingCustomer) {
            // Customer exists, use normal AI flow
            return [
                'should_use_ai' => true,
                'response' => null,
                'session' => null,
            ];
        }

        // New customer - check if they have active session
        $session = ConversationSession::getOrCreateSession($phoneNumber, 'registration');

        // Handle location message (latitude, longitude from WhatsApp)
        if ($messageType === 'location' || self::isLocationMessage($message)) {
            return self::handleLocationMessage($session, $message);
        }

        // Handle registration flow based on current step
        return self::handleRegistrationFlow($session, $message);
    }

    /**
     * Handle registration conversation flow
     */
    protected static function handleRegistrationFlow(ConversationSession $session, string $message): array
    {
        return match ($session->current_step) {
            'ask_name' => self::handleNameStep($session, $message),
            'ask_intent' => self::handleIntentStep($session, $message),
            'ask_detail_alamat' => self::handleDetailAlamatStep($session, $message),
            'ask_location' => self::handleAskLocationStep($session, $message),
            'confirm_data' => self::handleConfirmDataStep($session, $message),
            // Skip kelurahan and kecamatan steps (kept for backward compatibility)
            'ask_kelurahan', 'ask_kecamatan' => [
                'should_use_ai' => false,
                'response' => 'Maaf, terjadi kesalahan. Silakan mulai ulang dengan mengirim "Halo" 😊',
                'session' => $session,
            ],
            default => [
                'should_use_ai' => false,
                'response' => 'Maaf, terjadi kesalahan. Silakan mulai ulang dengan mengirim "Halo" 😊',
                'session' => $session,
            ],
        };
    }

    /**
     * Step 1: Ask for name
     */
    protected static function handleNameStep(ConversationSession $session, string $message): array
    {
        // If this is first interaction, ask for name
        if (empty($session->getData('greeted'))) {
            $session->updateData(['greeted' => true]);

            return [
                'should_use_ai' => false,
                'response' => "Hai kak! 😊 Selamat datang di Aktif Laundry ✨\n\nBoleh tau nama kakak siapa ya?",
                'session' => $session,
            ];
        }

        // Extract name from message
        $extractedName = self::extractNameFromMessage($message);

        // Save name and ask intent
        $session->updateData(['nama' => $extractedName], 'ask_intent');

        return [
            'should_use_ai' => false,
            'response' => "Senang berkenalan dengan Kak {$extractedName}! 😊\n\nAda yang bisa aku bantu hari ini?\n\n1️⃣ Tanya-tanya dulu (info layanan, harga, promo)\n2️⃣ Mau pesan laundry\n\nKetik nomor atau pilihan kakak ya! 💙",
            'session' => $session,
        ];
    }

    /**
     * Step 2: Ask intent (inquiry or order)
     */
    protected static function handleIntentStep(ConversationSession $session, string $message): array
    {
        $normalizedMessage = strtolower(trim($message));

        // Check if wants to order
        if (str_contains($normalizedMessage, '2') ||
            str_contains($normalizedMessage, 'pesan') ||
            str_contains($normalizedMessage, 'order') ||
            str_contains($normalizedMessage, 'laundry')) {
            // Start registration flow
            $session->updateData(['intent' => 'order'], 'ask_detail_alamat');

            return [
                'should_use_ai' => false,
                'response' => "Oke siap! Kita butuh info alamat kakak dulu ya biar kurir bisa jemput cucian 🚗\n\n📝 *Step 1: Tulis Alamat Lengkap*\nContoh: Jl. Merdeka No. 123, RT 01/RW 02, Dekat Masjid Al-Ikhlas\n\n📍 *Step 2: Share Lokasi*\nNanti setelah kakak kirim alamat, tolong share lokasi juga ya biar lebih akurat! 😊",
                'session' => $session,
            ];
        }

        // Wants to inquiry - complete registration but allow AI to answer
        $session->updateData(['intent' => 'inquiry']);

        // Create basic customer record
        try {
            self::createBasicCustomer($session);
            $session->markCompleted();

            return [
                'should_use_ai' => true, // Now use AI to answer questions
                'response' => null,
                'session' => $session,
            ];
        } catch (Exception $e) {
            Log::error('Failed to create basic customer', [
                'session' => $session->toArray(),
                'error' => $e->getMessage(),
            ]);

            return [
                'should_use_ai' => false,
                'response' => 'Baik kak! Silakan tanya apa aja tentang layanan, harga, atau promo kami ya! 😊 Aku siap bantu! 💙',
                'session' => $session,
            ];
        }
    }

    /**
     * Step 3: Ask for detail alamat
     */
    protected static function handleDetailAlamatStep(ConversationSession $session, string $message): array
    {
        $session->updateData(['detail_alamat' => trim($message)], 'ask_location');

        return [
            'should_use_ai' => false,
            'response' => "Oke, alamat sudah dicatat! 📝\n\nSekarang tolong share lokasi kakak ya biar lebih akurat 📍\n\nCaranya:\n1. Klik ikon 📎 (attach) di WhatsApp\n2. Pilih \"Lokasi\"\n3. Pilih lokasi kakak atau \"Lokasi Saat Ini\"\n4. Kirim ke sini\n\nAtau bisa juga kirim link Google Maps lokasi kakak! 🗺️",
            'session' => $session,
        ];
    }

    /**
     * Step 4: Handle location share (reminder to send location)
     */
    protected static function handleAskLocationStep(ConversationSession $session, string $incomingMessage): array
    {
        return [
            'should_use_ai' => false,
            'response' => "Aku belum terima lokasi kakak nih 😅\n\nTolong share lokasi dengan:\n1. Klik ikon 📎 di WhatsApp\n2. Pilih \"Lokasi\"\n3. Pilih lokasi yang sesuai\n4. Kirim ke sini\n\nAtau kirim link Google Maps juga bisa ya! 🗺️",
            'session' => $session,
        ];
    }

    /**
     * Handle location message (from WhatsApp location or Google Maps link)
     */
    protected static function handleLocationMessage(ConversationSession $session, string $message): array
    {
        // Extract coordinates
        $coordinates = self::extractCoordinates($message);

        if (! $coordinates) {
            return [
                'should_use_ai' => false,
                'response' => "Maaf kak, aku gagal baca koordinat lokasinya 😅\n\nCoba share lokasi lagi atau kirim link Google Maps ya!",
                'session' => $session,
            ];
        }

        // Save coordinates and set default regional data
        $session->updateData([
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'kelurahan' => '', // Empty for now
            'kecamatan' => '', // Empty for now
            'kabupaten_kota' => RegionalLocation::getRegencyName(),
            'provinsi' => RegionalLocation::getProvinceName(),
        ], 'confirm_data');

        // Format confirmation message
        $nama = $session->getData('nama');
        $detailAlamat = $session->getData('detail_alamat');
        $kabupatenKota = $session->getData('kabupaten_kota');
        $provinsi = $session->getData('provinsi');

        $confirmMessage = "Perfect! Coba cek data kakak ya:\n\n";
        $confirmMessage .= "👤 Nama: {$nama}\n";
        $confirmMessage .= "📍 Alamat: {$detailAlamat}\n";
        $confirmMessage .= "🗺️ Lokasi: {$kabupatenKota}, {$provinsi}\n\n";
        $confirmMessage .= "Sudah benar? Ketik:\n";
        $confirmMessage .= "✅ YA - untuk konfirmasi\n";
        $confirmMessage .= '❌ TIDAK - untuk ulang dari awal';

        return [
            'should_use_ai' => false,
            'response' => $confirmMessage,
            'session' => $session,
        ];
    }

    /**
     * Step 5: Ask for kelurahan
     */
    protected static function handleKelurahanStep(ConversationSession $session, string $message): array
    {
        $session->updateData(['kelurahan' => trim($message)], 'ask_kecamatan');

        // Get kecamatan options
        $kecamatanOptions = RegionalLocation::getDistrictOptions(RegionalLocation::getRegencyName());
        $kecamatanList = collect($kecamatanOptions)->pluck('label')->take(5)->implode("\n");

        return [
            'should_use_ai' => false,
            'response' => "Oke! Sekarang tolong pilih Kecamatan kakak:\n\n{$kecamatanList}\n\nKetik nama kecamatan kakak ya!",
            'session' => $session,
        ];
    }

    /**
     * Step 6: Ask for kecamatan
     */
    protected static function handleKecamatanStep(ConversationSession $session, string $message): array
    {
        $session->updateData([
            'kecamatan' => trim($message),
            'kabupaten_kota' => RegionalLocation::getRegencyName(),
            'provinsi' => RegionalLocation::getProvinceName(),
        ], 'confirm_data');

        // Format confirmation message
        $nama = $session->getData('nama');
        $detailAlamat = $session->getData('detail_alamat');
        $kelurahan = $session->getData('kelurahan');
        $kecamatan = $session->getData('kecamatan');
        $kabupatenKota = $session->getData('kabupaten_kota');
        $provinsi = $session->getData('provinsi');

        $confirmMessage = "Perfect! Coba cek data kakak ya:\n\n";
        $confirmMessage .= "👤 Nama: {$nama}\n";
        $confirmMessage .= "📍 Alamat: {$detailAlamat}, {$kelurahan}, {$kecamatan}, {$kabupatenKota}, {$provinsi}\n\n";
        $confirmMessage .= "Sudah benar? Ketik:\n";
        $confirmMessage .= "✅ YA - untuk konfirmasi\n";
        $confirmMessage .= '❌ TIDAK - untuk ulang dari awal';

        return [
            'should_use_ai' => false,
            'response' => $confirmMessage,
            'session' => $session,
        ];
    }

    /**
     * Step 7: Confirm and create customer
     */
    protected static function handleConfirmDataStep(ConversationSession $session, string $message): array
    {
        $normalizedMessage = strtolower(trim($message));

        if (str_contains($normalizedMessage, 'ya') || str_contains($normalizedMessage, 'benar') || str_contains($normalizedMessage, 'iya')) {
            // Create customer
            try {
                $pelanggan = self::createFullCustomer($session);
                $session->markCompleted();

                return [
                    'should_use_ai' => false,
                    'response' => "Yeay! Data kakak sudah tersimpan 🎉\n\nSelamat datang di keluarga besar Aktif Laundry, Kak {$pelanggan->nama}! 💙\n\nSekarang kakak sudah bisa pesan laundry kapan aja. Ada yang mau ditanyakan? Aku siap bantu! 😊",
                    'session' => $session,
                ];
            } catch (Exception $e) {
                Log::error('Failed to create customer', [
                    'session' => $session->toArray(),
                    'error' => $e->getMessage(),
                ]);

                return [
                    'should_use_ai' => false,
                    'response' => "Maaf kak, terjadi error saat menyimpan data 😅\n\nCoba ulang nanti ya atau hubungi admin kami. Terima kasih! 🙏",
                    'session' => $session,
                ];
            }
        }

        if (str_contains($normalizedMessage, 'tidak') || str_contains($normalizedMessage, 'batal')) {
            // Reset session
            $session->markCancelled();

            return [
                'should_use_ai' => false,
                'response' => "Oke kak! Kalo mau daftar lagi nanti, kirim pesan \"Halo\" aja ya! 😊\n\nAda yang bisa aku bantu sekarang?",
                'session' => $session,
            ];
        }

        // Invalid response
        return [
            'should_use_ai' => false,
            'response' => "Maaf kak, aku nggak ngerti 😅\n\nTolong ketik:\n✅ YA - untuk konfirmasi\n❌ TIDAK - untuk ulang",
            'session' => $session,
        ];
    }

    /**
     * Extract coordinates from Google Maps URL or location message
     */
    protected static function extractCoordinates(string $message): ?array
    {
        // Pattern untuk berbagai format Google Maps URL
        $patterns = [
            // Format: @-3.992409,122.517426
            '/@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            // Format: !3d-3.992409!4d122.517426
            '/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/',
            // Format: latitude,longitude (simple)
            '/(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                if (isset($matches[1], $matches[2])) {
                    return [
                        'latitude' => $matches[1],
                        'longitude' => $matches[2],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Check if message contains location data
     */
    protected static function isLocationMessage(string $message): bool
    {
        return str_contains($message, 'maps.google') ||
            str_contains($message, 'goo.gl/maps') ||
            str_contains($message, '@') && str_contains($message, ',') ||
            preg_match('/-?\d+\.?\d*,\s*-?\d+\.?\d*/', $message);
    }

    /**
     * Extract name from user message
     */
    protected static function extractNameFromMessage(string $message): string
    {
        $message = trim($message);

        // Remove common prefixes in Indonesian
        $patterns = [
            '/^nama\s+saya\s+/ui',      // "Nama saya Denis"
            '/^nama\s+aku\s+/ui',        // "Nama aku Denis"
            '/^saya\s+/ui',              // "Saya Denis"
            '/^aku\s+/ui',               // "Aku Denis"
            '/^namaku\s+/ui',            // "Namaku Denis"
            '/^nama\s+ku\s+/ui',         // "Nama ku Denis"
            '/^panggil\s+aja\s+/ui',    // "Panggil aja Denis"
            '/^panggil\s+saya\s+/ui',   // "Panggil saya Denis"
            '/^panggil\s+aku\s+/ui',    // "Panggil aku Denis"
        ];

        foreach ($patterns as $pattern) {
            $message = preg_replace($pattern, '', $message);
        }

        // Remove common suffixes
        $suffixPatterns = [
            '/\s+kak$/ui',               // "Denis kak"
            '/\s+aja$/ui',               // "Denis aja"
            '/\s+aja\s+kak$/ui',        // "Denis aja kak"
            '/\s+ya$/ui',                // "Denis ya"
            '/\s+nih$/ui',               // "Denis nih"
            '/\s+dong$/ui',              // "Denis dong"
        ];

        foreach ($suffixPatterns as $pattern) {
            $message = preg_replace($pattern, '', $message);
        }

        // Clean up and capitalize first letter of each word
        $name = trim($message);
        $name = ucwords(strtolower($name));

        // If name is too long (more than 50 chars), take first 50 chars
        if (strlen($name) > 50) {
            $name = substr($name, 0, 50);
        }

        // If name is empty after cleaning, return original message (fallback)
        if (empty($name)) {
            return ucwords(strtolower(trim($message)));
        }

        return $name;
    }

    /**
     * Find customer by phone number
     */
    protected static function findCustomerByPhone(string $phoneNumber): ?Pelanggan
    {
        return ChatbotHelper::getCustomerByPhone($phoneNumber);
    }

    /**
     * Create basic customer (name + phone only) for inquiry
     */
    protected static function createBasicCustomer(ConversationSession $session): Pelanggan
    {
        $normalizedPhone = PhoneNumber::normalize($session->phone_number);

        if (! $normalizedPhone) {
            throw new Exception('Invalid phone number format');
        }

        return DB::transaction(function () use ($session, $normalizedPhone) {
            return PelangganHelper::createPelanggan([
                'nama' => $session->getData('nama'),
                'no_hp' => $normalizedPhone,
                'email' => null,
                'password' => null,
                'detail_alamat' => 'Belum diisi',
                'kelurahan' => '',
                'kecamatan' => '',
                'kabupaten_kota' => RegionalLocation::getRegencyName(),
                'provinsi' => RegionalLocation::getProvinceName(),
                'latitude' => '-3.9778',
                'longitude' => '122.5145',
                'avatar_url' => null,
            ]);
        });
    }

    /**
     * Create full customer with complete address
     */
    protected static function createFullCustomer(ConversationSession $session): Pelanggan
    {
        $normalizedPhone = PhoneNumber::normalize($session->phone_number);

        if (! $normalizedPhone) {
            throw new Exception('Invalid phone number format');
        }

        return DB::transaction(function () use ($session, $normalizedPhone) {
            return PelangganHelper::createPelanggan([
                'nama' => $session->getData('nama'),
                'no_hp' => $normalizedPhone,
                'email' => null,
                'password' => null,
                'detail_alamat' => $session->getData('detail_alamat'),
                'kelurahan' => $session->getData('kelurahan'),
                'kecamatan' => $session->getData('kecamatan'),
                'kabupaten_kota' => $session->getData('kabupaten_kota'),
                'provinsi' => $session->getData('provinsi'),
                'latitude' => $session->getData('latitude'),
                'longitude' => $session->getData('longitude'),
                'avatar_url' => null,
            ]);
        });
    }
}
