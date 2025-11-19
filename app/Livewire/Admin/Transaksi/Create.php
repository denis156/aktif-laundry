<?php

namespace App\Livewire\Admin\Transaksi;

use Exception;
use Mary\Traits\Toast;
use App\Models\Layanan;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

#[Title('Tambah Transaksi')]

#[Layout('layouts.admin.app')]
class Create extends Component
{
    use Toast;
    public array $pelangganOptions = [];

    public array $formData = [
        'kode_transaksi' => '',
        'tanggal_masuk' => '',
        'kasir_id' => null,
        'pelanggan_id' => '',
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

    public function mount()
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

    public function jenisPakaianUpdated($outputString)
    {
        // Legacy method - not used in multi-layanan
    }

    public function multiLayananUpdated($data)
    {
        $this->multiLayananData = $data;
        $this->formData['subtotal'] = $data['totalSubtotal'];
        $this->formData['total'] = $data['totalGrandTotal'] - (float) $this->formData['diskon'];

        // Calculate tanggal selesai based on layanan with longest duration
        $this->calculateTanggalSelesaiFromMultiLayanan();
    }

    protected function calculateTanggalSelesaiFromMultiLayanan()
    {
        $tanggalTerlama = null;

        foreach ($this->multiLayananData['items'] as $item) {
            if (!empty($item['layanan_id'])) {
                try {
                    $layanan = Layanan::find($item['layanan_id']);
                    if ($layanan && $layanan->durasi_jam > 0) {
                        $tanggalSelesai = \Carbon\Carbon::parse($this->formData['tanggal_masuk'])
                            ->addHours($layanan->durasi_jam);

                        if (!$tanggalTerlama || $tanggalSelesai > $tanggalTerlama) {
                            $tanggalTerlama = $tanggalSelesai;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error calculating tanggal selesai: ' . $e->getMessage());
                }
            }
        }

        $this->formData['tanggal_selesai'] = $tanggalTerlama ? $tanggalTerlama->format('Y-m-d H:i') : '';
    }

    public function updatedFormDataPelangganId($value)
    {
        if ($value) {
            $pelanggan = Pelanggan::find($value);
            if ($pelanggan) {
                $this->formData['nama_pelanggan'] = $pelanggan->nama;
            }
        }
    }

    public function updatedFormDataLayananId($value)
    {
        if ($value) {
            $layanan = Layanan::find($value);
            if ($layanan) {
                $this->formData['nama_layanan'] = $layanan->nama_layanan;
                $this->formData['harga_per_kg'] = $layanan->harga_per_kg;

                // Set tanggal selesai berdasarkan durasi layanan
                if ($layanan->durasi_jam) {
                    $this->formData['tanggal_selesai'] = now()
                        ->addHours($layanan->durasi_jam)
                        ->format('Y-m-d\TH:i');
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
        $this->formData['total'] = (float) $this->formData['subtotal'] - (float) $this->formData['diskon'];
    }

    public function save()
    {
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

        $this->validate([
            'formData.kode_transaksi' => 'required|unique:transaksi,kode_transaksi',
            'formData.tanggal_masuk' => 'required|date',
            'formData.pelanggan_id' => 'required|exists:pelanggan,id',
            'formData.metode_pembayaran' => 'required|in:Tunai,Transfer,QRIS,Debit',
            'formData.status' => 'required|in:Menunggu,Proses,Selesai,Diambil,Batal',
        ]);

        try {
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function() {
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

                // Cek ulang apakah kode transaksi sudah ada, jika ya generate ulang
                if (Transaksi::where('kode_transaksi', $transaksiData['kode_transaksi'])->exists()) {
                    $this->refreshKodeTransaksi();
                    $transaksiData['kode_transaksi'] = $this->formData['kode_transaksi'];
                }

                // Simpan transaksi
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
                        \DB::table('transaksi_layanan')->insert($transaksiLayananData);
                    }
                }

                // Update total transaksi pelanggan dengan lock
                $pelanggan = Pelanggan::lockForUpdate()->find($this->formData['pelanggan_id']);
                if ($pelanggan) {
                    $pelanggan->increment('total_transaksi');
                }
            });

            $this->success('Transaksi berhasil ditambahkan!', position: 'toast-bottom');
            return $this->redirect('/admin/transaksi', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                $this->refreshKodeTransaksi();
                $this->success('Kode transaksi di-regenerate, silakan coba lagi', position: 'toast-bottom');
                return;
            }
            $this->error('Gagal menyimpan transaksi: ' . $e->getMessage(), position: 'toast-bottom');
        } catch (Exception $e) {
            $this->error('Gagal menyimpan transaksi: ' . $e->getMessage(), position: 'toast-bottom');
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
        return view('livewire.admin.transaksi.create', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
        ]);
    }
}
