<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use Exception;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Helper\Database\PengaturanHelper;

#[Title('Pengaturan')]
#[Layout('layouts.management.app')]
class Pengaturan extends Component
{
    use Toast;

    // * General Settings
    public string $nama_toko = '';

    // * Contact Settings
    public string $whatsapp = '';

    public string $email = '';

    // * Operasional Settings
    public string $jam_buka = '';

    public string $jam_tutup = '';

    // * Format Settings
    public string $format_id_jenis_pakaian = '';

    public string $format_id_layanan = '';

    public string $format_id_pelanggan = '';

    public string $format_id_transaksi = '';

    public string $format_id_kurir = '';

    public string $format_id_pengiriman = '';

    public string $format_id_pembayaran = '';

    public string $format_id_promo = '';

    public string $format_id_referral = '';

    // * Pricing Settings
    public float $biaya_antar_per_km = 0;

    public float $min_berat_kg = 0;

    public float $pajak_persen = 0;

    // * Features Settings
    public bool $enable_referral = false;

    public bool $enable_promo = false;

    // * Original values for dirty checking
    private array $originalValues = [];

    // * Active tab state
    public string $activeTab = 'general';

    /**
     * Mount component and load all settings from database
     */
    public function mount(): void
    {
        $this->loadSettings();
        $this->saveOriginalValues();
    }

    /**
     * Load all settings from database using Pengaturan model
     */
    protected function loadSettings(): void
    {
        // General
        $this->nama_toko = (string) PengaturanHelper::getValue('nama_toko', 'Aktif Laundry');

        // Contact
        $this->whatsapp = (string) PengaturanHelper::getValue('whatsapp', '');
        $this->email = (string) PengaturanHelper::getValue('email', '');

        // Operasional
        $this->jam_buka = (string) PengaturanHelper::getValue('jam_buka', '08:00');
        $this->jam_tutup = (string) PengaturanHelper::getValue('jam_tutup', '21:00');

        // Format
        $this->format_id_jenis_pakaian = (string) PengaturanHelper::getValue('format_id_jenis_pakaian', 'JNS');
        $this->format_id_layanan = (string) PengaturanHelper::getValue('format_id_layanan', 'LYN');
        $this->format_id_pelanggan = (string) PengaturanHelper::getValue('format_id_pelanggan', 'PLG');
        $this->format_id_transaksi = (string) PengaturanHelper::getValue('format_id_transaksi', 'TRX');
        $this->format_id_kurir = (string) PengaturanHelper::getValue('format_id_kurir', 'KUR');
        $this->format_id_pengiriman = (string) PengaturanHelper::getValue('format_id_pengiriman', 'PNG');
        $this->format_id_pembayaran = (string) PengaturanHelper::getValue('format_id_pembayaran', 'PBY');
        $this->format_id_promo = (string) PengaturanHelper::getValue('format_id_promo', 'PROMO');
        $this->format_id_referral = (string) PengaturanHelper::getValue('format_id_referral', 'REF');

        // Pricing
        $this->biaya_antar_per_km = (float) PengaturanHelper::getValue('biaya_antar_per_km', 2000);
        $this->min_berat_kg = (float) PengaturanHelper::getValue('min_berat_kg', 2);
        $this->pajak_persen = (float) PengaturanHelper::getValue('pajak_persen', 10);

        // Features
        $this->enable_referral = (bool) PengaturanHelper::getValue('enable_referral', true);
        $this->enable_promo = (bool) PengaturanHelper::getValue('enable_promo', true);
    }

    /**
     * Save original values for dirty state tracking
     */
    protected function saveOriginalValues(): void
    {
        $this->originalValues = [
            'nama_toko' => $this->nama_toko,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'jam_buka' => $this->jam_buka,
            'jam_tutup' => $this->jam_tutup,
            'format_id_jenis_pakaian' => $this->format_id_jenis_pakaian,
            'format_id_layanan' => $this->format_id_layanan,
            'format_id_pelanggan' => $this->format_id_pelanggan,
            'format_id_transaksi' => $this->format_id_transaksi,
            'format_id_kurir' => $this->format_id_kurir,
            'format_id_pengiriman' => $this->format_id_pengiriman,
            'format_id_pembayaran' => $this->format_id_pembayaran,
            'format_id_promo' => $this->format_id_promo,
            'format_id_referral' => $this->format_id_referral,
            'biaya_antar_per_km' => $this->biaya_antar_per_km,
            'min_berat_kg' => $this->min_berat_kg,
            'pajak_persen' => $this->pajak_persen,
            'enable_referral' => $this->enable_referral,
            'enable_promo' => $this->enable_promo,
        ];
    }

