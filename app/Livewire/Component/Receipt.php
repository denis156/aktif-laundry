<?php

namespace App\Livewire\Component;

use Exception;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Pelanggan;
use App\Models\Transaksi;

class Receipt extends Component
{
    public int $transaksiId;
    public array $transaksiData = [];
    public array $setting = [];
    public string $pelangganAlamat = '';
    public string $pelangganNoHp = '';

    public function mount($id)
    {
        $this->transaksiId = $id;
        $this->loadReceiptData();
    }

    protected function loadReceiptData()
    {
        try {
            // Get Transaksi Data
            $transaksi = Transaksi::with(['pelanggan', 'layanan'])->findOrFail($this->transaksiId);

            $this->transaksiData = [
                'kode_transaksi' => $transaksi->kode_transaksi,
                'tanggal_masuk' => $transaksi->tanggal_masuk->format('Y-m-d H:i:s'),
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'nama_layanan' => $transaksi->nama_layanan,
                'jenis_pakaian' => $transaksi->jenis_pakaian,
                'berat_kg' => (float) $transaksi->berat_kg,
                'harga_per_kg' => (int) $transaksi->harga_per_kg,
                'subtotal' => (int) $transaksi->subtotal,
                'diskon' => (int) $transaksi->diskon,
                'total' => (int) $transaksi->total,
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'tanggal_selesai' => $transaksi->tanggal_selesai ? $transaksi->tanggal_selesai->format('Y-m-d H:i:s') : '',
                'status' => $transaksi->status,
                'catatan' => $transaksi->catatan ?? '',
            ];

            // Get Setting Data
            $this->setting = [
                'nama_toko' => Setting::get('nama_toko', 'Aktif Laundry'),
                'alamat' => Setting::get('alamat', ''),
                'telepon' => Setting::get('telepon', ''),
                'whatsapp' => Setting::get('whatsapp', ''),
                'email' => Setting::get('email', ''),
                'jam_buka' => Setting::get('jam_buka', '08:00'),
                'jam_tutup' => Setting::get('jam_tutup', '21:00'),
            ];

            // Get Pelanggan Data (Alamat & No HP)
            if ($transaksi->pelanggan) {
                $this->pelangganAlamat = $transaksi->pelanggan->alamat ?? '';
                $this->pelangganNoHp = $transaksi->pelanggan->no_hp ?? '';
            }

        } catch (Exception $e) {
            abort(404, 'Transaksi tidak ditemukan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.component.receipt', [
            'transaksiData' => $this->transaksiData,
            'setting' => $this->setting,
            'pelangganAlamat' => $this->pelangganAlamat,
            'pelangganNoHp' => $this->pelangganNoHp,
        ]);
    }

    /**
     * Method static untuk generate receipt data (untuk route non-Livewire)
     */
    public static function generateReceiptData($id)
    {
        try {
            // Get Transaksi Data
            $transaksi = Transaksi::with(['pelanggan', 'layanan'])->findOrFail($id);

            $transaksiData = [
                'kode_transaksi' => $transaksi->kode_transaksi,
                'tanggal_masuk' => $transaksi->tanggal_masuk->format('Y-m-d H:i:s'),
                'nama_pelanggan' => $transaksi->nama_pelanggan,
                'nama_layanan' => $transaksi->nama_layanan,
                'jenis_pakaian' => $transaksi->jenis_pakaian,
                'berat_kg' => (float) $transaksi->berat_kg,
                'harga_per_kg' => (int) $transaksi->harga_per_kg,
                'subtotal' => (int) $transaksi->subtotal,
                'diskon' => (int) $transaksi->diskon,
                'total' => (int) $transaksi->total,
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'tanggal_selesai' => $transaksi->tanggal_selesai ? $transaksi->tanggal_selesai->format('Y-m-d H:i:s') : '',
                'status' => $transaksi->status,
                'catatan' => $transaksi->catatan ?? '',
            ];

            // Get Setting Data
            $setting = [
                'nama_toko' => Setting::get('nama_toko', 'Aktif Laundry'),
                'alamat' => Setting::get('alamat', ''),
                'telepon' => Setting::get('telepon', ''),
                'whatsapp' => Setting::get('whatsapp', ''),
                'email' => Setting::get('email', ''),
                'jam_buka' => Setting::get('jam_buka', '08:00'),
                'jam_tutup' => Setting::get('jam_tutup', '21:00'),
            ];

            // Get Pelanggan Data (Alamat & No HP)
            $pelangganAlamat = '';
            $pelangganNoHp = '';
            if ($transaksi->pelanggan) {
                $pelangganAlamat = $transaksi->pelanggan->alamat ?? '';
                $pelangganNoHp = $transaksi->pelanggan->no_hp ?? '';
            }

            return [
                'transaksiData' => $transaksiData,
                'setting' => $setting,
                'pelangganAlamat' => $pelangganAlamat,
                'pelangganNoHp' => $pelangganNoHp,
            ];

        } catch (Exception $e) {
            abort(404, 'Transaksi tidak ditemukan: ' . $e->getMessage());
        }
    }
}
