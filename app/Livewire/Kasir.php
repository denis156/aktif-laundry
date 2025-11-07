<?php

namespace App\Livewire;

use Exception;
use Carbon\Carbon;
use Mary\Traits\Toast;
use App\Models\Layanan;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

#[Title('Kasir')]
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

    public string $lastTransactionId = '';
    public bool $showReceipt = false;
    public array $pelangganOptions = [];

    // Toggle antara pilih pelanggan existing atau input pelanggan baru
    public bool $isPelangganBaru = false;

    // Form data untuk pelanggan baru
    public array $pelangganBaru = [
        'nama' => '',
        'no_hp' => '',
        'alamat' => '',
        'email' => '',
    ];

    // Listener untuk event dari component
    protected $listeners = ['multiLayananUpdated'];

    public function multiLayananUpdated($data)
    {
        $this->multiLayananData = $data;
        $this->formData['subtotal'] = $data['totalSubtotal'];
        $this->formData['total'] = $data['totalGrandTotal'] - $this->formData['diskon'];

        // Calculate tanggal selesai based on layanan with longest duration
        $this->calculateTanggalSelesaiFromMultiLayanan();
    }

    public function mount()
    {
        $this->refreshKodeTransaksi();
        $this->formData['tanggal_masuk'] = now()->format('Y-m-d\TH:i');
        $this->formData['kasir_id'] = Auth::id();
        $this->search();
    }

    protected function resetForm()
    {
        $this->formData = [
            'kode_transaksi' => $this->generateKode(),
            'tanggal_masuk' => now()->format('Y-m-d\TH:i'),
            'kasir_id' => Auth::id(),
            'pelanggan_id' => '',
            'nama_pelanggan' => '',
            'diskon' => 0,
            'subtotal' => 0,
            'total' => 0,
            'metode_pembayaran' => 'Tunai',
            'tanggal_selesai' => '',
            'status' => 'Menunggu',
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
            'alamat' => '',
            'email' => '',
        ];

        $this->isPelangganBaru = false;
    }

    protected function generateKode(): string
    {
        $prefix = Setting::get('format_id_transaksi', 'TRX');
        $prefixLength = strlen($prefix);

        $lastTransaksi = Transaksi::withTrashed()->orderBy('kode_transaksi', 'desc')->first();

        if (!$lastTransaksi) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastTransaksi->kode_transaksi, $prefixLength);

        // Check if there are any gaps in the numbering by finding the next available number
        $nextNumber = $lastNumber + 1;

        // Verify if this number is already used (in case of deletions)
        while (Transaksi::where('kode_transaksi', $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
            $nextNumber++;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function refreshKodeTransaksi()
    {
        $this->formData['kode_transaksi'] = $this->generateKode();
    }

    public function search(string $term = '')
    {
        try {
            $selected = Pelanggan::where('id', $this->formData['pelanggan_id'])->get();

            $this->pelangganOptions = Pelanggan::query()
                ->where('nama', 'like', "%{$term}%")
                ->orWhere('no_hp', 'like', "%{$term}%")
                ->take(10)
                ->orderBy('nama')
                ->get()
                ->merge($selected)
                ->map(fn($p) => [
                    'id' => (string) $p->id,
                    'nama' => $p->nama,
                    'no_hp' => $p->no_hp,
                ])
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error searching pelanggan: ' . $e->getMessage());
            $this->pelangganOptions = [];
        }
    }

    public function updatedFormDataPelangganId($value)
    {
        if ($value) {
            $pelanggan = Pelanggan::find($value);
            if ($pelanggan) {
                $this->formData['nama_pelanggan'] = $pelanggan->nama;

                // Auto-fill form pelanggan dengan data yang dipilih (untuk ditampilkan di disabled fields)
                $this->pelangganBaru['nama'] = $pelanggan->nama;
                $this->pelangganBaru['no_hp'] = $pelanggan->no_hp;
                $this->pelangganBaru['email'] = $pelanggan->email ?? '';
                $this->pelangganBaru['alamat'] = $pelanggan->alamat ?? '';
            }
        }
    }

    public function updatedIsPelangganBaru($value)
    {
        if ($value) {
            // Saat toggle ke mode "Pelanggan Baru", clear form pelanggan
            $this->pelangganBaru = [
                'nama' => '',
                'no_hp' => '',
                'alamat' => '',
                'email' => '',
            ];
            // Clear juga pilihan pelanggan
            $this->formData['pelanggan_id'] = '';
            $this->formData['nama_pelanggan'] = '';
        } else {
            // Saat toggle ke mode "Pilih Pelanggan", clear form pelanggan juga
            $this->pelangganBaru = [
                'nama' => '',
                'no_hp' => '',
                'alamat' => '',
                'email' => '',
            ];
        }
    }

    public function updatedFormDataDiskon()
    {
        $this->formData['total'] = $this->multiLayananData['totalGrandTotal'] - $this->formData['diskon'];
    }

    protected function calculateTanggalSelesaiFromMultiLayanan()
    {
        $tanggalTerlama = null;

        foreach ($this->multiLayananData['items'] as $item) {
            if (!empty($item['layanan_id'])) {
                $layanan = Layanan::find($item['layanan_id']);
                if ($layanan && $layanan->durasi_jam > 0) {
                    $tanggalSelesai = Carbon::parse($this->formData['tanggal_masuk'])
                        ->addHours($layanan->durasi_jam);

                    if (!$tanggalTerlama || $tanggalSelesai > $tanggalTerlama) {
                        $tanggalTerlama = $tanggalSelesai;
                    }
                }
            }
        }

        $this->formData['tanggal_selesai'] = $tanggalTerlama ? $tanggalTerlama->format('Y-m-d H:i') : '';
    }


    public function save()
    {
        // Jika mode pelanggan baru, simpan pelanggan dulu
        if ($this->isPelangganBaru) {
            if (empty($this->pelangganBaru['nama'])) {
                $this->error('Nama pelanggan wajib diisi!', position: 'toast-bottom');
                return;
            }

            if (empty($this->pelangganBaru['no_hp'])) {
                $this->error('Nomor HP wajib diisi!', position: 'toast-bottom');
                return;
            }

            $this->savePelangganBaru();

            if ($this->isPelangganBaru) {
                return;
            }
        }

        // Validasi transaksi
        if (empty($this->formData['pelanggan_id'])) {
            $this->error('Pilih pelanggan terlebih dahulu!', position: 'toast-bottom');
            return;
        }

        // Validasi multi-layanan
        if (empty($this->multiLayananData['items'])) {
            $this->error('Tambahkan minimal 1 layanan!', position: 'toast-bottom');
            return;
        }

        $hasValidLayanan = false;
        foreach ($this->multiLayananData['items'] as $item) {
            if (!empty($item['layanan_id'])) {
                if ($item['tipe_layanan'] === 'per_kg') {
                    if (empty($item['berat_kg']) || $item['berat_kg'] < 0.1) {
                        $this->error('Berat minimal 0.1 kg untuk layanan ' . $item['nama_layanan'] . '!', position: 'toast-bottom');
                        return;
                    }
                    if (empty($item['jenis_pakaian']) || count($item['jenis_pakaian']) === 0) {
                        $this->error('Jenis pakaian wajib diisi untuk layanan ' . $item['nama_layanan'] . '!', position: 'toast-bottom');
                        return;
                    }
                } else {
                    if (empty($item['jumlah_satuan']) || $item['jumlah_satuan'] < 1) {
                        $this->error('Jumlah minimal 1 untuk layanan ' . $item['nama_layanan'] . '!', position: 'toast-bottom');
                        return;
                    }
                }
                $hasValidLayanan = true;
            }
        }

        if (!$hasValidLayanan) {
            $this->error('Pilih layanan yang valid terlebih dahulu!', position: 'toast-bottom');
            return;
        }

        try {
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function() {
                // ID Kasir
                $this->formData['kasir_id'] = Auth::id() ?? 1;

                // Cek ulang apakah kode transaksi sudah ada, jika ya generate ulang
                if (Transaksi::where('kode_transaksi', $this->formData['kode_transaksi'])->exists()) {
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

                        $transaksiLayananData = [
                            'transaksi_id' => $transaksi->id,
                            'layanan_id' => $item['layanan_id'],
                            'nama_layanan' => $item['nama_layanan'] ?? ($layanan ? $layanan->nama_layanan : ''),
                            'subtotal' => $item['subtotal'] ?? 0,
                        ];

                        if ($item['tipe_layanan'] === 'per_kg') {
                            // Prepare data untuk per_kg
                            $transaksiLayananData['jenis_pakaian'] = !empty($item['jenis_pakaian']) ? json_encode($item['jenis_pakaian']) : null;
                            $transaksiLayananData['berat_kg'] = $item['berat_kg'] ?? 0;
                            $transaksiLayananData['harga_per_kg'] = $item['harga_per_kg'] ?? ($layanan ? $layanan->harga_per_kg : 0);

                            // Calculate subtotal jika belum ada
                            if (empty($item['subtotal'])) {
                                $berat = (float) ($item['berat_kg'] ?? 0);
                                $harga = (int) ($transaksiLayananData['harga_per_kg']);
                                $transaksiLayananData['subtotal'] = $berat * $harga;
                            }
                        } else {
                            // Prepare data untuk per_satuan
                            $transaksiLayananData['jumlah_satuan'] = $item['jumlah_satuan'] ?? 1;
                            $transaksiLayananData['harga_per_satuan'] = $item['harga_per_satuan'] ?? ($layanan ? $layanan->harga_per_satuan : 0);

                            // Calculate subtotal jika belum ada
                            if (empty($item['subtotal'])) {
                                $jumlah = (int) ($item['jumlah_satuan'] ?? 1);
                                $harga = (int) ($transaksiLayananData['harga_per_satuan']);
                                $transaksiLayananData['subtotal'] = $jumlah * $harga;
                            }
                        }

                        // Create TransaksiLayanan
                        DB::table('transaksi_layanan')->insert($transaksiLayananData);
                    }
                }

                // Update total transaksi pelanggan dengan lock
                $pelanggan = Pelanggan::lockForUpdate()->find($this->formData['pelanggan_id']);
                if ($pelanggan) {
                    $pelanggan->increment('total_transaksi');
                }

                // Simpan kode transaksi terakhir untuk struk
                $this->lastTransactionId = $transaksi->kode_transaksi;
            });

            $this->success('Transaksi berhasil disimpan!', position: 'toast-bottom');

            // Reset form untuk transaksi baru
            $this->resetForm();

            // Tampilkan pilihan cetak struk
            $this->showReceipt = true;
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                $this->refreshKodeTransaksi();
                $this->success('Kode transaksi di-regenerate, silakan coba lagi', position: 'toast-bottom');
                return;
            }
            $this->error('Gagal menyimpan transaksi: ' . $e->getMessage(), timeout: 10000, position: 'toast-bottom');
        } catch (Exception $e) {
            $this->error('Gagal menyimpan transaksi: ' . $e->getMessage(), timeout: 10000, position: 'toast-bottom');
        }
    }


    public function printReceipt($transactionId = null)
    {
        $kode = $transactionId ?? $this->lastTransactionId;
        if (!empty($kode)) {
            // Cari transaksi by kode untuk dapat ID
            $transaksi = Transaksi::where('kode_transaksi', $kode)->first();
            if ($transaksi) {
                $this->dispatch('open-print-window', url: route('receipt.print', ['id' => $transaksi->id]));
                $this->showReceipt = false;
            }
        }
    }

    public function batalTransaksi()
    {
        $this->resetForm();
        $this->success('Form direset', position: 'toast-bottom');
    }

    public function savePelangganBaru()
    {
        // Validasi sederhana
        if (empty($this->pelangganBaru['nama'])) {
            $this->error('Nama pelanggan wajib diisi!', position: 'toast-bottom');
            return;
        }

        if (empty($this->pelangganBaru['no_hp'])) {
            $this->error('Nomor HP wajib diisi!', position: 'toast-bottom');
            return;
        }

        try {
            // Gunakan transaction untuk pembuatan pelanggan baru
            DB::transaction(function() {
                // Generate kode pelanggan baru
                $prefix = Setting::get('format_id_pelanggan', 'PLG');
                $prefixLength = strlen($prefix);

                $lastPelanggan = Pelanggan::withTrashed()->orderBy('kode_pelanggan', 'desc')->first();

                if (!$lastPelanggan) {
                    $kodePelanggan = $prefix . '001';
                } else {
                    $lastNumber = (int) substr($lastPelanggan->kode_pelanggan, $prefixLength);
                    $nextNumber = $lastNumber + 1;

                    // Check if there are any gaps in the numbering by finding the next available number
                    while (Pelanggan::where('kode_pelanggan', $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                        $nextNumber++;
                    }

                    $kodePelanggan = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                }

                // Simpan pelanggan baru
                $pelanggan = Pelanggan::create([
                    'kode_pelanggan' => $kodePelanggan,
                    'nama' => $this->pelangganBaru['nama'],
                    'no_hp' => $this->pelangganBaru['no_hp'],
                    'alamat' => $this->pelangganBaru['alamat'] ?? '',
                    'email' => $this->pelangganBaru['email'] ?? '',
                    'tanggal_daftar' => $this->formData['tanggal_masuk'] ? \Carbon\Carbon::parse($this->formData['tanggal_masuk'])->format('Y-m-d H:i:s') : now(),
                    'total_transaksi' => 0,
                    'status' => 'Aktif',
                ]);

                $this->success("Pelanggan {$this->pelangganBaru['nama']} berhasil ditambahkan!", position: 'toast-bottom');

                // Auto-select pelanggan yang baru ditambahkan
                $this->formData['pelanggan_id'] = $pelanggan->id;
                $this->formData['nama_pelanggan'] = $pelanggan->nama;

                // Reset form pelanggan baru
                $this->pelangganBaru = [
                    'nama' => '',
                    'no_hp' => '',
                    'alamat' => '',
                    'email' => '',
                ];

                // Switch kembali ke mode pilih pelanggan existing
                $this->isPelangganBaru = false;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                $this->error('Kode pelanggan sudah ada, sistem akan mencoba generate ulang', position: 'toast-bottom');
                return;
            }
            $this->error('Gagal menyimpan pelanggan: ' . $e->getMessage(), position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Gagal menyimpan pelanggan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }


    public function getPelangganOptions()
    {
        try {
            return Pelanggan::where('status', 'Aktif')
                ->orderBy('nama')
                ->get()
                ->map(fn($p) => [
                    'id' => (string) $p->id,
                    'nama' => $p->nama,
                    'no_hp' => $p->no_hp,
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting pelanggan options: ' . $e->getMessage());
            return [];
        }
    }

    public function getLayananOptions()
    {
        try {
            return Layanan::where('status', 'Aktif')
                ->orderBy('nama_layanan')
                ->get()
                ->map(fn($l) => ['id' => $l->id, 'name' => $l->nama_layanan . ' - Rp ' . number_format($l->harga_per_kg, 0, ',', '.')])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting layanan options: ' . $e->getMessage());
            return [];
        }
    }

    public function render()
    {
        return view('livewire.kasir', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
        ]);
    }
}
