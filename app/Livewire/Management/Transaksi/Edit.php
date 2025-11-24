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
use App\Helper\Database\TransaksiLayananHelper;
use App\Models\Kurir;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Promo;
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

#[Title('Edit Transaksi')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;

    public int $transaksiId;

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
        'promo_id' => null,
        'referral_id' => null,
        'kode_promo' => '',
        'nama_pelanggan' => '',
        'subtotal' => 0,
        'diskon' => 0,
        'total' => 0,
        'metode_pembayaran' => 'Tunai',
        'tanggal_selesai' => '',
        'status' => 'Menunggu',
        'catatan' => '',
    ];

    // Multi-layanan data
    public array $multiLayananData = [
        'items' => [],
        'totalSubtotal' => 0,
        'totalGrandTotal' => 0,
    ];

    protected $listeners = ['jenisPakaianUpdated', 'multiLayananUpdated'];

    public function mount(int $id): void
    {
        $this->transaksiId = $id;
        $this->loadTransaksi();
        $this->search();
        $this->loadOptions();
    }

    public function loadOptions(): void
    {
        $this->promoOptions = PromoHelper::getPromoOptions();
        $this->referralOptions = ReferralHelper::getReferralOptions();
        $this->kurirOptions = KurirHelper::getKurirOptions();
    }

    public function search(string $term = ''): void
    {
        try {
            // Menggunakan PelangganHelper untuk get options (approach OOP)
            $this->pelangganOptions = PelangganHelper::getPelangganOptions($term, 10);
        } catch (Exception $e) {
            Log::error('Error searching pelanggan in Edit', [
                'term' => $term,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->pelangganOptions = [];
        }
    }

    protected function loadTransaksi(): void
    {
        try {
            $transaksi = Transaksi::with(['transaksiLayanan.layanan'])->findOrFail($this->transaksiId);

            $this->formData = [
                'kode_transaksi' => $transaksi->kode_transaksi,
                'tanggal_masuk' => $transaksi->tanggal_masuk->format('Y-m-d\TH:i'),
                'kasir_id' => $transaksi->kasir_id,
                'pelanggan_id' => $transaksi->pelanggan_id,
                'promo_id' => $transaksi->promo_id,
                'referral_id' => $transaksi->referral_id,
                'kode_promo' => $transaksi->kode_promo ?? '',
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'subtotal' => $transaksi->subtotal,
                'diskon' => $transaksi->diskon,
                'total' => $transaksi->total,
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'tanggal_selesai' => $transaksi->tanggal_selesai
                    ? $transaksi->tanggal_selesai->format('Y-m-d\TH:i')
                    : '',
                'status' => $transaksi->status,
                'catatan' => $transaksi->catatan ?? '',
            ];

            // Set selectedPromoId untuk UI dropdown
            if ($transaksi->promo_id) {
                $this->selectedPromoId = $transaksi->promo_id;
            }

            // Set selectedReferralId untuk UI dropdown
            if ($transaksi->referral_id) {
                $this->selectedReferralId = $transaksi->referral_id;
            }

            // Load multi-layanan data
            $this->loadMultiLayananData($transaksi);

            // Load metadata
            $this->loadMetadata($transaksi);
        } catch (Exception $e) {
            Log::error('Transaksi Edit: Failed to load transaksi', [
                'transaksi_id' => $this->transaksiId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Transaksi tidak ditemukan', position: 'toast-bottom');
            $this->redirect('/management/transaksi', navigate: true);
        }
    }

    protected function loadMultiLayananData($transaksi)
    {
        $items = [];
        $totalSubtotal = 0;

        // Load transaksi layanan data
        if ($transaksi->transaksiLayanan && $transaksi->transaksiLayanan->count() > 0) {
            foreach ($transaksi->transaksiLayanan as $tl) {
                $layanan = $tl->layanan;
                if (! $layanan) {
                    continue; // Skip if layanan not found
                }

                $item = [
                    'layanan_id' => $tl->layanan_id,
                    'nama_layanan' => $tl->nama_layanan ?? $layanan->nama_layanan,
                    'tipe_layanan' => $layanan->tipe_layanan ?? 'per_kg',
                    'subtotal' => $tl->subtotal ?? 0,
                    'jenis_pakaian' => [],
                    'satuan' => $layanan->satuan ?? 'kg',
                ];

                if (TransaksiLayananHelper::isPerKg($tl)) {
                    $item['berat_kg'] = $tl->berat_kg ?? 0;
                    $item['harga_per_kg'] = $tl->harga_per_kg ?? $layanan->harga_per_kg ?? 0;

                    // Decode jenis_pakaian from JSON
                    if (! empty($tl->jenis_pakaian)) {
                        if (is_string($tl->jenis_pakaian)) {
                            $decoded = json_decode($tl->jenis_pakaian, true);
                            $item['jenis_pakaian'] = is_array($decoded) ? $decoded : [];
                        } elseif (is_array($tl->jenis_pakaian)) {
                            $item['jenis_pakaian'] = $tl->jenis_pakaian;
                        }
                    }

                    // Recalculate subtotal if missing
                    if (empty($item['subtotal']) && $item['berat_kg'] > 0 && $item['harga_per_kg'] > 0) {
                        $item['subtotal'] = $item['berat_kg'] * $item['harga_per_kg'];
                    }
                } else {
                    $item['jumlah_satuan'] = $tl->jumlah_satuan ?? 1;
                    $item['harga_per_satuan'] = $tl->harga_per_satuan ?? $layanan->harga_per_satuan ?? 0;

                    // Recalculate subtotal if missing
                    if (empty($item['subtotal']) && $item['jumlah_satuan'] > 0 && $item['harga_per_satuan'] > 0) {
                        $item['subtotal'] = $item['jumlah_satuan'] * $item['harga_per_satuan'];
                    }
                }

                $items[] = $item;
                $totalSubtotal += $item['subtotal'];
            }
        } else {
            // Fallback for old single-layanan transactions
            if ($transaksi->layanan_id) {
                $layanan = Layanan::find($transaksi->layanan_id);
                if ($layanan) {
                    $item = [
                        'layanan_id' => $transaksi->layanan_id,
                        'nama_layanan' => $transaksi->nama_layanan ?? $layanan->nama_layanan,
                        'tipe_layanan' => $layanan->tipe_layanan ?? 'per_kg',
                        'subtotal' => $transaksi->subtotal ?? 0,
                        'jenis_pakaian' => [],
                        'satuan' => $layanan->satuan ?? 'kg',
                    ];

                    if ($layanan->tipe_layanan === 'per_kg') {
                        $item['berat_kg'] = $transaksi->berat_kg ?? 0;
                        $item['harga_per_kg'] = $transaksi->harga_per_kg ?? $layanan->harga_per_kg ?? 0;

                        // Decode jenis_pakaian from JSON
                        if (! empty($transaksi->jenis_pakaian)) {
                            if (is_string($transaksi->jenis_pakaian)) {
                                $decoded = json_decode($transaksi->jenis_pakaian, true);
                                $item['jenis_pakaian'] = is_array($decoded) ? $decoded : [];
                            } elseif (is_array($transaksi->jenis_pakaian)) {
                                $item['jenis_pakaian'] = $transaksi->jenis_pakaian;
                            }
                        }
                    } else {
                        $item['jumlah_satuan'] = $transaksi->total_item ?? 1;
                        $item['harga_per_satuan'] = $layanan->harga_per_satuan ?? 0;
                    }

                    $items[] = $item;
                    $totalSubtotal = $item['subtotal'];
                }
            }
        }

        $this->multiLayananData = [
            'items' => $items,
            'totalSubtotal' => $totalSubtotal,
            'totalGrandTotal' => $totalSubtotal,
        ];
    }

    public function jenisPakaianUpdated($outputString)
    {
        // Legacy method - not used in multi-layanan
    }

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
        $tempTransaksi = new Transaksi();
        $tempTransaksi->tanggal_masuk = $this->formData['tanggal_masuk'];
        $tempTransaksi->setRelation('transaksiLayanan', collect($this->multiLayananData['items'])->map(function ($item) {
            if (! empty($item['layanan_id'])) {
                $tempTransaksiLayanan = new TransaksiLayanan();
                $tempTransaksiLayanan->setRelation('layanan', Layanan::find($item['layanan_id']));

                return $tempTransaksiLayanan;
            }

            return null;
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

    protected function loadMetadata(Transaksi $transaksi): void
    {
        // Load promo info
        $promoInfo = TransaksiHelper::getPromoInfo($transaksi);
        if ($promoInfo && isset($promoInfo['promo_id'])) {
            $this->selectedPromoId = $promoInfo['promo_id'];
        }

        // Load referral info
        $referralInfo = TransaksiHelper::getReferralInfo($transaksi);
        if ($referralInfo && isset($referralInfo['referral_id'])) {
            $this->selectedReferralId = $referralInfo['referral_id'];
        }

        // Load kurir info
        $kurirJemput = TransaksiHelper::getKurirJemput($transaksi);
        if ($kurirJemput) {
            $kurir = Kurir::where('nama', $kurirJemput)->first();
            if ($kurir) {
                $this->kurirJemputId = $kurir->id;
            }
        }

        $kurirAntar = TransaksiHelper::getKurirAntar($transaksi);
        if ($kurirAntar) {
            $kurir = Kurir::where('nama', $kurirAntar)->first();
            if ($kurir) {
                $this->kurirAntarId = $kurir->id;
            }
        }
    }

    public function updatedSelectedPromoId(?int $value): void
    {
        if ($value) {
            $this->formData['promo_id'] = $value;
            $this->calculatePromoDiskon();
        } else {
            $this->formData['promo_id'] = null;
            $this->formData['kode_promo'] = '';
            $this->formData['diskon'] = 0;
            $this->formData['total'] = (float) $this->formData['subtotal'];
        }
    }

    protected function calculatePromoDiskon(): void
    {
        $promoId = $this->formData['promo_id'];

        if (! $promoId) {
            $this->formData['diskon'] = 0;
            $this->formData['kode_promo'] = '';
            $this->formData['total'] = (float) $this->formData['subtotal'];

            return;
        }

        $promo = PromoHelper::getById($promoId);

        if (! $promo) {
            $this->warning('Promo tidak ditemukan', position: 'toast-bottom');
            $this->selectedPromoId = null;
            $this->formData['promo_id'] = null;
            $this->formData['diskon'] = 0;
            $this->formData['kode_promo'] = '';
            $this->formData['total'] = (float) $this->formData['subtotal'];

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
        $subtotal = (int) $this->formData['subtotal'];

        $result = PromoHelper::hitungDiskon($promo, $subtotal, $totalBerat, $pelangganId);

        if ($result['valid']) {
            $this->formData['diskon'] = $result['diskon'];
            $this->formData['kode_promo'] = $promo->kode_promo;
            $this->formData['total'] = (float) ($subtotal - $result['diskon']);
            $this->success("Promo {$promo->kode_promo} diterapkan! {$result['pesan']}", position: 'toast-bottom');
        } else {
            $this->warning($result['pesan'], position: 'toast-bottom');
            $this->selectedPromoId = null;
            $this->formData['promo_id'] = null;
            $this->formData['diskon'] = 0;
            $this->formData['kode_promo'] = '';
            $this->formData['total'] = (float) $this->formData['subtotal'];
        }
    }

    public function printReceipt(): void
    {
        // Redirect ke halaman print receipt di tab baru
        $this->dispatch('open-print-window', url: route('receipt.print', ['id' => $this->transaksiId]));
    }

    public function save()
    {
        // Validasi multi-layanan
        if (empty($this->multiLayananData['items'])) {
            Log::warning('Transaksi Edit validation failed: items layanan kosong');
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
                        Log::warning('Transaksi Edit validation failed: berat_kg invalid', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                            'berat_kg' => $item['berat_kg'] ?? null,
                            'min_berat_kg_required' => $minBeratKg,
                        ]);
                        $this->error("Berat minimal {$minBeratKg} kg untuk layanan ".$item['nama_layanan'].'!', position: 'toast-bottom');

                        return;
                    }
                    if (empty($item['jenis_pakaian']) || count($item['jenis_pakaian']) === 0) {
                        Log::warning('Transaksi Edit validation failed: jenis_pakaian kosong', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                        ]);
                        $this->error('Jenis pakaian wajib diisi untuk layanan '.$item['nama_layanan'].'!', position: 'toast-bottom');

                        return;
                    }
                } else {
                    if (empty($item['jumlah_satuan']) || $item['jumlah_satuan'] < 1) {
                        Log::warning('Transaksi Edit validation failed: jumlah_satuan invalid', [
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
            Log::warning('Transaksi Edit validation failed: tidak ada layanan valid');
            $this->error('Pilih layanan yang valid terlebih dahulu!', position: 'toast-bottom');

            return;
        }

        $this->validate([
            'formData.tanggal_masuk' => 'required|date',
            'formData.pelanggan_id' => 'required|exists:pelanggan,id',
            'formData.metode_pembayaran' => 'required|in:Tunai,Transfer,QRIS,Debit',
            'formData.status' => 'required|in:Menunggu,Proses,Selesai,Diambil,Batal',
        ]);

        try {
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function () {
                $transaksi = Transaksi::findOrFail($this->transaksiId);

                // ID Kasir
                $this->formData['kasir_id'] = Auth::id() ?? 1;

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

                // Update transaksi
                $transaksi->update($transaksiData);

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
                    }
                } else {
                    // Hapus promo jika tidak ada
                    TransaksiHelper::setPromoInfo($transaksi, null);
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
                    }
                } else {
                    // Hapus referral jika tidak ada
                    TransaksiHelper::setReferralInfo($transaksi, null);
                }

                // 3. Kurir Jemput
                if ($this->kurirJemputId) {
                    $kurir = Kurir::find($this->kurirJemputId);
                    if ($kurir) {
                        TransaksiHelper::setKurirJemput($transaksi, $kurir->nama);
                    }
                } else {
                    TransaksiHelper::setKurirJemput($transaksi, null);
                }

                // 4. Kurir Antar
                if ($this->kurirAntarId) {
                    $kurir = Kurir::find($this->kurirAntarId);
                    if ($kurir) {
                        TransaksiHelper::setKurirAntar($transaksi, $kurir->nama);
                    }
                } else {
                    TransaksiHelper::setKurirAntar($transaksi, null);
                }

                // Save metadata ke database
                $transaksi->save();

                // Hapus transaksi layanan lama (force delete untuk menghindari duplikasi)
                DB::table('transaksi_layanan')->where('transaksi_id', $transaksi->id)->delete();

                // Simpan detail transaksi layanan baru
                foreach ($this->multiLayananData['items'] as $index => $item) {
                    if (! empty($item['layanan_id'])) {
                        // Get layanan data untuk backup jika item data kosong
                        $layanan = Layanan::find($item['layanan_id']);

                        if (! $layanan) {
                            Log::error('Transaksi Edit: Layanan not found', [
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
                            Log::error('Transaksi Edit: Failed to create TransaksiLayanan', [
                                'error' => $e->getMessage(),
                                'data' => $transaksiLayananData,
                                'index' => $index,
                            ]);
                            throw $e;
                        }
                    }
                }
            });

            $this->success('Transaksi berhasil diupdate!', position: 'toast-bottom');

            return $this->redirect('/management/transaksi', navigate: true);
        } catch (QueryException $e) {
            Log::error('Transaksi Edit: Database error', [
                'error_code' => $e->errorInfo[1] ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? [],
                'trace' => $e->getTraceAsString(),
            ]);

            // Jangan expose SQL error ke user - security risk!
            $this->error('Gagal menyimpan transaksi. Silakan coba lagi atau hubungi administrator.', timeout: 10000, position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Transaksi Edit: Unexpected error', [
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
            Log::error('Transaksi Edit: Error getting pelanggan options', [
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

    public function render()
    {
        return view('livewire.management.transaksi.edit', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
        ]);
    }
}
