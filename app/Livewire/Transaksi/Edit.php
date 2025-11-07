<?php

namespace App\Livewire\Transaksi;

use Exception;
use Mary\Traits\Toast;
use App\Models\Layanan;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Livewire\Attributes\Title;
use App\Models\TransaksiLayanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

#[Title('Edit Transaksi')]
class Edit extends Component
{
    use Toast;

    public int $transaksiId;
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

    public function mount($id)
    {
        $this->transaksiId = $id;
        $this->loadTransaksi();
        $this->search();
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

    protected function loadTransaksi()
    {
        try {
            $transaksi = Transaksi::with(['transaksiLayanan.layanan'])->findOrFail($this->transaksiId);

            $this->formData = [
                'kode_transaksi' => $transaksi->kode_transaksi,
                'tanggal_masuk' => $transaksi->tanggal_masuk->format('Y-m-d\TH:i'),
                'kasir_id' => $transaksi->kasir_id,
                'pelanggan_id' => $transaksi->pelanggan_id,
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

            // Load multi-layanan data
            $this->loadMultiLayananData($transaksi);
        } catch (Exception $e) {
            $this->error('Transaksi tidak ditemukan', position: 'toast-bottom');
            return $this->redirect('/admin/transaksi', navigate: true);
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
                if (!$layanan) {
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

                if ($tl->isPerKg()) {
                    $item['berat_kg'] = $tl->berat_kg ?? 0;
                    $item['harga_per_kg'] = $tl->harga_per_kg ?? $layanan->harga_per_kg ?? 0;

                    // Decode jenis_pakaian from JSON
                    if (!empty($tl->jenis_pakaian)) {
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
                        if (!empty($transaksi->jenis_pakaian)) {
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
                } catch (Exception $e) {
                    Log::error('Error calculating tanggal selesai: ' . $e->getMessage());
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

    public function updatedFormDataDiskon()
    {
        $this->formData['total'] = (float) $this->formData['subtotal'] - (float) $this->formData['diskon'];
    }

    public function printReceipt()
    {
        // Redirect ke halaman print receipt di tab baru
        $this->dispatch('open-print-window', url: route('receipt.print', ['id' => $this->transaksiId]));
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
            'formData.tanggal_masuk' => 'required|date',
            'formData.pelanggan_id' => 'required|exists:pelanggan,id',
            'formData.metode_pembayaran' => 'required|in:Tunai,Transfer,QRIS,Debit',
            'formData.status' => 'required|in:Menunggu,Proses,Selesai,Diambil,Batal',
        ]);

        try {
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function() {
                $transaksi = Transaksi::findOrFail($this->transaksiId);

                // Prepare transaksi data
                $transaksiData = $this->formData;
                $transaksiData['kasir_id'] = Auth::id();
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

                // Hapus transaksi layanan lama (force delete untuk menghindari duplikasi)
                DB::table('transaksi_layanan')->where('transaksi_id', $transaksi->id)->delete();

                // Simpan detail transaksi layanan baru
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
            });

            $this->success('Transaksi berhasil diupdate!', position: 'toast-bottom');
            return $this->redirect('/admin/transaksi', navigate: true);
        } catch (QueryException $e) {
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
        return view('livewire.transaksi.edit', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
        ]);
    }
}
