<?php

declare(strict_types=1);

namespace App\Livewire\Management\Transaksi;

use Exception;
use App\Models\Kurir;
use App\Models\Promo;
use Mary\Traits\Toast;
use App\Models\Layanan;
use Livewire\Component;
use App\Models\Referral;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\TransaksiLayanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helper\Database\KurirHelper;
use App\Helper\Database\PromoHelper;
use Illuminate\Support\Facades\Auth;
use App\Helper\Database\LayananHelper;
use App\Helper\Database\ReferralHelper;
use Illuminate\Database\QueryException;
use App\Helper\Database\PelangganHelper;
use App\Helper\Database\TransaksiHelper;
use App\Helper\Database\PengaturanHelper;

#[Title('Tambah Transaksi')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;

    public array $pelangganOptions = [];

    public array $promoOptions = [];

    public array $referralOptions = [];

    public array $kurirOptions = [];

    // Metadata fields
    public ?int $selectedPromoId = null;

    public ?int $selectedReferralId = null;

    public ?int $kurirJemputId = null;

    public ?int $kurirAntarId = null;

    public array $formData = [
        'kode_transaksi' => '',
        'tanggal_masuk' => '',
        'kasir_id' => null,
        'pelanggan_id' => '',
        'nama_pelanggan' => '',
        'subtotal' => 0,
        'diskon' => 0,
        'total' => 0,
        'metode_pembayaran' => TransaksiHelper::METODE_TUNAI,
        'tanggal_selesai' => '',
        'status' => TransaksiHelper::STATUS_MENUNGGU,
        'catatan' => '',
    ];

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
        $this->formData['total'] = $data['totalGrandTotal'] - (float) $this->formData['diskon'];

        // Calculate tanggal selesai based on layanan with longest duration
        $this->calculateTanggalSelesaiFromMultiLayanan();
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

    public function updatedFormDataDiskon(): void
    {
        $this->formData['total'] = (float) $this->formData['subtotal'] - (float) $this->formData['diskon'];
    }

    public function updatedSelectedPromoId(?int $value): void
    {
        if ($value) {
            try {
                $promo = Promo::find($value);

                if ($promo && PromoHelper::isValid($promo)) {
                    // Hitung diskon berdasarkan tipe
                    if ($promo->tipe_diskon === 'persen') {
                        $diskon = ($this->formData['subtotal'] * $promo->nilai_diskon) / 100;

                        // Check max diskon
                        if ($promo->maks_diskon && $diskon > $promo->maks_diskon) {
                            $diskon = $promo->maks_diskon;
                        }
                    } else {
                        $diskon = $promo->nilai_diskon;
                    }

                    // Check min transaksi
                    if ($promo->min_transaksi && $this->formData['subtotal'] < $promo->min_transaksi) {
                        $this->warning('Promo ini memerlukan minimum transaksi Rp '.number_format($promo->min_transaksi, 0, ',', '.'), position: 'toast-bottom');
                        $this->selectedPromoId = null;

                        return;
                    }

                    $this->formData['diskon'] = (int) $diskon;
                    $this->formData['total'] = (float) $this->formData['subtotal'] - (float) $this->formData['diskon'];

                    $this->success("Promo {$promo->kode_promo} diterapkan!", position: 'toast-bottom');
                } else {
                    $this->warning('Promo tidak valid atau sudah habis', position: 'toast-bottom');
                    $this->selectedPromoId = null;
                }
            } catch (Exception $e) {
                Log::error('Error applying promo in Create', [
                    'promo_id' => $value,
                    'error' => $e->getMessage(),
                ]);
                $this->error('Gagal menerapkan promo', position: 'toast-bottom');
                $this->selectedPromoId = null;
            }
        } else {
            // Reset diskon jika promo dihapus
            $this->formData['diskon'] = 0;
            $this->formData['total'] = (float) $this->formData['subtotal'];
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
            'formData.metode_pembayaran' => 'required|in:Tunai,Transfer,QRIS,Debit',
            'formData.status' => 'required|in:Menunggu,Proses,Selesai,Diambil,Batal',
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

                // === Handle Metadata ===

                // 1. Promo metadata
                if ($this->selectedPromoId) {
                    $promo = Promo::find($this->selectedPromoId);
                    if ($promo) {
                        TransaksiHelper::setPromoInfo($transaksi, [
                            'promo_id' => $promo->id,
                            'kode_promo' => $promo->kode_promo,
                            'nama_promo' => $promo->nama_promo,
                            'tipe_diskon' => $promo->tipe_diskon,
                            'nilai_diskon' => $promo->nilai_diskon,
                            'nilai_diskon_real' => $this->formData['diskon'],
                        ]);

                        // Increment usage counter
                        PromoHelper::incrementUsage($promo);
                    }
                }

                // 2. Referral metadata
                if ($this->selectedReferralId) {
                    $referral = Referral::find($this->selectedReferralId);
                    if ($referral) {
                        TransaksiHelper::setReferralInfo($transaksi, [
                            'referral_id' => $referral->id,
                            'kode_referral' => $referral->kode_referral,
                            'pelanggan_referrer_id' => $referral->pelanggan_id,
                            'poin_diberikan' => $referral->poin_referee,
                        ]);

                        // Increment referral counter
                        ReferralHelper::incrementReferral($referral);
                    }
                }

                // 3. Kurir Jemput
                if ($this->kurirJemputId) {
                    $kurir = Kurir::find($this->kurirJemputId);
                    if ($kurir) {
                        TransaksiHelper::setKurirJemput($transaksi, $kurir->nama);
                    }
                }

                // 4. Kurir Antar
                if ($this->kurirAntarId) {
                    $kurir = Kurir::find($this->kurirAntarId);
                    if ($kurir) {
                        TransaksiHelper::setKurirAntar($transaksi, $kurir->nama);
                    }
                }

                // Save metadata ke database
                $transaksi->save();

                // Simpan detail transaksi layanan
                foreach ($this->multiLayananData['items'] as $index => $item) {
                    if (! empty($item['layanan_id'])) {
                        // Get layanan data untuk backup jika item data kosong
                        $layanan = Layanan::find($item['layanan_id']);

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
            // Gunakan PelangganHelper untuk get options (OOP approach)
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
        // Gunakan LayananHelper untuk get options (OOP approach)
        return LayananHelper::getLayananOptions();
    }

    public function render(): mixed
    {
        return view('livewire.management.transaksi.create', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
        ]);
    }
}
