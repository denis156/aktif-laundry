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
        'layanan_id' => '',
        'nama_layanan' => '',
        'jenis_pakaian' => '',
        'berat_kg' => '',
        'harga_per_kg' => '',
        'subtotal' => 0,
        'diskon' => 0,
        'total' => 0,
        'metode_pembayaran' => 'Tunai',
        'tanggal_selesai' => '',
        'status' => 'Menunggu',
        'catatan' => '',
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

    // Listener untuk event dari component KeyValueJenisPakaian
    protected $listeners = ['jenisPakaianUpdated'];

    public function jenisPakaianUpdated($value)
    {
        $this->formData['jenis_pakaian'] = $value;
    }

    public function mount()
    {
        $this->refreshKodeTransaksi();
        $this->search();
    }

    protected function resetForm()
    {
        $this->formData = [
            'kode_transaksi' => $this->generateKode(),
            'tanggal_masuk' => now()->format('Y-m-d H:i'),
            'kasir_id' => Auth::id(),
            'pelanggan_id' => '',
            'nama_pelanggan' => '',
            'layanan_id' => '',
            'nama_layanan' => '',
            'jenis_pakaian' => '',
            'berat_kg' => '',
            'harga_per_kg' => '',
            'subtotal' => 0,
            'diskon' => 0,
            'total' => 0,
            'metode_pembayaran' => 'Tunai',
            'tanggal_selesai' => '',
            'status' => 'Menunggu',
            'catatan' => '',
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

    public function updatedFormDataLayananId($value)
    {
        if ($value) {
            $layanan = Layanan::find($value);
            if ($layanan) {
                $this->formData['nama_layanan'] = $layanan->nama_layanan;
                $this->formData['harga_per_kg'] = $layanan->harga_per_kg;

                // Auto-fill tanggal selesai berdasarkan durasi layanan
                if ($layanan->durasi_jam) {
                    $this->calculateTanggalSelesai($layanan->durasi_jam);
                }

                $this->calculateTotal();
            }
        }
    }

    public function updatedFormDataBeratKg($value)
    {
        // Normalize input: replace comma with dot for decimal separator
        if (is_string($value)) {
            $this->formData['berat_kg'] = str_replace(',', '.', $value);
        }

        $this->calculateTotal();
    }

    public function updatedFormDataDiskon()
    {
        $this->calculateTotal();
    }

    protected function calculateTotal()
    {
        $berat = (float) ($this->formData['berat_kg'] ?? 0);
        $harga = (float) ($this->formData['harga_per_kg'] ?? 0);
        $diskon = (float) ($this->formData['diskon'] ?? 0);

        $this->formData['subtotal'] = $berat * $harga;
        $this->formData['total'] = $this->formData['subtotal'] - $diskon;
    }

    protected function calculateTanggalSelesai($durasiJam)
    {
        if (!empty($this->formData['tanggal_masuk']) && $durasiJam > 0) {
            try {
                $tanggalMasuk = Carbon::parse($this->formData['tanggal_masuk']);
                $tanggalSelesai = $tanggalMasuk->addHours($durasiJam);
                $this->formData['tanggal_selesai'] = $tanggalSelesai->format('Y-m-d H:i');
            } catch (Exception $e) {
                $this->formData['tanggal_selesai'] = '';
            }
        }
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

        if (empty($this->formData['layanan_id'])) {
            $this->error('Pilih layanan terlebih dahulu!', position: 'toast-bottom');
            return;
        }

        if (empty($this->formData['berat_kg']) || $this->formData['berat_kg'] < 0.5) {
            $this->error('Berat minimal 0.5 kg!', position: 'toast-bottom');
            return;
        }

        if (empty($this->formData['jenis_pakaian'])) {
            $this->error('Jenis pakaian wajib diisi!', position: 'toast-bottom');
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
                $transaksi = Transaksi::create($this->formData);

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

    public function updatedFormDataTanggalMasuk($value)
    {
        if ($value && $this->formData['layanan_id']) {
            $layanan = Layanan::find($this->formData['layanan_id']);
            if ($layanan && $layanan->durasi_jam) {
                $this->calculateTanggalSelesai($layanan->durasi_jam);
            }
        }
    }

    public function getPelangganOptions()
    {
        return Pelanggan::where('status', 'Aktif')
            ->orderBy('nama')
            ->get()
            ->map(fn($p) => [
                'id' => (string) $p->id,
                'nama' => $p->nama,
                'no_hp' => $p->no_hp,
            ])
            ->toArray();
    }

    public function getLayananOptions()
    {
        return Layanan::where('status', 'Aktif')
            ->orderBy('nama_layanan')
            ->get()
            ->map(fn($l) => ['id' => $l->id, 'name' => $l->nama_layanan . ' - Rp ' . number_format($l->harga_per_kg, 0, ',', '.')])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.kasir', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
        ]);
    }
}
