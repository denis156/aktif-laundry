<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use App\Helper\Database\LayananHelper;
use App\Helper\Database\PelangganHelper;
use App\Helper\Database\PengaturanHelper;
use App\Helper\Database\PromoHelper;
use App\Helper\Database\TransaksiHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Referral;
use App\Models\Transaksi;
use App\Models\TransaksiLayanan;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Kasir')]
#[Layout('layouts.management.app')]
class Kasir extends Component
{
    use Toast;

    public array $formData = [
        'kode_transaksi' => '',
        'tanggal_masuk' => '',
        'kasir_id' => null,
        'pelanggan_id' => '',
        'nama_pelanggan' => '',
        'promo_id' => null,
        'referral_id' => null,
        'kode_promo' => '',
        'kode_referral' => '',
        'subtotal' => 0,
        'total' => 0,
        'metode_pembayaran' => TransaksiHelper::METODE_BAYAR_SAAT_JEMPUT,
        'tipe_bayar' => null,
        'status_bayar' => TransaksiHelper::STATUS_BELUM_BAYAR,
        'tanggal_bayar' => null,
        'jumlah_bayar' => null,
        'tanggal_selesai' => '',
        'status' => TransaksiHelper::STATUS_MENUNGGU,
        'catatan' => '',
    ];

    // Promo validation result
    public array $promoResult = [
        'valid' => false,
        'diskon' => 0,
        'pesan' => '',
        'tipe' => '',
    ];

    // Cache promo object untuk menghindari duplicate query
    protected $cachedPromo = null;

    // Multi-layanan data
    public array $multiLayananData = [
        'items' => [],
        'totalSubtotal' => 0,
        'totalGrandTotal' => 0,
    ];

    public string $lastTransactionId = '';

    public bool $showReceipt = false;

    public array $pelangganOptions = [];

    // Toggle antara pilih pelanggan existing atau input pelanggan baru
    public bool $isPelangganBaru = false;

    // Form data untuk pelanggan baru
    public array $pelangganBaru = [
        'nama' => '',
        'no_hp' => '',
        'email' => '',
        'detail_alamat' => '',
        'kelurahan' => '',
        'kecamatan' => '',
        'kabupaten_kota' => '',
        'provinsi' => '',
        'latitude' => '',
        'longitude' => '',
    ];

    public string $sharelok = '';

    // Options untuk regional select
    public array $provinsiOptions = [];

    public array $kabupatenKotaOptions = [];

    public array $kecamatanOptions = [];

    public array $kelurahanOptions = [];

    // Listener untuk event dari component
    protected $listeners = ['multiLayananUpdated'];

    public function multiLayananUpdated(array $data): void
    {
        $this->multiLayananData = $data;
        $this->formData['subtotal'] = $data['totalSubtotal'];

        // Recalculate promo diskon jika ada promo yang dipilih
        if ($this->formData['promo_id']) {
            $this->calculatePromoDiskon();
        } else {
            $this->formData['total'] = $data['totalGrandTotal'];
        }

        // Calculate tanggal selesai based on layanan with longest duration
        $this->calculateTanggalSelesaiFromMultiLayanan();
    }

    public function mount(): void
    {
        $this->refreshKodeTransaksi();
        $this->formData['tanggal_masuk'] = now()->format('Y-m-d\TH:i');
        $this->formData['kasir_id'] = Auth::id();

        // Set default kabupaten/kota dan provinsi menggunakan RegionalLocation Helper
        $this->pelangganBaru['kabupaten_kota'] = RegionalLocation::getRegencyName();
        $this->pelangganBaru['provinsi'] = RegionalLocation::getProvinceName();

        // Load regional options
        $this->loadRegionalOptions();

        $this->search();
    }