    /**
     * Check if there are unsaved changes
     */
    public function hasChanges(): bool
    {
        // Return false if original values not set yet
        if (empty($this->originalValues)) {
            return false;
        }

        return $this->nama_toko !== ($this->originalValues['nama_toko'] ?? '')
            || $this->whatsapp !== ($this->originalValues['whatsapp'] ?? '')
            || $this->email !== ($this->originalValues['email'] ?? '')
            || $this->jam_buka !== ($this->originalValues['jam_buka'] ?? '')
            || $this->jam_tutup !== ($this->originalValues['jam_tutup'] ?? '')
            || $this->format_id_jenis_pakaian !== ($this->originalValues['format_id_jenis_pakaian'] ?? '')
            || $this->format_id_layanan !== ($this->originalValues['format_id_layanan'] ?? '')
            || $this->format_id_pelanggan !== ($this->originalValues['format_id_pelanggan'] ?? '')
            || $this->format_id_transaksi !== ($this->originalValues['format_id_transaksi'] ?? '')
            || $this->format_id_kurir !== ($this->originalValues['format_id_kurir'] ?? '')
            || $this->format_id_pengiriman !== ($this->originalValues['format_id_pengiriman'] ?? '')
            || $this->format_id_pembayaran !== ($this->originalValues['format_id_pembayaran'] ?? '')
            || $this->format_id_promo !== ($this->originalValues['format_id_promo'] ?? '')
            || $this->format_id_referral !== ($this->originalValues['format_id_referral'] ?? '')
            || $this->biaya_antar_per_km !== ($this->originalValues['biaya_antar_per_km'] ?? 0)
            || $this->min_berat_kg !== ($this->originalValues['min_berat_kg'] ?? 0)
            || $this->pajak_persen !== ($this->originalValues['pajak_persen'] ?? 0)
            || $this->enable_referral !== ($this->originalValues['enable_referral'] ?? false)
            || $this->enable_promo !== ($this->originalValues['enable_promo'] ?? false);
    }

