<?php

namespace App\Livewire\Transaksi;

use Mary\Traits\Toast;
use App\Models\Layanan;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

#[Title('Tambah Transaksi')]
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

    public function mount()
    {
        $this->formData['kode_transaksi'] = $this->generateKode();
        $this->formData['tanggal_masuk'] = now()->format('Y-m-d\TH:i');
        $this->formData['kasir_id'] = Auth::id();
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

    protected function generateKode(): string
    {
        $prefix = Setting::get('format_id_transaksi', 'TRX');
        $prefixLength = strlen($prefix);

        $lastTransaksi = Transaksi::orderBy('kode_transaksi', 'desc')->first();

        if (!$lastTransaksi) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastTransaksi->kode_transaksi, $prefixLength);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
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

    public function save()
    {
        $this->validate([
            'formData.kode_transaksi' => 'required|unique:transaksi,kode_transaksi',
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
            Transaksi::create($this->formData);

            // Update total transaksi pelanggan
            $pelanggan = Pelanggan::find($this->formData['pelanggan_id']);
            if ($pelanggan) {
                $pelanggan->increment('total_transaksi');
            }

            $this->success('Transaksi berhasil ditambahkan!', position: 'toast-bottom');
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
        return view('livewire.transaksi.create', [
            'pelangganOptions' => $this->getPelangganOptions(),
            'layananOptions' => $this->getLayananOptions(),
        ]);
    }
}