    private function loadRegionalOptions(): void
    {
        // Load provinsi options (seluruh Indonesia)
        $this->provinsiOptions = RegionalLocation::getProvinceOptions();

        // Load kabupaten/kota options berdasarkan provinsi yang dipilih
        if (! empty($this->pelangganBaru['provinsi'])) {
            $this->kabupatenKotaOptions = RegionalLocation::getRegencyOptions($this->pelangganBaru['provinsi']);
        }

        // Load kecamatan options jika kabupaten/kota sudah dipilih
        if (! empty($this->pelangganBaru['kabupaten_kota'])) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->pelangganBaru['kabupaten_kota']);
        }

        // Load kelurahan options jika kecamatan sudah dipilih
        if (! empty($this->pelangganBaru['kecamatan'])) {
            $this->kelurahanOptions = RegionalLocation::getVillageOptions($this->pelangganBaru['kecamatan']);
        }
    }

    public function updatedPelangganBaruProvinsi(): void
    {
        // Reset dependent fields
        $this->pelangganBaru['kabupaten_kota'] = '';
        $this->pelangganBaru['kecamatan'] = '';
        $this->pelangganBaru['kelurahan'] = '';
        $this->kabupatenKotaOptions = [];
        $this->kecamatanOptions = [];
        $this->kelurahanOptions = [];

        // Load kabupaten/kota options
        if (! empty($this->pelangganBaru['provinsi'])) {
            $this->kabupatenKotaOptions = RegionalLocation::getRegencyOptions($this->pelangganBaru['provinsi']);
        }
    }

    public function updatedPelangganBaruKabupatenKota(): void
    {
        // Reset dependent fields
        $this->pelangganBaru['kecamatan'] = '';
        $this->pelangganBaru['kelurahan'] = '';
        $this->kecamatanOptions = [];
        $this->kelurahanOptions = [];

        // Load kecamatan options
        if (! empty($this->pelangganBaru['kabupaten_kota'])) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->pelangganBaru['kabupaten_kota']);
        }
    }

    public function updatedPelangganBaruKecamatan(): void
    {
        // Reset dependent field
        $this->pelangganBaru['kelurahan'] = '';
        $this->kelurahanOptions = [];

        // Load kelurahan options
        if (! empty($this->pelangganBaru['kecamatan'])) {
            $this->kelurahanOptions = RegionalLocation::getVillageOptions($this->pelangganBaru['kecamatan']);
        }
    }

    protected function resetForm(): void
    {
        $this->formData = [
            'kode_transaksi' => TransaksiHelper::generateKodeTransaksi(),
            'tanggal_masuk' => now()->format('Y-m-d\TH:i'),
            'kasir_id' => Auth::id(),
            'pelanggan_id' => '',
            'nama_pelanggan' => '',
            'promo_id' => null,
            'referral_id' => null,
            'kode_promo' => '',
            'kode_referral' => '',
            'subtotal' => 0,
            'total' => 0,
            'metode_pembayaran' => TransaksiHelper::METODE_BAYAR_SAAT_JEMPUT,
            'tipe_bayar' => null,
            'status_bayar' => TransaksiHelper::STATUS_BELUM_BAYAR,
            'tanggal_bayar' => null,
            'jumlah_bayar' => null,
            'tanggal_selesai' => '',
            'status' => TransaksiHelper::STATUS_MENUNGGU,
            'catatan' => '',
        ];

        $this->promoResult = [
            'valid' => false,
            'diskon' => 0,
            'pesan' => '',
            'tipe' => '',
        ];

        $this->cachedPromo = null;

        $this->multiLayananData = [
            'items' => [],
            'totalSubtotal' => 0,
            'totalGrandTotal' => 0,
        ];

        $this->pelangganBaru = [
            'nama' => '',
            'no_hp' => '',
            'email' => '',
            'detail_alamat' => '',
            'kelurahan' => '',
            'kecamatan' => '',
            'kabupaten_kota' => RegionalLocation::getRegencyName(),
            'provinsi' => RegionalLocation::getProvinceName(),
            'latitude' => '',
            'longitude' => '',
        ];
        $this->sharelok = '';

        $this->isPelangganBaru = false;
    }

    public function refreshKodeTransaksi(): void
    {
        $this->formData['kode_transaksi'] = TransaksiHelper::generateKodeTransaksi();
    }

    public function search(string $term = ''): void
    {
        try {
            // Menggunakan PelangganHelper untuk get options (approach OOP)
            $this->pelangganOptions = PelangganHelper::getPelangganOptions($term, 10);
        } catch (\Exception $e) {
            Log::error('Error searching pelanggan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->pelangganOptions = [];
        }
    }

    public function updatedFormDataPelangganId(mixed $value): void
    {
        if ($value) {
            $pelanggan = Pelanggan::find($value);
            if ($pelanggan instanceof Pelanggan) {
                $this->formData['nama_pelanggan'] = $pelanggan->nama;

                // Auto-fill form pelanggan dengan data yang dipilih
                $this->pelangganBaru['nama'] = $pelanggan->nama;
                $this->pelangganBaru['no_hp'] = PhoneNumber::formatLocal($pelanggan->no_hp)
                    ?? $pelanggan->no_hp;
                $this->pelangganBaru['email'] = $pelanggan->email ?? '';
                $this->pelangganBaru['detail_alamat'] = $pelanggan->detail_alamat ?? '';
                $this->pelangganBaru['kelurahan'] = $pelanggan->kelurahan ?? '';
                $this->pelangganBaru['kecamatan'] = $pelanggan->kecamatan ?? '';
                $this->pelangganBaru['kabupaten_kota'] = $pelanggan->kabupaten_kota
                    ?: RegionalLocation::getRegencyName();
                $this->pelangganBaru['provinsi'] = $pelanggan->provinsi
                    ?: RegionalLocation::getProvinceName();
                $this->pelangganBaru['latitude'] = $pelanggan->latitude ? (string) $pelanggan->latitude : '';
                $this->pelangganBaru['longitude'] = $pelanggan->longitude ? (string) $pelanggan->longitude : '';
            }
        }
    }

    public function updatedIsPelangganBaru(mixed $value): void
    {
        if ($value) {
            // Saat toggle ke mode "Pelanggan Baru", clear form pelanggan
            $this->pelangganBaru = [
                'nama' => '',
                'no_hp' => '',
                'email' => '',
                'detail_alamat' => '',
                'kelurahan' => '',
                'kecamatan' => '',
                'kabupaten_kota' => RegionalLocation::getRegencyName(),
                'provinsi' => RegionalLocation::getProvinceName(),
                'latitude' => '',
                'longitude' => '',
            ];
            $this->sharelok = '';
            // Clear juga pilihan pelanggan
            $this->formData['pelanggan_id'] = '';
            $this->formData['nama_pelanggan'] = '';
        } else {
            // Saat toggle ke mode "Pilih Pelanggan", clear form pelanggan juga
            $this->pelangganBaru = [
                'nama' => '',
                'no_hp' => '',
                'email' => '',
                'detail_alamat' => '',
                'kelurahan' => '',
                'kecamatan' => '',
                'kabupaten_kota' => RegionalLocation::getRegencyName(),
                'provinsi' => RegionalLocation::getProvinceName(),
                'latitude' => '',
                'longitude' => '',
            ];
            $this->sharelok = '';
        }
    }

    public function updatedFormDataPromoId(): void
    {
        $this->calculatePromoDiskon();
    }

    public function updatedFormDataKodeReferral(string $value): void
    {
        $this->applyReferralCode($value);
    }

    public function updatedSharelok(): void
    {
        $this->extractCoordinatesFromUrl();
    }

    private function extractCoordinatesFromUrl(): void
    {
        if (empty($this->sharelok)) {
            return;
        }

        // Pattern untuk berbagai format Google Maps URL
        $patterns = [
            // Format: @-3.992409,122.517426
            '/@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            // Format: !3d-3.992409!4d122.517426
            '/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/',
            // Format: 3°59'32.7"S 122°31'02.7"E (DMS)
            '/(\d+)°(\d+)\'([\d.]+)"([NS])\s+(\d+)°(\d+)\'([\d.]+)"([EW])/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->sharelok, $matches)) {
                if (count($matches) === 9) {
                    // DMS format
                    $lat = $this->convertDMSToDecimal((int) $matches[1], (int) $matches[2], (float) $matches[3], $matches[4]);
                    $lon = $this->convertDMSToDecimal((int) $matches[5], (int) $matches[6], (float) $matches[7], $matches[8]);
                    $this->pelangganBaru['latitude'] = (string) $lat;
                    $this->pelangganBaru['longitude'] = (string) $lon;

                    return;
                } elseif (count($matches) === 3) {
                    // Decimal format
                    $this->pelangganBaru['latitude'] = $matches[1];
                    $this->pelangganBaru['longitude'] = $matches[2];

                    return;
                }
            }
        }
    }

    private function convertDMSToDecimal(int $degrees, int $minutes, float $seconds, string $direction): float
    {
        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if ($direction === 'S' || $direction === 'W') {
            $decimal *= -1;
        }

        return round($decimal, 6);
    }

    protected function applyReferralCode(string $kode): void
    {
        if (empty($kode)) {
            $this->formData['referral_id'] = null;

            return;
        }

        // Cari referral by kode
        $referral = Referral::where('kode_referral', strtoupper($kode))
            ->where('status', 'Aktif')
            ->first();

        if (! $referral) {
            $this->formData['referral_id'] = null;
            $this->warning('Kode referral tidak ditemukan atau tidak aktif', position: 'toast-bottom');

            return;
        }

        // Pastikan tidak menggunakan kode referral sendiri
        if ($this->formData['pelanggan_id'] && $referral->pelanggan_id == $this->formData['pelanggan_id']) {
            $this->formData['referral_id'] = null;
            $this->error('Tidak dapat menggunakan kode referral sendiri', position: 'toast-bottom');

            return;
        }

        $this->formData['referral_id'] = $referral->id;

        // Jika referral punya promo referee dan belum ada promo dipilih, auto-apply
        if ($referral->promo_referee_id && ! $this->formData['promo_id']) {
            $this->formData['promo_id'] = $referral->promo_referee_id;
            $this->calculatePromoDiskon();
        }

        $this->success("Kode referral {$kode} berhasil diterapkan!", position: 'toast-bottom');
    }

    protected function calculatePromoDiskon(): void
    {
        $promoId = $this->formData['promo_id'];

        if (! $promoId) {
            $this->promoResult = [
                'valid' => false,
                'diskon' => 0,
                'pesan' => '',
                'tipe' => '',
            ];
            $this->formData['kode_promo'] = '';
            $this->cachedPromo = null;
            $this->updateTotal();

            return;
        }

        // Convert to integer for type safety
        $promoId = (int) $promoId;

        // Use cached promo if available
        if ($this->cachedPromo && $this->cachedPromo->id === $promoId) {
            $promo = $this->cachedPromo;
        } else {
            $promo = PromoHelper::getById($promoId);
            $this->cachedPromo = $promo;
        }

        if (! $promo) {
            $this->promoResult = [
                'valid' => false,
                'diskon' => 0,
                'pesan' => 'Promo tidak ditemukan',
                'tipe' => '',
            ];
            $this->formData['kode_promo'] = '';
            $this->cachedPromo = null;
            $this->updateTotal();

            return;
        }

        // Hitung total berat dari multiLayananData
        $totalBerat = 0.0;
        foreach ($this->multiLayananData['items'] as $item) {
            if (($item['tipe_layanan'] ?? '') === 'per_kg') {
                $totalBerat += (float) ($item['berat_kg'] ?? 0);
            }
        }

        // Hitung diskon menggunakan PromoHelper
        $pelangganId = $this->formData['pelanggan_id'] ? (int) $this->formData['pelanggan_id'] : null;
        $subtotal = (int) ($this->multiLayananData['totalSubtotal'] ?? 0);

        $this->promoResult = PromoHelper::hitungDiskon($promo, $subtotal, $totalBerat, $pelangganId);

        if ($this->promoResult['valid']) {
            $this->formData['kode_promo'] = $promo->kode_promo;
        } else {
            $this->formData['kode_promo'] = '';
            $this->warning($this->promoResult['pesan'], position: 'toast-bottom');
        }

        $this->updateTotal();
    }

    protected function updateTotal(): void
    {
        $subtotal = (int) ($this->multiLayananData['totalSubtotal'] ?? 0);
        $diskon = (int) ($this->promoResult['diskon'] ?? 0);

        $this->multiLayananData['totalGrandTotal'] = $subtotal - $diskon;
        $this->formData['total'] = $this->multiLayananData['totalGrandTotal'];
        $this->formData['subtotal'] = $subtotal;
    }

    protected function calculateTanggalSelesaiFromMultiLayanan(): void
    {
        // Create temporary transaksi object untuk menggunakan TransaksiHelper
        $tempTransaksi = new Transaksi;
        $tempTransaksi->tanggal_masuk = $this->formData['tanggal_masuk'];
        $tempTransaksi->setRelation('transaksiLayanan', collect($this->multiLayananData['items'])->map(function ($item) {
            if (! empty($item['layanan_id'])) {
                $tempTransaksiLayanan = new TransaksiLayanan;
                $tempTransaksiLayanan->setRelation('layanan', Layanan::find($item['layanan_id']));

                return $tempTransaksiLayanan;
            }

            return null;
        })->filter());

        $tanggalTerlama = TransaksiHelper::getTanggalSelesaiTerlama($tempTransaksi);
        $this->formData['tanggal_selesai'] = $tanggalTerlama ? $tanggalTerlama->format('Y-m-d H:i') : '';
    }

    public function save(): void
    {
        // VALIDASI PELANGGAN BARU (jika mode pelanggan baru)
        if ($this->isPelangganBaru) {
            if (empty($this->pelangganBaru['nama'])) {
                Log::warning('Kasir validation failed: nama pelanggan kosong');
                $this->error('Nama pelanggan wajib diisi!', position: 'toast-bottom');

                return;
            }

            if (empty($this->pelangganBaru['no_hp'])) {
                Log::warning('Kasir validation failed: no_hp pelanggan kosong');
                $this->error('Nomor HP wajib diisi!', position: 'toast-bottom');

                return;
            }

            if (empty($this->pelangganBaru['detail_alamat'])) {
                Log::warning('Kasir validation failed: detail_alamat pelanggan kosong');
                $this->error('Detail alamat wajib diisi!', position: 'toast-bottom');

                return;
            }

            if (empty($this->pelangganBaru['latitude'])) {
                Log::warning('Kasir validation failed: latitude pelanggan kosong');
                $this->error('Latitude wajib diisi!', position: 'toast-bottom');

                return;
            }

            if (empty($this->pelangganBaru['longitude'])) {
                Log::warning('Kasir validation failed: longitude pelanggan kosong');
                $this->error('Longitude wajib diisi!', position: 'toast-bottom');

                return;
            }
        }

        // VALIDASI PELANGGAN EXISTING (jika mode pilih pelanggan)
        if (! $this->isPelangganBaru && empty($this->formData['pelanggan_id'])) {
            Log::warning('Kasir validation failed: pelanggan_id kosong');
            $this->error('Pilih pelanggan terlebih dahulu!', position: 'toast-bottom');

            return;
        }

        // Validasi metode pembayaran
        if (! TransaksiHelper::isValidMetodePembayaran($this->formData['metode_pembayaran'])) {
            Log::warning('Kasir validation failed: metode_pembayaran invalid', [
                'metode_pembayaran' => $this->formData['metode_pembayaran'],
            ]);
            $this->error('Metode pembayaran tidak valid!', position: 'toast-bottom');

            return;
        }

        // Validasi status
        if (! TransaksiHelper::isValidStatus($this->formData['status'])) {
            Log::warning('Kasir validation failed: status invalid', [
                'status' => $this->formData['status'],
            ]);
            $this->error('Status transaksi tidak valid!', position: 'toast-bottom');

            return;
        }

        // Validasi multi-layanan
        if (empty($this->multiLayananData['items'])) {
            Log::warning('Kasir validation failed: items layanan kosong');
            $this->error('Tambahkan minimal 1 layanan!', position: 'toast-bottom');

            return;
        }

        $hasValidLayanan = false;

        // Ambil min_berat_kg dari pengaturan (OOP approach)
        $minBeratKg = (float) PengaturanHelper::getValue('min_berat_kg', 2);

        foreach ($this->multiLayananData['items'] as $index => $item) {
            if (! empty($item['layanan_id'])) {
                if ($item['tipe_layanan'] === 'per_kg') {
                    if (empty($item['berat_kg']) || $item['berat_kg'] < $minBeratKg) {
                        Log::warning('Kasir validation failed: berat_kg invalid', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                            'berat_kg' => $item['berat_kg'] ?? null,
                            'min_berat_kg_required' => $minBeratKg,
                        ]);
                        $this->error("Berat minimal {$minBeratKg} kg untuk layanan ".$item['nama_layanan'].'!', position: 'toast-bottom');

                        return;
                    }
                    if (empty($item['jenis_pakaian']) || count($item['jenis_pakaian']) === 0) {
                        Log::warning('Kasir validation failed: jenis_pakaian kosong', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                        ]);
                        $this->error('Jenis pakaian wajib diisi untuk layanan '.$item['nama_layanan'].'!', position: 'toast-bottom');

                        return;
                    }
                } else {
                    if (empty($item['jumlah_satuan']) || $item['jumlah_satuan'] < 1) {
                        Log::warning('Kasir validation failed: jumlah_satuan invalid', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                            'jumlah_satuan' => $item['jumlah_satuan'] ?? null,
                        ]);
                        $this->error('Jumlah minimal 1 untuk layanan '.$item['nama_layanan'].'!', position: 'toast-bottom');

                        return;
                    }
                }
                $hasValidLayanan = true;
            }
        }

        if (! $hasValidLayanan) {
            Log::warning('Kasir validation failed: tidak ada layanan valid');
            $this->error('Pilih layanan yang valid terlebih dahulu!', position: 'toast-bottom');

            return;
        }

        try {
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function () {
                // SIMPAN PELANGGAN BARU jika mode pelanggan baru (SETELAH semua validasi)
                if ($this->isPelangganBaru) {
                    try {
                        $pelanggan = PelangganHelper::createPelanggan(
                            $this->pelangganBaru,
                            $this->formData['tanggal_masuk'] ? \Carbon\Carbon::parse($this->formData['tanggal_masuk'])->format('Y-m-d H:i:s') : null
                        );

                        // Auto-select pelanggan yang baru ditambahkan
                        $this->formData['pelanggan_id'] = $pelanggan->id;
                        $this->formData['nama_pelanggan'] = $pelanggan->nama;

                        Log::info('Kasir: Pelanggan baru berhasil dibuat', [
                            'pelanggan_id' => $pelanggan->id,
                            'nama' => $pelanggan->nama,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Kasir: Failed to create pelanggan in transaction', [
                            'error' => $e->getMessage(),
                            'pelanggan_data' => $this->pelangganBaru,
                        ]);
                        throw $e; // Re-throw untuk rollback transaction
                    }
                }

                // ID Kasir
                $this->formData['kasir_id'] = Auth::id() ?? 1;

                // Cek ulang apakah kode transaksi sudah ada, jika ya generate ulang
                if (Transaksi::where('kode_transaksi', $this->formData['kode_transaksi'])->exists()) {
                    Log::warning('Kasir: Duplicate kode_transaksi detected, regenerating', [
                        'old_kode' => $this->formData['kode_transaksi'],
                    ]);
                    $this->refreshKodeTransaksi();
                }

                // Simpan transaksi
                $transaksiData = $this->formData;
                $transaksiData['jumlah_layanan'] = count($this->multiLayananData['items']);
                $transaksiData['total_berat'] = 0;
                $transaksiData['total_item'] = 0;

                // Calculate total berat dan total item
                foreach ($this->multiLayananData['items'] as $item) {
                    if ($item['tipe_layanan'] === 'per_kg') {
                        $transaksiData['total_berat'] += (float) ($item['berat_kg'] ?? 0);
                    } else {
                        $transaksiData['total_item'] += (int) ($item['jumlah_satuan'] ?? 0);
                    }
                }

                $transaksi = Transaksi::create($transaksiData);

                // Collect all layanan IDs and load them at once to prevent N+1 queries
                $layananIds = collect($this->multiLayananData['items'])
                    ->pluck('layanan_id')
                    ->filter()
                    ->unique()
                    ->toArray();

                $layananMap = Layanan::whereIn('id', $layananIds)->get()->keyBy('id');

                // Simpan detail transaksi layanan
                foreach ($this->multiLayananData['items'] as $index => $item) {
                    if (! empty($item['layanan_id'])) {
                        // Get layanan data dari loaded map
                        $layanan = $layananMap->get($item['layanan_id']);

                        if (! $layanan) {
                            Log::error('Kasir: Layanan not found', [
                                'layanan_id' => $item['layanan_id'],
                                'index' => $index,
                            ]);
                            throw new Exception("Layanan dengan ID {$item['layanan_id']} tidak ditemukan");
                        }

                        $transaksiLayananData = [
                            'transaksi_id' => $transaksi->id,
                            'layanan_id' => $item['layanan_id'],
                            'nama_layanan' => $item['nama_layanan'] ?? $layanan->nama_layanan,
                            'subtotal' => $item['subtotal'] ?? 0,
                        ];

                        if ($item['tipe_layanan'] === 'per_kg') {
                            // Prepare data untuk per_kg
                            $transaksiLayananData['jenis_pakaian'] = ! empty($item['jenis_pakaian']) ? $item['jenis_pakaian'] : null;
                            $transaksiLayananData['berat_kg'] = $item['berat_kg'] ?? 0;
                            $transaksiLayananData['harga_per_kg'] = $item['harga_per_kg'] ?? $layanan->harga_per_kg;
                            $transaksiLayananData['jumlah_satuan'] = null;
                            $transaksiLayananData['harga_per_satuan'] = null;

                            // Calculate subtotal jika belum ada
                            if (empty($item['subtotal'])) {
                                $berat = (float) ($item['berat_kg'] ?? 0);
                                $harga = (int) $transaksiLayananData['harga_per_kg'];
                                $transaksiLayananData['subtotal'] = (int) ($berat * $harga);
                            }
                        } else {
                            // Prepare data untuk per_satuan
                            $transaksiLayananData['jenis_pakaian'] = null;
                            $transaksiLayananData['berat_kg'] = null;
                            $transaksiLayananData['harga_per_kg'] = null;
                            $transaksiLayananData['jumlah_satuan'] = $item['jumlah_satuan'] ?? 1;
                            $transaksiLayananData['harga_per_satuan'] = $item['harga_per_satuan'] ?? $layanan->harga_per_satuan;

                            // Calculate subtotal jika belum ada
                            if (empty($item['subtotal'])) {
                                $jumlah = (int) ($item['jumlah_satuan'] ?? 1);
                                $harga = (int) $transaksiLayananData['harga_per_satuan'];
                                $transaksiLayananData['subtotal'] = $jumlah * $harga;
                            }
                        }

                        // Create TransaksiLayanan using Model (untuk automatic JSON casting)
                        $transaksiLayananData['created_at'] = now();
                        $transaksiLayananData['updated_at'] = now();

                        try {
                            TransaksiLayanan::create($transaksiLayananData);
                        } catch (Exception $e) {
                            Log::error('Kasir: Failed to create TransaksiLayanan', [
                                'error' => $e->getMessage(),
                                'data' => $transaksiLayananData,
                                'index' => $index,
                            ]);
                            throw $e;
                        }
                    }
                }

                // Simpan snapshot promo ke transaksi_promo (audit trail)
                if ($this->formData['promo_id'] && $this->promoResult['valid'] && $this->promoResult['diskon'] > 0) {
                    $promo = $this->cachedPromo ?? PromoHelper::getById((int) $this->formData['promo_id']);
                    if ($promo) {
                        // Create TransaksiPromo record dengan snapshot data promo
                        $transaksi->transaksiPromo()->create([
                            'promo_id' => $promo->id,
                            'kode_promo' => $promo->kode_promo,
                            'nama_promo' => $promo->nama_promo,
                            'tipe_diskon' => $promo->tipe_diskon,
                            'nilai_diskon_persen' => $promo->nilai_diskon,
                            'nilai_diskon_nominal' => $this->promoResult['diskon'], // Nilai diskon aktual yang diterapkan
                            'diskon_maksimal' => $promo->diskon_maksimal,
                            'gratis_kg' => $promo->gratis_kg,
                            'gratis_hari' => $promo->gratis_hari,
                            'diterapkan_ke' => $promo->diterapkan_ke ?? 'subtotal', // Default 'subtotal' jika null
                            'layanan_id' => $promo->layanan_id,
                            'urutan_apply' => 1, // Future: untuk multiple promo
                        ]);

                        // Observer TransaksiPromoObserver akan otomatis increment kuota

                        Log::info('Kasir: Promo saved', [
                            'transaksi_id' => $transaksi->id,
                            'promo_id' => $promo->id,
                            'kode_promo' => $promo->kode_promo,
                            'diskon_nominal' => $this->promoResult['diskon'],
                        ]);
                    }
                }

                // Simpan kode transaksi terakhir untuk struk
                $this->lastTransactionId = $transaksi->kode_transaksi;
            });

            // Success message berbeda tergantung apakah ada pelanggan baru
            if ($this->isPelangganBaru) {
                $namaPelanggan = $this->pelangganBaru['nama'];
                $this->success("Pelanggan {$namaPelanggan} dan transaksi berhasil disimpan!", position: 'toast-bottom');
            } else {
                $this->success('Transaksi berhasil disimpan!', position: 'toast-bottom');
            }

            // Reset form untuk transaksi baru
            $this->resetForm();

            // Tampilkan pilihan cetak struk
            $this->showReceipt = true;
        } catch (QueryException $e) {
            Log::error('Kasir: Database error', [
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
            ]);

            // Handle unique constraint violation
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) { // Duplicate entry
                Log::warning('Kasir: Duplicate entry detected, regenerating kode', [
                    'kode_transaksi' => $this->formData['kode_transaksi'],
                ]);
                $this->refreshKodeTransaksi();
                $this->success('Kode transaksi di-regenerate, silakan coba lagi', position: 'toast-bottom');

                return;
            }

            // Jangan expose SQL error ke user - security risk!
            $this->error('Gagal menyimpan transaksi. Silakan coba lagi atau hubungi administrator.', timeout: 10000, position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Kasir: Unexpected error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
                'multiLayananData' => $this->multiLayananData,
            ]);

            // Jangan expose technical error ke user - security risk!
            $this->error('Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.', timeout: 10000, position: 'toast-bottom');
        }
    }

    public function printReceipt(?string $transactionId = null): void
    {
        $kode = $transactionId ?? $this->lastTransactionId;
        if (! empty($kode)) {
            // Cari transaksi by kode untuk dapat ID
            $transaksi = Transaksi::where('kode_transaksi', $kode)->first();
            if ($transaksi instanceof Transaksi) {
                $this->dispatch('open-print-window', url: route('receipt.print', ['id' => $transaksi->id]));
                $this->showReceipt = false;
            }
        }
    }

    public function batalTransaksi(): void
    {
        $this->resetForm();
        $this->success('Form direset', position: 'toast-bottom');
    }

    public function savePelangganBaru(): void
    {
        // Validasi sederhana
        if (empty($this->pelangganBaru['nama'])) {
            Log::warning('Kasir: savePelangganBaru validation failed - nama kosong');
            $this->error('Nama pelanggan wajib diisi!', position: 'toast-bottom');

            return;
        }

        if (empty($this->pelangganBaru['no_hp'])) {
            Log::warning('Kasir: savePelangganBaru validation failed - no_hp kosong');
            $this->error('Nomor HP wajib diisi!', position: 'toast-bottom');

            return;
        }

        if (empty($this->pelangganBaru['detail_alamat'])) {
            Log::warning('Kasir: savePelangganBaru validation failed - detail_alamat kosong');
            $this->error('Detail alamat wajib diisi!', position: 'toast-bottom');

            return;
        }

        if (empty($this->pelangganBaru['latitude'])) {
            Log::warning('Kasir: savePelangganBaru validation failed - latitude kosong');
            $this->error('Latitude wajib diisi!', position: 'toast-bottom');

            return;
        }

        if (empty($this->pelangganBaru['longitude'])) {
            Log::warning('Kasir: savePelangganBaru validation failed - longitude kosong');
            $this->error('Longitude wajib diisi!', position: 'toast-bottom');

            return;
        }

        try {
            // Gunakan transaction untuk pembuatan pelanggan baru
            DB::transaction(function () {
                // Create pelanggan menggunakan PelangganHelper (OOP approach)
                $pelanggan = PelangganHelper::createPelanggan(
                    $this->pelangganBaru,
                    $this->formData['tanggal_masuk'] ? \Carbon\Carbon::parse($this->formData['tanggal_masuk'])->format('Y-m-d H:i:s') : null
                );

                $this->success("Pelanggan {$this->pelangganBaru['nama']} berhasil ditambahkan!", position: 'toast-bottom');

                // Auto-select pelanggan yang baru ditambahkan
                $this->formData['pelanggan_id'] = $pelanggan->id;
                $this->formData['nama_pelanggan'] = $pelanggan->nama;

                // Reset form pelanggan baru
                $this->pelangganBaru = [
                    'nama' => '',
                    'no_hp' => '',
                    'email' => '',
                    'detail_alamat' => '',
                    'kelurahan' => '',
                    'kecamatan' => '',
                    'kabupaten_kota' => '',
                    'provinsi' => '',
                ];

                // Switch kembali ke mode pilih pelanggan existing
                $this->isPelangganBaru = false;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Kasir: Database error saat create pelanggan', [
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pelanggan_data' => $this->pelangganBaru,
            ]);

            // Handle unique constraint violation
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) { // Duplicate entry
                $this->error('Nomor HP atau email sudah terdaftar. Silakan gunakan data lain.', position: 'toast-bottom');

                return;
            }

            // Jangan expose SQL error ke user - security risk!
            $this->error('Gagal menyimpan pelanggan. Silakan coba lagi atau hubungi administrator.', position: 'toast-bottom');
        } catch (\Exception $e) {
            Log::error('Kasir: Unexpected error saat create pelanggan', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'pelanggan_data' => $this->pelangganBaru,
            ]);

            // Jangan expose technical error ke user - security risk!
            $this->error('Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.', position: 'toast-bottom');
        }
    }

    public function getPelangganOptions(): array
    {
        try {
            // Gunakan PelangganHelper untuk get options (OOP approach)
            return PelangganHelper::getPelangganOptions('', 100);
        } catch (\Exception $e) {
            Log::error('Error getting pelanggan options: '.$e->getMessage());

            return [];
        }
    }

    public function getLayananOptions(): array
    {
        // Gunakan LayananHelper untuk get options (OOP approach)
        return LayananHelper::getLayananOptions();
    }

    public function getMetodePembayaranOptions(): array
    {
        // Get metode pembayaran dari TransaksiHelper
        return collect(TransaksiHelper::getAllMetodePembayaran())->map(fn (string $metode) => [
            'id' => $metode,
            'name' => $metode,
        ])->toArray();
    }

    public function getStatusOptions(): array
    {
        // Get status transaksi dari TransaksiHelper
        return collect(TransaksiHelper::getAllStatus())->map(fn (string $status) => [
            'id' => $status,
            'name' => $status,
        ])->toArray();
    }

    public function getTipeBayarOptions(): array
    {
        // Get tipe bayar dari TransaksiHelper (BAGAIMANA cara bayar)
        return collect(TransaksiHelper::getAllTipeBayar())->map(fn (string $tipe) => [
            'id' => $tipe,
            'name' => $tipe,
        ])->toArray();
    }

    public function getStatusBayarOptions(): array
    {
        // Get status bayar dari TransaksiHelper
        return collect(TransaksiHelper::getAllStatusBayar())->map(fn (string $status) => [
            'id' => $status,
            'name' => $status,
        ])->toArray();
    }

    public function getPromoOptions(): array
    {
        // Get promo yang aktif dari PromoHelper, filter berdasarkan pelanggan
        $pelangganId = $this->formData['pelanggan_id'] ?? null;

        return PromoHelper::getPromoOptions($pelangganId ? (int) $pelangganId : null);
    }

    public function render()
    {
        return view('livewire.management.kasir', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
            'metodePembayaranOptions' => $this->getMetodePembayaranOptions(),
            'tipeBayarOptions' => $this->getTipeBayarOptions(),
            'statusBayarOptions' => $this->getStatusBayarOptions(),
            'statusOptions' => $this->getStatusOptions(),
            'promoOptions' => $this->getPromoOptions(),
        ]);
    }
}
