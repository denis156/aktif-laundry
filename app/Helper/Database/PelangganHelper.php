<?php

declare(strict_types=1);

namespace App\Helper\Database;

use App\Models\Pelanggan;
use App\Models\Pengaturan;
use App\Helper\PhoneNumber;
use App\Helper\AddressMetadata;
use App\Helper\RegionalLocation;

// ! Helper untuk mengelola data Pelanggan
//
// ? Metadata yang disimpan:
// * - detail_alamat: Alamat detail (jalan, nomor, RT/RW)
// * - kelurahan: Kelurahan/Desa
// * - kecamatan: Kecamatan
// * - kabupaten_kota: Kabupaten/Kota
// * - provinsi: Provinsi
// * - latitude: Koordinat GPS latitude
// * - longitude: Koordinat GPS longitude
// * - member_card: Nomor kartu member
// * - loyalty_points: Poin loyalty pelanggan
// * - preferensi_pengiriman: Pilihan antar_jemput atau ambil_sendiri

class PelangganHelper
{
    public const META_MEMBER_CARD = 'member_card';
    public const META_LOYALTY_POINTS = 'loyalty_points';
    public const META_PREFERENSI_PENGIRIMAN = 'preferensi_pengiriman';

    // * Ambil nilai dari metadata
    public static function getMetadata(Pelanggan $pelanggan, string $key, mixed $default = null): mixed
    {
        return data_get($pelanggan->metadata, $key, $default);
    }

    // * Set nilai ke metadata
    public static function setMetadata(Pelanggan $pelanggan, string $key, mixed $value): void
    {
        $metadata = $pelanggan->metadata ?? [];
        data_set($metadata, $key, $value);
        $pelanggan->metadata = $metadata;
    }

    // * Merge data ke metadata
    public static function mergeMetadata(Pelanggan $pelanggan, array $data): void
    {
        $pelanggan->metadata = array_merge($pelanggan->metadata ?? [], $data);
    }

    // * Ambil total poin loyalty pelanggan
    public static function getLoyaltyPoints(Pelanggan $pelanggan): int
    {
        return (int) self::getMetadata($pelanggan, self::META_LOYALTY_POINTS, 0);
    }

    // * Set total poin loyalty pelanggan
    public static function setLoyaltyPoints(Pelanggan $pelanggan, int $points): void
    {
        self::setMetadata($pelanggan, self::META_LOYALTY_POINTS, $points);
    }

    // * Tambah poin loyalty (reward, cashback, dll)
    public static function addLoyaltyPoints(Pelanggan $pelanggan, int $points): void
    {
        $current = self::getLoyaltyPoints($pelanggan);
        self::setLoyaltyPoints($pelanggan, $current + $points);
    }

    // ! Rules validasi untuk metadata
    public static function metadataRules(): array
    {
        return [
            self::META_MEMBER_CARD => 'nullable|string|max:50',
            self::META_LOYALTY_POINTS => 'nullable|integer|min:0',
            self::META_PREFERENSI_PENGIRIMAN => 'nullable|in:antar_jemput,ambil_sendiri',
        ];
    }

    // * Generate kode pelanggan unik
    public static function generateKodePelanggan(): string
    {
        $prefix = Pengaturan::getValue('format_id_pelanggan', 'PLG');
        $prefixLength = strlen($prefix);

        $lastPelanggan = Pelanggan::withTrashed()->orderBy('kode_pelanggan', 'desc')->first();

        if (!$lastPelanggan) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastPelanggan->kode_pelanggan, $prefixLength);
        $nextNumber = $lastNumber + 1;

        // Check if there are any gaps in the numbering
        while (Pelanggan::where('kode_pelanggan', $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
            $nextNumber++;
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // * Create pelanggan baru dengan address metadata dan GPS
    public static function createPelanggan(array $data, ?string $tanggalDaftar = null): Pelanggan
    {
        // Normalize nomor HP
        $normalizedPhone = PhoneNumber::normalize($data['no_hp'] ?? '');
        if (!$normalizedPhone) {
            throw new \Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
        }

        // Generate metadata alamat dengan GPS
        $addressMetadata = AddressMetadata::generate(
            $data['detail_alamat'] ?? '',
            $data['kelurahan'] ?? '',
            $data['kecamatan'] ?? '',
            $data['kabupaten_kota'] ?? RegionalLocation::getRegencyName(),
            $data['provinsi'] ?? RegionalLocation::getProvinceName(),
            isset($data['latitude']) ? (float) $data['latitude'] : null,
            isset($data['longitude']) ? (float) $data['longitude'] : null
        );

        // Alamat lengkap gabungan untuk kolom alamat (text)
        $alamatLengkap = AddressMetadata::buildAlamatFromArray($data);

        // Create pelanggan dengan metadata saja (tidak ada kolom regional terpisah)
        return Pelanggan::create([
            'kode_pelanggan' => self::generateKodePelanggan(),
            'nama' => $data['nama'],
            'no_hp' => $normalizedPhone,
            'email' => $data['email'] ?? null,
            'alamat' => $alamatLengkap,
            'tanggal_daftar' => $tanggalDaftar ?? now(),
            'total_transaksi' => 0,
            'status' => 'Aktif',
            'metadata' => $addressMetadata,
        ]);
    }

    // * Get pelanggan options untuk dropdown (with search)
    public static function getPelangganOptions(string $search = '', int $limit = 10): array
    {
        return Pelanggan::where('status', 'Aktif')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('nama', 'like', "%{$search}%")
                          ->orWhere('no_hp', 'like', "%{$search}%");
                }
            })
            ->take($limit)
            ->orderBy('nama')
            ->get()
            ->map(fn ($p) => [
                'id' => (string) $p->id,
                'nama' => $p->nama,
                'no_hp' => PhoneNumber::formatLocal($p->no_hp) ?? $p->no_hp,
            ])
            ->toArray();
    }
}
