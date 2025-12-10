<?php

declare(strict_types=1);

namespace App\Livewire\Management\Kurir;

use App\Helper\AvatarPlaceholder;
use App\Helper\Database\KurirHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\Kurir;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Tambah Kurir')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;
    use WithFileUploads;

    public array $formData = [
        'kode_kurir' => '',
        'nama' => '',
        'no_hp' => '',
        'email' => '',
        'detail_alamat' => '',
        'kelurahan' => '',
        'kecamatan' => '',
        'kabupaten_kota' => '',
        'provinsi' => '',
        'no_kendaraan' => '',
        'jenis_kendaraan' => '',
        'password' => '',
        'password_confirmation' => '',
        'tanggal_bergabung' => '',
        'status' => 'Aktif',
        'bank_name' => '',
        'bank_account_number' => '',
        'bank_account_name' => '',
        'emergency_contact_name' => '',
        'emergency_contact_phone' => '',
        'emergency_contact_relation' => '',
    ];

    public $avatar;

    // Options untuk select regional
    public array $provinsiOptions = [];

    public array $kabupatenKotaOptions = [];

    public array $kecamatanOptions = [];

    public array $kelurahanOptions = [];

    public function mount(): void
    {
        $this->refreshKodeKurir();
        $this->formData['tanggal_bergabung'] = now()->format('Y-m-d\TH:i');

        // Set default kabupaten/kota dan provinsi menggunakan RegionalLocation Helper
        $this->formData['kabupaten_kota'] = RegionalLocation::getRegencyName();
        $this->formData['provinsi'] = RegionalLocation::getProvinceName();

        // Load regional options
        $this->loadRegionalOptions();
    }

    private function loadRegionalOptions(): void
    {
        // Load provinsi options (seluruh Indonesia)
        $this->provinsiOptions = RegionalLocation::getProvinceOptions();

        // Load kabupaten/kota options berdasarkan provinsi yang dipilih
        if (! empty($this->formData['provinsi'])) {
            $this->kabupatenKotaOptions = RegionalLocation::getRegencyOptions($this->formData['provinsi']);
        }

        // Load kecamatan options jika kabupaten/kota sudah dipilih
        if (! empty($this->formData['kabupaten_kota'])) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->formData['kabupaten_kota']);
        }

        // Load kelurahan options jika kecamatan sudah dipilih
        if (! empty($this->formData['kecamatan'])) {
            $this->kelurahanOptions = RegionalLocation::getVillageOptions($this->formData['kecamatan']);
        }
    }

    public function updatedFormDataProvinsi(): void
    {
        // Reset dependent fields
        $this->formData['kabupaten_kota'] = '';
        $this->formData['kecamatan'] = '';
        $this->formData['kelurahan'] = '';
        $this->kabupatenKotaOptions = [];
        $this->kecamatanOptions = [];
        $this->kelurahanOptions = [];

        // Load kabupaten/kota options
        if (! empty($this->formData['provinsi'])) {
            $this->kabupatenKotaOptions = RegionalLocation::getRegencyOptions($this->formData['provinsi']);
        }
    }

    public function updatedFormDataKabupatenKota(): void
    {
        // Reset dependent fields
        $this->formData['kecamatan'] = '';
        $this->formData['kelurahan'] = '';
        $this->kecamatanOptions = [];
        $this->kelurahanOptions = [];

        // Load kecamatan options
        if (! empty($this->formData['kabupaten_kota'])) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->formData['kabupaten_kota']);
        }
    }

    public function updatedFormDataKecamatan(): void
    {
        // Reset dependent field
        $this->formData['kelurahan'] = '';
        $this->kelurahanOptions = [];

        // Load kelurahan options
        if (! empty($this->formData['kecamatan'])) {
            $this->kelurahanOptions = RegionalLocation::getVillageOptions($this->formData['kecamatan']);
        }
    }

    public function refreshKodeKurir(): void
    {
        try {
            $this->formData['kode_kurir'] = KurirHelper::generateKodeKurir();
        } catch (Exception $e) {
            Log::error('Failed to generate kode kurir', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal generate kode kurir. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function save(): void
    {
        $rules = [
            'formData.kode_kurir' => 'required|unique:kurir,kode_kurir',
            'formData.nama' => 'required|string|max:255',
            'formData.no_hp' => 'required|string|max:15|unique:kurir,no_hp',
            'formData.detail_alamat' => 'nullable|string',
            'formData.kelurahan' => 'nullable|string|max:100',
            'formData.kecamatan' => 'nullable|string|max:100',
            'formData.kabupaten_kota' => 'nullable|string|max:100',
            'formData.provinsi' => 'nullable|string|max:100',
            'formData.email' => 'nullable|email|max:255|unique:kurir,email',
            'formData.jenis_kendaraan' => 'required|in:Motor,Mobil',
            'formData.no_kendaraan' => 'required|string|max:20',
            'formData.password' => 'required|min:'.KurirHelper::PASSWORD_MIN_LENGTH.'|confirmed',
            'formData.tanggal_bergabung' => 'required|date',
            'formData.status' => 'required|in:Aktif,Tidak Aktif,Cuti',
            'avatar' => 'nullable|image|max:'.KurirHelper::AVATAR_MAX_SIZE_KB,
            'formData.bank_name' => 'nullable|string|max:100',
            'formData.bank_account_number' => 'nullable|string|max:50',
            'formData.bank_account_name' => 'nullable|string|max:255',
            'formData.emergency_contact_name' => 'nullable|string|max:255',
            'formData.emergency_contact_phone' => 'nullable|string|max:15',
            'formData.emergency_contact_relation' => 'nullable|string|max:50',
        ];

        $this->validate($rules);

        try {
            // Normalize phone number menggunakan PhoneNumber helper
            $normalizedPhone = PhoneNumber::normalize($this->formData['no_hp']);
            if (! $normalizedPhone) {
                $this->error('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8', position: 'toast-bottom');

                return;
            }

            // Normalize emergency contact phone if provided
            $normalizedEmergencyPhone = null;
            if (! empty($this->formData['emergency_contact_phone'])) {
                $normalizedEmergencyPhone = PhoneNumber::normalize($this->formData['emergency_contact_phone']);
                if (! $normalizedEmergencyPhone) {
                    $this->error('Format nomor HP kontak darurat tidak valid.', position: 'toast-bottom');

                    return;
                }
            }

            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function () use ($normalizedPhone, $normalizedEmergencyPhone) {
                // Cek ulang apakah kode kurir sudah ada, jika ya generate ulang
                if (Kurir::where('kode_kurir', $this->formData['kode_kurir'])->exists()) {
                    $this->refreshKodeKurir();
                }

                // Prepare data untuk create kurir
                $data = [
                    'kode_kurir' => $this->formData['kode_kurir'],
                    'nama' => $this->formData['nama'],
                    'no_hp' => $normalizedPhone,
                    'email' => $this->formData['email'] ?: null,
                    'detail_alamat' => $this->formData['detail_alamat'],
                    'kelurahan' => $this->formData['kelurahan'],
                    'kecamatan' => $this->formData['kecamatan'],
                    'kabupaten_kota' => $this->formData['kabupaten_kota'],
                    'provinsi' => $this->formData['provinsi'],
                    'no_kendaraan' => $this->formData['no_kendaraan'] ?: null,
                    'jenis_kendaraan' => $this->formData['jenis_kendaraan'] ?: null,
                    'password' => $this->formData['password'],
                    'tanggal_bergabung' => $this->formData['tanggal_bergabung'],
                ];

                // Tambahkan avatar jika ada
                if ($this->avatar) {
                    $avatarPath = $this->avatar->store('avatars/kurir', 'public');
                    $data['avatar_url'] = $avatarPath;
                }

                // Create kurir menggunakan KurirHelper (sudah handle alamat regional otomatis)
                $kurir = KurirHelper::createKurir($data);

                // Set bank info jika ada
                if ($this->formData['bank_name'] || $this->formData['bank_account_number']) {
                    KurirHelper::setBankInfo(
                        $kurir,
                        $this->formData['bank_name'] ?? '',
                        $this->formData['bank_account_number'] ?? '',
                        $this->formData['bank_account_name'] ?? ''
                    );
                }

                // Set emergency contact jika ada
                if ($this->formData['emergency_contact_name'] && $normalizedEmergencyPhone) {
                    KurirHelper::setEmergencyContact(
                        $kurir,
                        $this->formData['emergency_contact_name'],
                        $normalizedEmergencyPhone,
                        $this->formData['emergency_contact_relation'] ?? null
                    );
                }

                $kurir->save();
            });

            $this->success('Kurir berhasil ditambahkan!', position: 'toast-bottom');
            $this->redirect('/management/kurir', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            $errorCode = $e->errorInfo[0] ?? $e->getCode();

            // PostgreSQL unique violation code
            if ($errorCode == 23505 || $e->errorInfo[1] == 1062) {
                $errorMessage = $e->getMessage();

                // Detect which field is duplicate
                if (str_contains($errorMessage, 'kurir_no_hp_unique') || str_contains($errorMessage, 'no_hp')) {
                    Log::warning('Duplicate phone number detected when creating kurir', [
                        'no_hp' => $this->formData['no_hp'],
                        'error' => $e->getMessage(),
                    ]);
                    $this->error('Nomor HP sudah terdaftar. Gunakan nomor HP lain.', position: 'toast-bottom');

                    return;
                } elseif (str_contains($errorMessage, 'kurir_email_unique') || str_contains($errorMessage, 'email')) {
                    Log::warning('Duplicate email detected when creating kurir', [
                        'email' => $this->formData['email'],
                        'error' => $e->getMessage(),
                    ]);
                    $this->error('Email sudah terdaftar. Gunakan email lain.', position: 'toast-bottom');

                    return;
                } elseif (str_contains($errorMessage, 'kurir_kode_kurir_unique') || str_contains($errorMessage, 'kode_kurir')) {
                    Log::warning('Duplicate kode kurir detected when creating kurir', [
                        'kode_kurir' => $this->formData['kode_kurir'],
                        'error' => $e->getMessage(),
                    ]);
                    $this->refreshKodeKurir();
                    $this->warning('Kode kurir sudah ada, silakan coba lagi dengan kode baru.', position: 'toast-bottom');

                    return;
                }

                // Generic duplicate message
                $this->error('Data sudah terdaftar. Periksa nomor HP, email, atau kode kurir.', position: 'toast-bottom');

                return;
            }

            Log::error('Database error when creating kurir', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->formData,
            ]);
            $this->error('Gagal menyimpan kurir. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Failed to create kurir', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->formData,
            ]);
            $this->error('Gagal menyimpan kurir. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        $avatarUrl = $this->avatar
            ? $this->avatar->temporaryUrl()
            : AvatarPlaceholder::generate($this->formData['nama'] ?? 'Kurir', 256);

        return view('livewire.management.kurir.create', [
            'statusOptions' => KurirHelper::getStatusOptions(),
            'jenisKendaraanOptions' => KurirHelper::getJenisKendaraanOptions(),
            'avatarUrl' => $avatarUrl,
        ]);
    }
}
