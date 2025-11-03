<?php

namespace App\Livewire\Transaksi;

use Mary\Traits\Toast;
use App\Models\Layanan;
use Livewire\Component;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

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

    protected $listeners = ['jenisPakaianUpdated'];

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
            $transaksi = Transaksi::findOrFail($this->transaksiId);

            $this->formData = [
                'kode_transaksi' => $transaksi->kode_transaksi,
                'tanggal_masuk' => $transaksi->tanggal_masuk->format('Y-m-d\TH:i'),
                'kasir_id' => $transaksi->kasir_id,
                'pelanggan_id' => $transaksi->pelanggan_id,
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'layanan_id' => $transaksi->layanan_id,
                'nama_layanan' => $transaksi->nama_layanan,
                'jenis_pakaian' => is_array($transaksi->jenis_pakaian)
                    ? json_encode($transaksi->jenis_pakaian)
                    : $transaksi->jenis_pakaian,
                'berat_kg' => (float) $transaksi->berat_kg,
                'harga_per_kg' => $transaksi->harga_per_kg,
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
        } catch (\Exception $e) {
            $this->error('Transaksi tidak ditemukan', position: 'toast-bottom');
            return $this->redirect('/transaksi', navigate: true);
        }
    }

    public function jenisPakaianUpdated($outputString)
    {
        $this->formData['jenis_pakaian'] = $outputString;
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

    public function printReceipt()
    {
        // Redirect ke halaman print receipt di tab baru
        $this->dispatch('open-print-window', url: route('receipt.print', ['id' => $this->transaksiId]));
    }

    public function save()
    {
        $this->validate([
            'formData.tanggal_masuk' => 'required|date',
            'formData.pelanggan_id' => 'required|exists:pelanggan,id',
            'formData.layanan_id' => 'required|exists:layanan,id',
            'formData.jenis_pakaian' => 'required|string',
            'formData.berat_kg' => 'required|numeric|min:0.1',
            'formData.harga_per_kg' => 'required|integer|min:0',
            'formData.metode_pembayaran' => 'required|in:Tunai,Transfer,QRIS,Debit',
            'formData.status' => 'required|in:Menunggu,Proses,Selesai,Diambil,Batal',
        ]);

        try {
            $transaksi = Transaksi::findOrFail($this->transaksiId);
            $this->formData['kasir_id'] = Auth::id();
            $transaksi->update($this->formData);

            $this->success('Transaksi berhasil diupdate!', position: 'toast-bottom');
            return $this->redirect('/transaksi', navigate: true);
        } catch (\Exception $e) {
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
