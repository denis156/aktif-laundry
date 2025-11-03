<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

#[Title('Pengaturan')]
class Pengaturan extends Component
{
    use Toast;

    public array $settings = [
        'nama_toko' => '',
        'whatsapp' => '',
        'email' => '',
        'jam_buka' => '',
        'jam_tutup' => '',
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
        ], [
            'settings.nama_toko.required' => 'Nama toko wajib diisi',
            'settings.whatsapp.required' => 'Nomor WhatsApp wajib diisi',
            'settings.email.required' => 'Email wajib diisi',
            'settings.email.email' => 'Format email tidak valid',
            'settings.jam_buka.required' => 'Jam buka wajib diisi',
            'settings.jam_tutup.required' => 'Jam tutup wajib diisi',
        ]);

        try {
            // Simpan semua settings
            Setting::set('nama_toko', $this->settings['nama_toko'], 'Nama toko laundry');
            Setting::set('whatsapp', $this->settings['whatsapp'], 'Nomor WhatsApp (format: 8xxx tanpa 0)');
            Setting::set('email', $this->settings['email'], 'Email toko');
            Setting::set('jam_buka', $this->settings['jam_buka'], 'Jam buka toko');
            Setting::set('jam_tutup', $this->settings['jam_tutup'], 'Jam tutup toko');

            $this->success('Pengaturan berhasil disimpan!', position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Gagal menyimpan pengaturan: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.pengaturan');
    }
}
