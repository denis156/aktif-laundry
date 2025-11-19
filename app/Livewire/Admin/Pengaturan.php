<?php

namespace App\Livewire\Admin;

use Exception;
use Mary\Traits\Toast;
use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('Pengaturan')]

#[Layout('layouts.admin.app')]
class Pengaturan extends Component
{
    use Toast;

    public array $settings = [
        'nama_toko' => '',
        'whatsapp' => '',
        'email' => '',
        'jam_buka' => '',
        'jam_tutup' => '',
        'format_id_jenis_pakaian' => '',
        'format_id_layanan' => '',
        'format_id_pelanggan' => '',
        'format_id_transaksi' => '',
    ];

    public function mount()
    {
        $this->loadSettings();
    }

    protected function loadSettings()
    {
        $this->settings = [
            'nama_toko' => Setting::get('nama_toko', 'Aktif Laundry'),
            'whatsapp' => Setting::get('whatsapp', ''),
            'email' => Setting::get('email', ''),
            'jam_buka' => Setting::get('jam_buka', '08:00'),
            'jam_tutup' => Setting::get('jam_tutup', '21:00'),
            'format_id_jenis_pakaian' => Setting::get('format_id_jenis_pakaian', 'JNS'),
            'format_id_layanan' => Setting::get('format_id_layanan', 'LYN'),
            'format_id_pelanggan' => Setting::get('format_id_pelanggan', 'PLG'),
            'format_id_transaksi' => Setting::get('format_id_transaksi', 'TRX'),
        ];
    }

    public function save()
    {
        // Validasi
        $this->validate([
            'settings.nama_toko' => 'required|string|max:255',
            'settings.whatsapp' => 'required|string|max:15',
            'settings.email' => 'required|email',
            'settings.jam_buka' => 'required',
            'settings.jam_tutup' => 'required',
            'settings.format_id_jenis_pakaian' => 'required|string|max:10',
            'settings.format_id_layanan' => 'required|string|max:10',
            'settings.format_id_pelanggan' => 'required|string|max:10',
            'settings.format_id_transaksi' => 'required|string|max:10',
        ], [
            'settings.nama_toko.required' => 'Nama toko wajib diisi',
            'settings.whatsapp.required' => 'Nomor WhatsApp wajib diisi',
            'settings.email.required' => 'Email wajib diisi',
            'settings.email.email' => 'Format email tidak valid',
            'settings.jam_buka.required' => 'Jam buka wajib diisi',
            'settings.jam_tutup.required' => 'Jam tutup wajib diisi',
            'settings.format_id_jenis_pakaian.required' => 'Format ID Jenis Pakaian wajib diisi',
            'settings.format_id_layanan.required' => 'Format ID Layanan wajib diisi',
            'settings.format_id_pelanggan.required' => 'Format ID Pelanggan wajib diisi',
            'settings.format_id_transaksi.required' => 'Format ID Transaksi wajib diisi',
        ]);

        try {
            // Simpan semua settings
            Setting::set('nama_toko', $this->settings['nama_toko'], 'Nama toko laundry');
            Setting::set('whatsapp', $this->settings['whatsapp'], 'Nomor WhatsApp (format: 8xxx tanpa 0)');
            Setting::set('email', $this->settings['email'], 'Email toko');
            Setting::set('jam_buka', $this->settings['jam_buka'], 'Jam buka toko');
            Setting::set('jam_tutup', $this->settings['jam_tutup'], 'Jam tutup toko');
            Setting::set('format_id_jenis_pakaian', $this->settings['format_id_jenis_pakaian'], 'Format ID untuk Jenis Pakaian');
            Setting::set('format_id_layanan', $this->settings['format_id_layanan'], 'Format ID untuk Layanan');
            Setting::set('format_id_pelanggan', $this->settings['format_id_pelanggan'], 'Format ID untuk Pelanggan');
            Setting::set('format_id_transaksi', $this->settings['format_id_transaksi'], 'Format ID untuk Transaksi');

            $this->success('Pengaturan berhasil disimpan!', position: 'toast-bottom');
        } catch (Exception $e) {
            $this->error('Gagal menyimpan pengaturan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.admin.pengaturan');
    }
}