    /**
     * Validate all settings based on their types
     */
    protected function validateSettings(): void
    {
        $this->validate([
            // General
            'nama_toko' => ['required', 'string', 'max:255'],

            // Contact
            'whatsapp' => ['required', 'string', 'max:15', 'regex:/^[0-9]+$/'],
            'email' => ['required', 'email', 'max:255'],

            // Operasional
            'jam_buka' => ['required', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'jam_tutup' => ['required', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],

            // Format
            'format_id_jenis_pakaian' => ['required', 'string', 'max:10'],
            'format_id_layanan' => ['required', 'string', 'max:10'],
            'format_id_pelanggan' => ['required', 'string', 'max:10'],
            'format_id_transaksi' => ['required', 'string', 'max:10'],
            'format_id_kurir' => ['required', 'string', 'max:10'],
            'format_id_pengiriman' => ['required', 'string', 'max:10'],
            'format_id_pembayaran' => ['required', 'string', 'max:10'],
            'format_id_promo' => ['required', 'string', 'max:10'],
            'format_id_referral' => ['required', 'string', 'max:10'],

            // Pricing
            'biaya_antar_per_km' => ['required', 'numeric', 'min:0'],
            'min_berat_kg' => ['required', 'numeric', 'min:0', 'max:100'],
            'pajak_persen' => ['required', 'numeric', 'min:0', 'max:100'],

            // Features
            'enable_referral' => ['boolean'],
            'enable_promo' => ['boolean'],
        ], [
            // General
            'nama_toko.required' => 'Nama toko wajib diisi',
            'nama_toko.max' => 'Nama toko maksimal 255 karakter',

            // Contact
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi',
            'whatsapp.max' => 'Nomor WhatsApp maksimal 15 digit',
            'whatsapp.regex' => 'Nomor WhatsApp hanya boleh berisi angka',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',

            // Operasional
            'jam_buka.required' => 'Jam buka wajib diisi',
            'jam_buka.regex' => 'Format jam buka tidak valid (HH:MM)',
            'jam_tutup.required' => 'Jam tutup wajib diisi',
            'jam_tutup.regex' => 'Format jam tutup tidak valid (HH:MM)',

            // Format
            'format_id_jenis_pakaian.required' => 'Format ID Jenis Pakaian wajib diisi',
            'format_id_jenis_pakaian.max' => 'Format ID Jenis Pakaian maksimal 10 karakter',
            'format_id_layanan.required' => 'Format ID Layanan wajib diisi',
            'format_id_layanan.max' => 'Format ID Layanan maksimal 10 karakter',
            'format_id_pelanggan.required' => 'Format ID Pelanggan wajib diisi',
            'format_id_pelanggan.max' => 'Format ID Pelanggan maksimal 10 karakter',
            'format_id_transaksi.required' => 'Format ID Transaksi wajib diisi',
            'format_id_transaksi.max' => 'Format ID Transaksi maksimal 10 karakter',
            'format_id_kurir.required' => 'Format ID Kurir wajib diisi',
            'format_id_kurir.max' => 'Format ID Kurir maksimal 10 karakter',
            'format_id_pengiriman.required' => 'Format ID Pengiriman wajib diisi',
            'format_id_pengiriman.max' => 'Format ID Pengiriman maksimal 10 karakter',
            'format_id_pembayaran.required' => 'Format ID Pembayaran wajib diisi',
            'format_id_pembayaran.max' => 'Format ID Pembayaran maksimal 10 karakter',
            'format_id_promo.required' => 'Format ID Promo wajib diisi',
            'format_id_promo.max' => 'Format ID Promo maksimal 10 karakter',
            'format_id_referral.required' => 'Format ID Referral wajib diisi',
            'format_id_referral.max' => 'Format ID Referral maksimal 10 karakter',

            // Pricing
            'biaya_antar_per_km.required' => 'Biaya antar per km wajib diisi',
            'biaya_antar_per_km.numeric' => 'Biaya antar per km harus berupa angka',
            'biaya_antar_per_km.min' => 'Biaya antar per km minimal 0',
            'min_berat_kg.required' => 'Minimum berat wajib diisi',
            'min_berat_kg.numeric' => 'Minimum berat harus berupa angka',
            'min_berat_kg.min' => 'Minimum berat minimal 0',
            'min_berat_kg.max' => 'Minimum berat maksimal 100 kg',
            'pajak_persen.required' => 'Persentase pajak wajib diisi',
            'pajak_persen.numeric' => 'Persentase pajak harus berupa angka',
            'pajak_persen.min' => 'Persentase pajak minimal 0',
            'pajak_persen.max' => 'Persentase pajak maksimal 100',
        ]);
    }

    /**
     * Save all settings to database using transaction
     */
    public function save(): void
    {
        // Validate all settings
        $this->validateSettings();

        try {
            // Use database transaction for data consistency
            DB::beginTransaction();

            // Save General Settings
            PengaturanHelper::setValue(
                'nama_toko',
                $this->nama_toko,
                'string',
                'general',
                'Nama toko laundry'
            );

            // Save Contact Settings
            PengaturanHelper::setValue(
                'whatsapp',
                $this->whatsapp,
                'string',
                'contact',
                'Nomor WhatsApp (format: 8xxx tanpa 0)'
            );
            PengaturanHelper::setValue(
                'email',
                $this->email,
                'string',
                'contact',
                'Email toko'
            );

            // Save Operasional Settings
            PengaturanHelper::setValue(
                'jam_buka',
                $this->jam_buka,
                'string',
                'operasional',
                'Jam buka toko'
            );
            PengaturanHelper::setValue(
                'jam_tutup',
                $this->jam_tutup,
                'string',
                'operasional',
                'Jam tutup toko'
            );

            // Save Format Settings
            PengaturanHelper::setValue(
                'format_id_jenis_pakaian',
                $this->format_id_jenis_pakaian,
                'string',
                'format',
                'Format ID untuk Jenis Pakaian'
            );
            PengaturanHelper::setValue(
                'format_id_layanan',
                $this->format_id_layanan,
                'string',
                'format',
                'Format ID untuk Layanan'
            );
            PengaturanHelper::setValue(
                'format_id_pelanggan',
                $this->format_id_pelanggan,
                'string',
                'format',
                'Format ID untuk Pelanggan'
            );
            PengaturanHelper::setValue(
                'format_id_transaksi',
                $this->format_id_transaksi,
                'string',
                'format',
                'Format ID untuk Transaksi'
            );
            PengaturanHelper::setValue(
                'format_id_kurir',
                $this->format_id_kurir,
                'string',
                'format',
                'Format ID untuk Kurir'
            );
            PengaturanHelper::setValue(
                'format_id_pengiriman',
                $this->format_id_pengiriman,
                'string',
                'format',
                'Format ID untuk Pengiriman'
            );
            PengaturanHelper::setValue(
                'format_id_pembayaran',
                $this->format_id_pembayaran,
                'string',
                'format',
                'Format ID untuk Pembayaran'
            );
            PengaturanHelper::setValue(
                'format_id_promo',
                $this->format_id_promo,
                'string',
                'format',
                'Format ID untuk Promo'
            );
            PengaturanHelper::setValue(
                'format_id_referral',
                $this->format_id_referral,
                'string',
                'format',
                'Format ID untuk Referral'
            );

            // Save Pricing Settings
            PengaturanHelper::setValue(
                'biaya_antar_per_km',
                $this->biaya_antar_per_km,
                'number',
                'pricing',
                'Biaya antar per kilometer'
            );
            PengaturanHelper::setValue(
                'min_berat_kg',
                $this->min_berat_kg,
                'number',
                'pricing',
                'Minimum berat kiloan'
            );
            PengaturanHelper::setValue(
                'pajak_persen',
                $this->pajak_persen,
                'number',
                'pricing',
                'Persentase pajak'
            );

            // Save Features Settings
            PengaturanHelper::setValue(
                'enable_referral',
                $this->enable_referral,
                'boolean',
                'features',
                'Aktifkan sistem referral'
            );
            PengaturanHelper::setValue(
                'enable_promo',
                $this->enable_promo,
                'boolean',
                'features',
                'Aktifkan sistem promo'
            );

            // Commit transaction
            DB::commit();

            // Update original values after successful save
            $this->saveOriginalValues();

            $this->success('Pengaturan berhasil disimpan!', position: 'toast-bottom');
        } catch (Exception $e) {
            // Rollback on error
            DB::rollBack();

            $this->error(
                'Gagal menyimpan pengaturan: '.$e->getMessage(),
                position: 'toast-bottom'
            );
        }
    }

    /**
     * Reset all settings to original values
     */
    public function resetChanges(): void
    {
        if (empty($this->originalValues)) {
            $this->warning('Tidak ada perubahan untuk direset', position: 'toast-bottom');

            return;
        }

        $this->nama_toko = $this->originalValues['nama_toko'] ?? '';
        $this->whatsapp = $this->originalValues['whatsapp'] ?? '';
        $this->email = $this->originalValues['email'] ?? '';
        $this->jam_buka = $this->originalValues['jam_buka'] ?? '';
        $this->jam_tutup = $this->originalValues['jam_tutup'] ?? '';
        $this->format_id_jenis_pakaian = $this->originalValues['format_id_jenis_pakaian'] ?? '';
        $this->format_id_layanan = $this->originalValues['format_id_layanan'] ?? '';
        $this->format_id_pelanggan = $this->originalValues['format_id_pelanggan'] ?? '';
        $this->format_id_transaksi = $this->originalValues['format_id_transaksi'] ?? '';
        $this->format_id_kurir = $this->originalValues['format_id_kurir'] ?? '';
        $this->format_id_pengiriman = $this->originalValues['format_id_pengiriman'] ?? '';
        $this->format_id_pembayaran = $this->originalValues['format_id_pembayaran'] ?? '';
        $this->format_id_promo = $this->originalValues['format_id_promo'] ?? '';
        $this->format_id_referral = $this->originalValues['format_id_referral'] ?? '';
        $this->biaya_antar_per_km = $this->originalValues['biaya_antar_per_km'] ?? 0;
        $this->min_berat_kg = $this->originalValues['min_berat_kg'] ?? 0;
        $this->pajak_persen = $this->originalValues['pajak_persen'] ?? 0;
        $this->enable_referral = $this->originalValues['enable_referral'] ?? false;
        $this->enable_promo = $this->originalValues['enable_promo'] ?? false;

        $this->success('Perubahan dibatalkan', position: 'toast-bottom');
    }

    /**
     * Change active tab
     */
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.management.pengaturan', [
            'hasChanges' => $this->hasChanges(),
        ]);
    }
}
