<?php

declare(strict_types=1);

namespace App\Livewire\Management\Transaksi;

use App\Helper\Database\KurirHelper;
use App\Helper\Database\LayananHelper;
use App\Helper\Database\PelangganHelper;
use App\Helper\Database\PengaturanHelper;
use App\Helper\Database\PromoHelper;
use App\Helper\Database\ReferralHelper;
use App\Helper\Database\TransaksiHelper;
use App\Models\Kurir;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Promo;
use App\Models\Transaksi;
use App\Models\TransaksiLayanan;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Mary\Traits\WithMediaSync;

#[Title('Tambah Transaksi')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;
    use WithFileUploads;
    use WithMediaSync;

    public array $pelangganOptions = [];

    public array $promoOptions = [];

    public array $referralOptions = [];

    public array $kurirOptions = [];

    public ?int $kurirJemputId = null;

    public ?int $kurirAntarId = null;

    // Image Library - Temporary files
    #[Rule(['fotoTimbangan.*' => 'nullable|image|max:5120'])]
    public array $fotoTimbangan = [];

    #[Rule(['fotoPembayaran.*' => 'nullable|image|max:5120'])]
    public array $fotoPembayaran = [];

    // Image Library - Library metadata
    public Collection $libraryTimbangan;

    public Collection $libraryPembayaran;

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
        'catatan_internal' => '',
    ];

    // Promo validation result
    public array $promoResult = [
        'valid' => false,
        'diskon' => 0,
        'pesan' => '',
        'tipe' => '',
    ];

    // Cache promo object
    protected $cachedPromo = null;

    // Multi-layanan data
    public array $multiLayananData = [
        'items' => [],
        'totalSubtotal' => 0,
        'totalGrandTotal' => 0,
    ];

    public function mount(): void
    {
        $this->refreshKodeTransaksi();
        $this->formData['tanggal_masuk'] = now()->format('Y-m-d\TH:i');
        $this->formData['kasir_id'] = Auth::id();

        // Initialize multiLayananData dengan default values
        $this->multiLayananData = [
            'items' => [],
            'totalSubtotal' => 0,
            'totalGrandTotal' => 0,
        ];

        // Initialize image library metadata as empty collections
        $this->libraryTimbangan = new Collection();
        $this->libraryPembayaran = new Collection();

        $this->search();
        $this->loadOptions();
    }

    public function loadOptions(): void
    {
        $this->promoOptions = PromoHelper::getPromoOptions();
        $this->referralOptions = ReferralHelper::getReferralOptions();
        $this->kurirOptions = KurirHelper::getKurirOptions();
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
        } catch (Exception $e) {
            Log::error('Error searching pelanggan in Create', [
                'term' => $term,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->pelangganOptions = [];
        }
    }

    #[On('jenisPakaianUpdated')]
    public function jenisPakaianUpdated($outputString): void
    {
        // Legacy method - not used in multi-layanan
    }

    #[On('multiLayananUpdated')]
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

    public function updatedFormDataPromoId(): void
    {
        $this->calculatePromoDiskon();
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
        $tempTransaksi = new Transaksi();
        $tempTransaksi->tanggal_masuk = $this->formData['tanggal_masuk'];
        $tempTransaksi->setRelation('transaksiLayanan', collect($this->multiLayananData['items'])->map(function ($item) {
            if (! empty($item['layanan_id'])) {
                $tempTransaksiLayanan = new TransaksiLayanan();
                $tempTransaksiLayanan->setRelation('layanan', Layanan::find($item['layanan_id']));

                return $tempTransaksiLayanan;
            }

        })->filter());

        $tanggalTerlama = TransaksiHelper::getTanggalSelesaiTerlama($tempTransaksi);
        $this->formData['tanggal_selesai'] = $tanggalTerlama ? $tanggalTerlama->format('Y-m-d H:i') : '';
    }

    public function updatedFormDataPelangganId(mixed $value): void
    {
        if ($value) {
            $pelanggan = Pelanggan::find($value);
            if ($pelanggan instanceof Pelanggan) {
                $this->formData['nama_pelanggan'] = $pelanggan->nama;
            }
        }
    }

    public function save(): void
    {
        // Validasi multi-layanan
        if (empty($this->multiLayananData['items'])) {
            Log::warning('Transaksi Create validation failed: items layanan kosong');
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
                        Log::warning('Transaksi Create validation failed: berat_kg invalid', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                            'berat_kg' => $item['berat_kg'] ?? null,
                            'min_berat_kg_required' => $minBeratKg,
                        ]);
                        $this->error("Berat minimal {$minBeratKg} kg untuk layanan ".$item['nama_layanan'].'!', position: 'toast-bottom');

                        return;
                    }
                    if (empty($item['jenis_pakaian']) || count($item['jenis_pakaian']) === 0) {
                        Log::warning('Transaksi Create validation failed: jenis_pakaian kosong', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                        ]);
                        $this->error('Jenis pakaian wajib diisi untuk layanan '.$item['nama_layanan'].'!', position: 'toast-bottom');

                        return;
                    }
                } else {
                    if (empty($item['jumlah_satuan']) || $item['jumlah_satuan'] < 1) {
                        Log::warning('Transaksi Create validation failed: jumlah_satuan invalid', [
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
            Log::warning('Transaksi Create validation failed: tidak ada layanan valid');
            $this->error('Pilih layanan yang valid terlebih dahulu!', position: 'toast-bottom');

            return;
        }

        $this->validate([
            'formData.kode_transaksi' => 'required|unique:transaksi,kode_transaksi',
            'formData.tanggal_masuk' => 'required|date',
            'formData.pelanggan_id' => 'required|exists:pelanggan,id',
            'formData.metode_pembayaran' => [
                'required',
                'in:'.implode(',', TransaksiHelper::getAllMetodePembayaran()),
            ],
            'formData.tipe_bayar' => [
                'nullable',
                'in:'.implode(',', TransaksiHelper::getAllTipeBayar()),
            ],
            'formData.status_bayar' => [
                'required',
                'in:'.implode(',', TransaksiHelper::getAllStatusBayar()),
            ],
            'formData.status' => [
                'required',
                'in:'.implode(',', TransaksiHelper::getAllStatus()),
            ],
            'fotoTimbangan.*' => 'nullable|image|max:5120',
            'fotoPembayaran.*' => 'nullable|image|max:5120',
        ]);

        try {
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function () {
                // ID Kasir
                $this->formData['kasir_id'] = Auth::id() ?? 1;

                // Cek ulang apakah kode transaksi sudah ada, jika ya generate ulang
                if (Transaksi::where('kode_transaksi', $this->formData['kode_transaksi'])->exists()) {
                    Log::warning('Transaksi Create: Duplicate kode_transaksi detected, regenerating', [
                        'old_kode' => $this->formData['kode_transaksi'],
                    ]);
                    $this->refreshKodeTransaksi();
                }

                // Prepare transaksi data
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

                // Simpan transaksi
                $transaksi = Transaksi::create($transaksiData);

                // === Handle File Uploads dengan Image Library ===
                // Sync foto bukti timbangan using WithMediaSync trait
                $this->syncMedia(
                    model: $transaksi,
                    library: 'libraryTimbangan',
                    files: 'fotoTimbangan',
                    storage_subpath: '/transaksi/timbangan',
                    model_field: 'foto_bukti_timbangan',
                    visibility: 'public',
                    disk: 'public'
                );

                // Sync foto bukti pembayaran using WithMediaSync trait
                $this->syncMedia(
                    model: $transaksi,
                    library: 'libraryPembayaran',
                    files: 'fotoPembayaran',
                    storage_subpath: '/transaksi/pembayaran',
                    model_field: 'foto_bukti_pembayaran',
                    visibility: 'public',
                    disk: 'public'
                );

                // === Handle Promo & Kurir ===

                // 1. Increment promo usage jika ada promo yang digunakan
                if ($this->formData['promo_id']) {
                    $promo = $this->cachedPromo ?? PromoHelper::getById((int) $this->formData['promo_id']);
                    if ($promo) {
                        PromoHelper::incrementUsage($promo);
                        Log::info('Transaksi Create: Promo usage incremented', [
                            'promo_id' => $promo->id,
                            'kode_promo' => $promo->kode_promo,
                        ]);
                    }
                }

                // 2. Kurir Jemput
                if ($this->kurirJemputId) {
                    $kurir = Kurir::find($this->kurirJemputId);
                    if ($kurir) {
                        TransaksiHelper::setKurirJemput($transaksi, $kurir->id, $kurir->nama);
                    }
                } else {
                    TransaksiHelper::setKurirJemput($transaksi, null);
                }

                // 3. Kurir Antar
                if ($this->kurirAntarId) {
                    $kurir = Kurir::find($this->kurirAntarId);
                    if ($kurir) {
                        TransaksiHelper::setKurirAntar($transaksi, $kurir->id, $kurir->nama);
                    }
                } else {
                    TransaksiHelper::setKurirAntar($transaksi, null);
                }

                // Save ke database
                $transaksi->save();

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
                            Log::error('Transaksi Create: Layanan not found', [
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
                            Log::error('Transaksi Create: Failed to create TransaksiLayanan', [
                                'error' => $e->getMessage(),
                                'data' => $transaksiLayananData,
                                'index' => $index,
                            ]);
                            throw $e;
                        }
                    }
                }
            });

            $this->success('Transaksi berhasil ditambahkan!', position: 'toast-bottom');
            $this->redirect('/management/transaksi', navigate: true);
        } catch (QueryException $e) {
            Log::error('Transaksi Create: Database error', [
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
            ]);

            // Handle unique constraint violation
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) { // Duplicate entry
                Log::warning('Transaksi Create: Duplicate entry detected, regenerating kode', [
                    'kode_transaksi' => $this->formData['kode_transaksi'],
                ]);
                $this->refreshKodeTransaksi();
                $this->success('Kode transaksi di-regenerate, silakan coba lagi', position: 'toast-bottom');

                return;
            }

            // Jangan expose SQL error ke user - security risk!
            $this->error('Gagal menyimpan transaksi. Silakan coba lagi atau hubungi administrator.', timeout: 10000, position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Transaksi Create: Unexpected error', [
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

    public function getPelangganOptions(): array
    {
        try {
            return PelangganHelper::getPelangganOptions('', 100);
        } catch (Exception $e) {
            Log::error('Transaksi Create: Error getting pelanggan options', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function getLayananOptions(): array
    {
        return LayananHelper::getLayananOptions();
    }

    public function getStatusOptions(): array
    {
        return TransaksiHelper::getStatusOptions();
    }

    public function getMetodePembayaranOptions(): array
    {
        return TransaksiHelper::getMetodePembayaranOptions();
    }

    public function getTipeBayarOptions(): array
    {
        return TransaksiHelper::getTipeBayarOptions();
    }

    public function getStatusBayarOptions(): array
    {
        return TransaksiHelper::getStatusBayarOptions();
    }

    public function render(): mixed
    {
        return view('livewire.management.transaksi.create', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
            'statusOptions' => $this->getStatusOptions(),
            'metodePembayaranOptions' => $this->getMetodePembayaranOptions(),
            'tipeBayarOptions' => $this->getTipeBayarOptions(),
            'statusBayarOptions' => $this->getStatusBayarOptions(),
        ]);
    }
}
