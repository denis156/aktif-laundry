<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Staf;

use App\Helper\AvatarPlaceholder;
use App\Helper\Database\UserHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Tambah Staf')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $no_hp = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Avatar validation akan dihandle di save() method untuk pakai constant
    public $avatar = null;

    public bool $super_admin = false;

    // Address Information
    public string $detail_alamat = '';

    public string $kelurahan = '';

    public string $kecamatan = '';

    public string $kabupaten_kota = '';

    public string $provinsi = '';

    // Options untuk select
    public array $kelurahanOptions = [];

    public array $kecamatanOptions = [];

    public array $kabupatenKotaOptions = [];

    public array $provinsiOptions = [];

    // Metadata untuk staf
    public string $jam_masuk = '';

    public string $jam_keluar = '';

    public string $gaji = '';

    // Alamat lengkap (read-only, auto-generated)
    public string $alamat = '';

    public function mount()
    {
        // Set default provinsi dan kabupaten/kota
        $this->provinsi = RegionalLocation::getProvinceName();
        $this->kabupaten_kota = RegionalLocation::getRegencyName();

        // Load regional options
        $this->loadRegionalOptions();
    }

    private function loadRegionalOptions()
    {
        // Load provinsi options (seluruh Indonesia)
        $this->provinsiOptions = RegionalLocation::getProvinceOptions();

        // Load kabupaten/kota options berdasarkan provinsi yang dipilih
        if (! empty($this->provinsi)) {
            $this->kabupatenKotaOptions = RegionalLocation::getRegencyOptions($this->provinsi);
        }

        // Load kecamatan options jika kabupaten/kota sudah dipilih
        if (! empty($this->kabupaten_kota)) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->kabupaten_kota);
        }

        // Load kelurahan options jika kecamatan sudah dipilih
        if (! empty($this->kecamatan)) {
            $this->kelurahanOptions = RegionalLocation::getVillageOptions($this->kecamatan);
        }
    }

    public function updatedProvinsi()
    {
        // Reset dependent fields
        $this->kabupaten_kota = '';
        $this->kecamatan = '';
        $this->kelurahan = '';
        $this->kabupatenKotaOptions = [];
        $this->kecamatanOptions = [];
        $this->kelurahanOptions = [];

        // Load kabupaten/kota options
        if (! empty($this->provinsi)) {
            $this->kabupatenKotaOptions = RegionalLocation::getRegencyOptions($this->provinsi);
        }
    }

    public function updatedKabupatenKota()
    {
        // Reset dependent fields
        $this->kecamatan = '';
        $this->kelurahan = '';
        $this->kecamatanOptions = [];
        $this->kelurahanOptions = [];

        // Load kecamatan options
        if (! empty($this->kabupaten_kota)) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->kabupaten_kota);
        }
    }

    public function updatedKecamatan()
    {
        // Reset dependent field
        $this->kelurahan = '';
        $this->kelurahanOptions = [];

        // Load kelurahan options
        if (! empty($this->kecamatan)) {
            $this->kelurahanOptions = RegionalLocation::getVillageOptions($this->kecamatan);
        }

        // Update alamat preview
        $this->updateAlamatPreview();
    }

    public function updatedDetailAlamat()
    {
        $this->updateAlamatPreview();
    }

    public function updatedKelurahan()
    {
        $this->updateAlamatPreview();
    }

    private function updateAlamatPreview()
    {
        // Generate alamat preview dari komponen yang sudah diisi
        if (! empty($this->detail_alamat) || ! empty($this->kelurahan) || ! empty($this->kecamatan)) {
            $this->alamat = RegionalLocation::formatFullAddress(
                $this->detail_alamat,
                $this->kelurahan,
                $this->kecamatan,
                $this->kabupaten_kota,
                $this->provinsi
            );
        } else {
            $this->alamat = '';
        }
    }

    public function roleOptions(): array
    {
        return [
            ['id' => 0, 'name' => 'Staf'],
            ['id' => 1, 'name' => 'Super Admin'],
        ];
    }

    public function save(): void
    {
        $this->validate($this->validationRules(), $this->validationMessages());

        try {
            DB::transaction(function () {
                $normalizedPhone = $this->validateAndNormalizePhone();
                $avatarPath = $this->uploadAvatar();

                UserHelper::createUser([
                    'name' => $this->name,
                    'email' => $this->email,
                    'no_hp' => $normalizedPhone,
                    'password' => $this->password,
                    'avatar_url' => $avatarPath,
                    'super_admin' => $this->super_admin,
                    'detail_alamat' => $this->detail_alamat,
                    'kelurahan' => $this->kelurahan,
                    'kecamatan' => $this->kecamatan,
                    'kabupaten_kota' => $this->kabupaten_kota,
                    'provinsi' => $this->provinsi,
                    'jam_masuk' => $this->jam_masuk ?: null,
                    'jam_keluar' => $this->jam_keluar ?: null,
                    'gaji' => $this->gaji ? (int) $this->gaji : null,
                ]);
            });

            $this->success('Staf berhasil ditambahkan!', redirectTo: route('staf.index'), position: 'toast-bottom');
        } catch (Exception $e) {
            $this->handleSaveError($e);
        }
    }

    private function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'no_hp' => 'required|string|max:20',
            'password' => 'required|min:'.UserHelper::PASSWORD_MIN_LENGTH.'|confirmed',
            'avatar' => 'nullable|image|max:'.UserHelper::AVATAR_MAX_SIZE_KB,
            'super_admin' => 'boolean',
            'detail_alamat' => 'nullable|string|max:500',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten_kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'gaji' => 'nullable|integer|min:0',
        ];
    }

    private function validationMessages(): array
    {
        $avatarMaxSizeMB = UserHelper::AVATAR_MAX_SIZE_KB / 1024;

        return [
            'name.required' => 'Nama wajib diisi',
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'no_hp.required' => 'Nomor HP wajib diisi',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal '.UserHelper::PASSWORD_MIN_LENGTH.' karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'avatar.image' => 'File harus berupa gambar',
            'avatar.max' => "Ukuran file maksimal {$avatarMaxSizeMB} MB",
            'super_admin.boolean' => 'Role harus berupa pilihan yang valid',
            'detail_alamat.max' => 'Detail alamat maksimal 500 karakter',
            'kelurahan.max' => 'Kelurahan maksimal 100 karakter',
            'kecamatan.max' => 'Kecamatan maksimal 100 karakter',
            'kabupaten_kota.max' => 'Kabupaten/Kota maksimal 100 karakter',
            'provinsi.max' => 'Provinsi maksimal 100 karakter',
            'jam_masuk.date_format' => 'Format jam masuk tidak valid (HH:MM)',
            'jam_keluar.date_format' => 'Format jam keluar tidak valid (HH:MM)',
            'gaji.integer' => 'Gaji harus berupa angka',
            'gaji.min' => 'Gaji tidak boleh negatif',
        ];
    }

    private function validateAndNormalizePhone(): string
    {
        $normalizedPhone = PhoneNumber::normalize($this->no_hp);

        if (! $normalizedPhone) {
            Log::warning('Staf Create: Invalid phone number format', [
                'no_hp' => $this->no_hp,
            ]);
            throw new Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
        }

        return $normalizedPhone;
    }

    private function uploadAvatar(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        try {
            return $this->avatar->store('avatars', 'public');
        } catch (Exception $e) {
            Log::error('Staf Create: Failed to upload avatar', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Gagal upload avatar');
        }
    }

    private function handleSaveError(Exception $e): void
    {
        $errorMessage = $e->getMessage();

        if (str_contains($errorMessage, 'Format nomor HP tidak valid') || str_contains($errorMessage, 'Gagal upload avatar')) {
            $this->error($errorMessage, position: 'toast-bottom');

            return;
        }

        Log::error('Staf Create: Unexpected error during save', [
            'error' => $errorMessage,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $this->error('Gagal menambahkan staf. Silakan coba lagi atau hubungi administrator.', position: 'toast-bottom');
    }

    public function render(): mixed
    {
        $avatarUrl = $this->avatar
            ? $this->avatar->temporaryUrl()
            : AvatarPlaceholder::generate($this->name, 256);

        return view('livewire.management.pages.staf.create', [
            'roleOptions' => $this->roleOptions(),
            'avatarMaxSizeMB' => UserHelper::AVATAR_MAX_SIZE_KB / 1024,
            'passwordMinLength' => UserHelper::PASSWORD_MIN_LENGTH,
            'avatarUrl' => $avatarUrl,
        ]);
    }
}
