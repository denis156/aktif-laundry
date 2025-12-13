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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Edit Kurir')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;
    use WithFileUploads;

    public int $kurirId;

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
        'alamat' => '',
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

    public ?string $currentAvatarUrl = null;

    // Options untuk select regional
    public array $provinsiOptions = [];

    public array $kabupatenKotaOptions = [];

    public array $kecamatanOptions = [];

    public array $kelurahanOptions = [];

    public function mount($id): void
    {
        $this->kurirId = (int) $id;
        $this->loadKurir();
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

    protected function loadKurir(): void
    {
        try {
            $kurir = Kurir::findOrFail($this->kurirId);

            $this->formData = [
                'kode_kurir' => $kurir->kode_kurir,
                'nama' => $kurir->nama,
                'no_hp' => PhoneNumber::formatLocal($kurir->no_hp) ?? $kurir->no_hp,
                'email' => $kurir->email ?? '',
                'detail_alamat' => $kurir->detail_alamat ?? '',
                'kelurahan' => $kurir->kelurahan ?? '',
                'kecamatan' => $kurir->kecamatan ?? '',
                'kabupaten_kota' => $kurir->kabupaten_kota ?? RegionalLocation::getRegencyName(),
                'provinsi' => $kurir->provinsi ?? RegionalLocation::getProvinceName(),
                'alamat' => $kurir->alamat ?? '',
                'no_kendaraan' => $kurir->no_kendaraan ?? '',
                'jenis_kendaraan' => $kurir->jenis_kendaraan ?? '',
                'password' => '',
                'tanggal_bergabung' => $kurir->tanggal_bergabung?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'),
                'status' => $kurir->status,
                'bank_name' => $kurir->bank_name ?? '',
                'bank_account_number' => $kurir->bank_account_number ?? '',
                'bank_account_name' => $kurir->bank_account_name ?? '',
                'emergency_contact_name' => $kurir->emergency_contact_name ?? '',
                'emergency_contact_phone' => $kurir->emergency_contact_phone ? PhoneNumber::formatLocal($kurir->emergency_contact_phone) : '',
                'emergency_contact_relation' => $kurir->emergency_contact_relation ?? '',
            ];

            $this->currentAvatarUrl = $kurir->avatar_url;
        } catch (Exception $e) {
            Log::error('Failed to load kurir for edit', [
                'kurir_id' => $this->kurirId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Kurir tidak ditemukan', position: 'toast-bottom');
            $this->redirect('/management/kurir', navigate: true);
        }
    }

    public function save(): void
    {
        $rules = [
            'formData.nama' => 'required|string|max:255',
            'formData.no_hp' => 'required|string|max:15|unique:kurir,no_hp,'.$this->kurirId,
            'formData.detail_alamat' => 'nullable|string',
            'formData.kelurahan' => 'nullable|string|max:100',
            'formData.kecamatan' => 'nullable|string|max:100',
            'formData.kabupaten_kota' => 'nullable|string|max:100',
            'formData.provinsi' => 'nullable|string|max:100',
            'formData.email' => 'nullable|email|max:255|unique:kurir,email,'.$this->kurirId,
            'formData.jenis_kendaraan' => 'required|in:Motor,Mobil',
            'formData.no_kendaraan' => 'required|string|max:20',
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

        if (! empty($this->formData['password'])) {
            $rules['formData.password'] = 'min:'.KurirHelper::PASSWORD_MIN_LENGTH.'|confirmed';
        }

        $this->validate($rules);

        try {
            $kurir = Kurir::findOrFail($this->kurirId);

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

            // Prepare data untuk update kurir
            $data = [
                'nama' => $this->formData['nama'],
                'no_hp' => $normalizedPhone,
                'email' => $this->formData['email'] ?: null,
                'no_kendaraan' => $this->formData['no_kendaraan'] ?: null,
                'jenis_kendaraan' => $this->formData['jenis_kendaraan'] ?: null,
                'tanggal_bergabung' => $this->formData['tanggal_bergabung'],
                'status' => $this->formData['status'],
            ];

            // Update password jika diisi
            if (! empty($this->formData['password'])) {
                $data['password'] = Hash::make($this->formData['password']);
            }

            $kurir->update($data);

            // Set alamat regional menggunakan KurirHelper
            KurirHelper::setAlamatRegional(
                $kurir,
                $this->formData['detail_alamat'],
                $this->formData['kelurahan'],
                $this->formData['kecamatan'],
                $this->formData['kabupaten_kota'],
                $this->formData['provinsi']
            );

            // Upload foto profil baru jika ada
            if ($this->avatar) {
                $avatarPath = $this->avatar->store('avatars/kurir', 'public');
                $kurir->avatar_url = $avatarPath;
            }

            // Set bank info
            if ($this->formData['bank_name'] || $this->formData['bank_account_number']) {
                KurirHelper::setBankInfo(
                    $kurir,
                    $this->formData['bank_name'] ?? '',
                    $this->formData['bank_account_number'] ?? '',
                    $this->formData['bank_account_name'] ?? ''
                );
            } else {
                $kurir->bank_name = null;
                $kurir->bank_account_number = null;
                $kurir->bank_account_name = null;
            }

            // Set emergency contact
            if ($this->formData['emergency_contact_name'] && $normalizedEmergencyPhone) {
                KurirHelper::setEmergencyContact(
                    $kurir,
                    $this->formData['emergency_contact_name'],
                    $normalizedEmergencyPhone,
                    $this->formData['emergency_contact_relation'] ?? null
                );
            } else {
                $kurir->emergency_contact_name = null;
                $kurir->emergency_contact_phone = null;
                $kurir->emergency_contact_relation = null;
            }

            $kurir->save();

            $this->success('Kurir berhasil diupdate!', position: 'toast-bottom');
            $this->redirect('/management/kurir', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            $errorCode = $e->errorInfo[0] ?? $e->getCode();

            // PostgreSQL unique violation code
            if ($errorCode == 23505 || $e->errorInfo[1] == 1062) {
                $errorMessage = $e->getMessage();

                // Detect which field is duplicate
                if (str_contains($errorMessage, 'kurir_no_hp_unique') || str_contains($errorMessage, 'no_hp')) {
                    Log::warning('Duplicate phone number detected when updating kurir', [
                        'kurir_id' => $this->kurirId,
                        'no_hp' => $this->formData['no_hp'],
                        'error' => $e->getMessage(),
                    ]);
                    $this->error('Nomor HP sudah terdaftar. Gunakan nomor HP lain.', position: 'toast-bottom');

                    return;
                } elseif (str_contains($errorMessage, 'kurir_email_unique') || str_contains($errorMessage, 'email')) {
                    Log::warning('Duplicate email detected when updating kurir', [
                        'kurir_id' => $this->kurirId,
                        'email' => $this->formData['email'],
                        'error' => $e->getMessage(),
                    ]);
                    $this->error('Email sudah terdaftar. Gunakan email lain.', position: 'toast-bottom');

                    return;
                }

                // Generic duplicate message
                $this->error('Data sudah terdaftar. Periksa nomor HP atau email.', position: 'toast-bottom');

                return;
            }

            Log::error('Database error when updating kurir', [
                'kurir_id' => $this->kurirId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->formData,
            ]);
            $this->error('Gagal menyimpan kurir. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Failed to update kurir', [
                'kurir_id' => $this->kurirId,
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
            : AvatarPlaceholder::getAvatarOrPlaceholder($this->currentAvatarUrl, $this->formData['nama'] ?? 'Kurir', 256);

        return view('livewire.management.kurir.edit', [
            'statusOptions' => KurirHelper::getStatusOptions(),
            'jenisKendaraanOptions' => KurirHelper::getJenisKendaraanOptions(),
            'avatarUrl' => $avatarUrl,
            'passwordMinLength' => KurirHelper::PASSWORD_MIN_LENGTH,
        ]);
    }
}
