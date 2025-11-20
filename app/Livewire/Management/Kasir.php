<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use Exception;
use Carbon\Carbon;
use Mary\Traits\Toast;
use App\Models\Layanan;
use Livewire\Component;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\TransaksiLayanan;
use App\Helper\PhoneNumber;
use App\Helper\AddressMetadata;
use App\Helper\RegionalLocation;
use App\Helper\Database\LayananHelper;
use App\Helper\Database\PelangganHelper;
use App\Helper\Database\PengaturanHelper;
use App\Helper\Database\TransaksiHelper;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

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
        'diskon' => 0,
        'subtotal' => 0,
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
    ];

    // Listener untuk event dari component
    protected $listeners = ['multiLayananUpdated'];

    public function multiLayananUpdated(array $data): void
    {
        $this->multiLayananData = $data;
        $this->formData['subtotal'] = $data['totalSubtotal'];
        $this->formData['total'] = $data['totalGrandTotal'] - $this->formData['diskon'];

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

        $this->search();
    }

    protected function resetForm(): void
    {
        $this->formData = [
            'kode_transaksi' => TransaksiHelper::generateKodeTransaksi(),
            'tanggal_masuk' => now()->format('Y-m-d\TH:i'),
            'kasir_id' => Auth::id(),
            'pelanggan_id' => '',
            'nama_pelanggan' => '',
            'diskon' => 0,
            'subtotal' => 0,
            'total' => 0,
            'metode_pembayaran' => TransaksiHelper::METODE_TUNAI,
            'tanggal_selesai' => '',
            'status' => TransaksiHelper::STATUS_MENUNGGU,
            'catatan' => '',
        ];

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
        ];

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

                // Auto-fill form pelanggan dengan data yang dipilih menggunakan Helpers
                $this->pelangganBaru['nama'] = $pelanggan->nama;
                $this->pelangganBaru['no_hp'] = PhoneNumber::formatLocal($pelanggan->no_hp)
                    ?? $pelanggan->no_hp;
                $this->pelangganBaru['email'] = $pelanggan->email ?? '';
                $this->pelangganBaru['detail_alamat'] = AddressMetadata::getDetailAlamat($pelanggan);
                $this->pelangganBaru['kelurahan'] = AddressMetadata::getKelurahan($pelanggan);
                $this->pelangganBaru['kecamatan'] = AddressMetadata::getKecamatan($pelanggan);
                $this->pelangganBaru['kabupaten_kota'] = AddressMetadata::getKabupatenKota($pelanggan)
                    ?: RegionalLocation::getRegencyName();
                $this->pelangganBaru['provinsi'] = AddressMetadata::getProvinsi($pelanggan)
                    ?: RegionalLocation::getProvinceName();
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
            ];
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
            ];
        }
    }

    public function updatedFormDataDiskon(): void
    {
        $diskon = (int) ($this->formData['diskon'] ?? 0);

        // Update totalGrandTotal di multiLayananData
        $this->multiLayananData['totalGrandTotal'] = $this->multiLayananData['totalSubtotal'] - $diskon;

        // Update formData total
        $this->formData['total'] = $this->multiLayananData['totalGrandTotal'];
        $this->formData['subtotal'] = $this->multiLayananData['totalSubtotal'];
    }

    protected function calculateTanggalSelesaiFromMultiLayanan(): void
    {
        // Create temporary transaksi object untuk menggunakan TransaksiHelper
        $tempTransaksi = new Transaksi();
        $tempTransaksi->tanggal_masuk = $this->formData['tanggal_masuk'];
        $tempTransaksi->setRelation('transaksiLayanan', collect($this->multiLayananData['items'])->map(function ($item) {
            if (!empty($item['layanan_id'])) {
                $tempTransaksiLayanan = new TransaksiLayanan();
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
        }

        // VALIDASI PELANGGAN EXISTING (jika mode pilih pelanggan)
        if (!$this->isPelangganBaru && empty($this->formData['pelanggan_id'])) {
            Log::warning('Kasir validation failed: pelanggan_id kosong');
            $this->error('Pilih pelanggan terlebih dahulu!', position: 'toast-bottom');
            return;
        }

        // Validasi metode pembayaran
        if (!TransaksiHelper::isValidMetodePembayaran($this->formData['metode_pembayaran'])) {
            Log::warning('Kasir validation failed: metode_pembayaran invalid', [
                'metode_pembayaran' => $this->formData['metode_pembayaran'],
            ]);
            $this->error('Metode pembayaran tidak valid!', position: 'toast-bottom');
            return;
        }

        // Validasi status
        if (!TransaksiHelper::isValidStatus($this->formData['status'])) {
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
            if (!empty($item['layanan_id'])) {
                if ($item['tipe_layanan'] === 'per_kg') {
                    if (empty($item['berat_kg']) || $item['berat_kg'] < $minBeratKg) {
                        Log::warning('Kasir validation failed: berat_kg invalid', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                            'berat_kg' => $item['berat_kg'] ?? null,
                            'min_berat_kg_required' => $minBeratKg,
                        ]);
                        $this->error("Berat minimal {$minBeratKg} kg untuk layanan " . $item['nama_layanan'] . '!', position: 'toast-bottom');
                        return;
                    }
                    if (empty($item['jenis_pakaian']) || count($item['jenis_pakaian']) === 0) {
                        Log::warning('Kasir validation failed: jenis_pakaian kosong', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                        ]);
                        $this->error('Jenis pakaian wajib diisi untuk layanan ' . $item['nama_layanan'] . '!', position: 'toast-bottom');
                        return;
                    }
                } else {
                    if (empty($item['jumlah_satuan']) || $item['jumlah_satuan'] < 1) {
                        Log::warning('Kasir validation failed: jumlah_satuan invalid', [
                            'layanan_index' => $index,
                            'layanan_nama' => $item['nama_layanan'] ?? '',
                            'jumlah_satuan' => $item['jumlah_satuan'] ?? null,
                        ]);
                        $this->error('Jumlah minimal 1 untuk layanan ' . $item['nama_layanan'] . '!', position: 'toast-bottom');
                        return;
                    }
                }
                $hasValidLayanan = true;
            }
        }

        if (!$hasValidLayanan) {
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

                // Simpan detail transaksi layanan
                foreach ($this->multiLayananData['items'] as $index => $item) {
                    if (!empty($item['layanan_id'])) {
                        // Get layanan data untuk backup jika item data kosong
                        $layanan = Layanan::find($item['layanan_id']);

                        if (!$layanan) {
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
                            $transaksiLayananData['jenis_pakaian'] = !empty($item['jenis_pakaian']) ? $item['jenis_pakaian'] : null;
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
                            $transaksiLayanan = TransaksiLayanan::create($transaksiLayananData);
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

                // Update total transaksi pelanggan dengan lock
                $pelanggan = Pelanggan::lockForUpdate()->find($this->formData['pelanggan_id']);
                if ($pelanggan) {
                    $pelanggan->increment('total_transaksi');
                } else {
                    Log::warning('Kasir: Pelanggan not found for increment', [
                        'pelanggan_id' => $this->formData['pelanggan_id'],
                    ]);
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
        if (!empty($kode)) {
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

    public function getKecamatanOptions(): array
    {
        // Get kecamatan di Kota Kendari dari API menggunakan RegionalLocation helper
        $districts = RegionalLocation::getKendariDistricts();

        // Transform ke format yang dibutuhkan oleh x-select component
        return collect($districts)->map(fn (array $district) => [
            'id' => $district['name'] ?? '',
            'name' => $district['name'] ?? '',
        ])->toArray();
    }

    public function getKelurahanOptions(): array
    {
        // Kelurahan berdasarkan kecamatan yang dipilih menggunakan RegionalLocation Helper
        $kecamatanName = $this->pelangganBaru['kecamatan'] ?? '';

        if (empty($kecamatanName)) {
            return [];
        }

        // Cari district code berdasarkan nama kecamatan
        $districts = RegionalLocation::getKendariDistricts();
        $districtCode = null;

        foreach ($districts as $district) {
            if (($district['name'] ?? '') === $kecamatanName) {
                $districtCode = $district['code'] ?? null;
                break;
            }
        }

        if (!$districtCode) {
            return [];
        }

        // Get kelurahan/desa berdasarkan district code dari API
        $villages = RegionalLocation::getVillagesByDistrict($districtCode);

        // Transform ke format yang dibutuhkan oleh x-select component
        return collect($villages)->map(fn (array $village) => [
            'id' => $village['name'] ?? '',
            'name' => $village['name'] ?? '',
        ])->toArray();
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

    public function render()
    {
        return view('livewire.management.kasir', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
            'kecamatanOptions' => $this->getKecamatanOptions(),
            'kelurahanOptions' => $this->getKelurahanOptions(),
            'metodePembayaranOptions' => $this->getMetodePembayaranOptions(),
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }
}
