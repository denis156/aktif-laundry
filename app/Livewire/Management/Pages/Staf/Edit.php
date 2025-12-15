<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Staf;

use App\Helper\AvatarPlaceholder;
use App\Helper\Database\UserHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Edit Staf')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;
    use WithFileUploads;

    public int $userId;

    public string $name = '';

    public string $email = '';

    public string $no_hp = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $currentAvatarUrl = '';

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

    // Original data untuk compare
    public string $originalNoHp = '';

    public string $originalDetailAlamat = '';

    public string $originalKelurahan = '';

    public string $originalKecamatan = '';

    public string $originalKabupatenKota = '';

    public string $originalProvinsi = '';

    public string $originalGaji = '';

    public function mount($id): void
    {
        $this->userId = (int) $id;
        $user = User::findOrFail($this->userId);

        $this->loadUserData($user);
        $this->loadRegionalOptions();
        $this->saveOriginalData();
    }

    private function loadUserData(User $user): void
    {
        $this->name = $user->name;
        $this->email = $user->email;
        $this->no_hp = PhoneNumber::formatLocal($user->no_hp) ?? '';
        $this->currentAvatarUrl = $user->avatar_url ?? '';
        $this->super_admin = $user->super_admin;

        $this->detail_alamat = $user->detail_alamat ?? '';
        $this->kelurahan = $user->kelurahan ?? '';
        $this->kecamatan = $user->kecamatan ?? '';
        $this->kabupaten_kota = $user->kabupaten_kota ?? RegionalLocation::getRegencyName();
        $this->provinsi = $user->provinsi ?? RegionalLocation::getProvinceName();

        $this->jam_masuk = UserHelper::getJamMasuk($user) ?? '';
        $this->jam_keluar = UserHelper::getJamKeluar($user) ?? '';
        $this->gaji = UserHelper::getGaji($user) !== null ? (string) UserHelper::getGaji($user) : '';

        $this->alamat = $user->alamat ?? '';
    }

    private function saveOriginalData(): void
    {
        $this->originalNoHp = $this->no_hp;
        $this->originalDetailAlamat = $this->detail_alamat;
        $this->originalKelurahan = $this->kelurahan;
        $this->originalKecamatan = $this->kecamatan;
        $this->originalKabupatenKota = $this->kabupaten_kota;
        $this->originalProvinsi = $this->provinsi;
        $this->originalGaji = $this->gaji;
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
        $isChangingPassword = ! empty($this->password);
        $this->validate($this->validationRules($isChangingPassword), $this->validationMessages($isChangingPassword));

        try {
            DB::transaction(function () use ($isChangingPassword) {
                $user = User::findOrFail($this->userId);

                if ($this->isSelfEmailChange($user)) {
                    throw new Exception('SELF_EMAIL_CHANGE');
                }

                $normalizedPhone = $this->validateAndNormalizePhone();
                $avatarPath = $this->uploadAvatar();

                $this->updateUserProfile($user, $avatarPath);
                $this->updateUserAddress($user, $normalizedPhone);
                $this->updateUserEmploymentData($user);
                $this->updateUserPassword($user, $isChangingPassword);

                $user->update(['super_admin' => $this->super_admin]);
                $this->alamat = $user->fresh()->alamat ?? '';
            });

            $this->success('Staf berhasil diperbarui!', redirectTo: route('staf.index'), position: 'toast-bottom');
        } catch (Exception $e) {
            $this->handleSaveError($e);
        }
    }

    private function validationRules(bool $isChangingPassword): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->userId,
            'no_hp' => 'required|string|max:20',
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

        if ($isChangingPassword) {
            $rules['password'] = 'min:'.UserHelper::PASSWORD_MIN_LENGTH.'|confirmed';
        }

        return $rules;
    }

    private function validationMessages(bool $isChangingPassword): array
    {
        $avatarMaxSizeMB = UserHelper::AVATAR_MAX_SIZE_KB / 1024;

        $messages = [
            'name.required' => 'Nama wajib diisi',
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'no_hp.required' => 'Nomor HP wajib diisi',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter',
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

        if ($isChangingPassword) {
            $messages['password.min'] = 'Password minimal '.UserHelper::PASSWORD_MIN_LENGTH.' karakter';
            $messages['password.confirmed'] = 'Konfirmasi password tidak cocok';
        }

        return $messages;
    }

    private function isSelfEmailChange(User $user): bool
    {
        return $user->id === Auth::id() && $user->email !== $this->email;
    }

    private function validateAndNormalizePhone(): string
    {
        $normalizedPhone = PhoneNumber::normalize($this->no_hp);

        if (! $normalizedPhone) {
            Log::warning('Staf Edit: Invalid phone number format', [
                'user_id' => $this->userId,
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
            Log::error('Staf Edit: Failed to upload avatar', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Gagal upload avatar');
        }
    }

    private function updateUserProfile(User $user, ?string $avatarPath): void
    {
        UserHelper::updateProfile($user, $this->name, $this->email, $avatarPath);
    }

    private function updateUserAddress(User $user, string $normalizedPhone): void
    {
        UserHelper::setAlamatRegional(
            $user,
            $this->detail_alamat,
            $this->kelurahan,
            $this->kecamatan,
            $this->kabupaten_kota,
            $this->provinsi
        );

        $user->no_hp = $normalizedPhone;
    }

    private function updateUserEmploymentData(User $user): void
    {
        UserHelper::setJamMasuk($user, $this->jam_masuk ?: null);
        UserHelper::setJamKeluar($user, $this->jam_keluar ?: null);
        UserHelper::setGaji($user, $this->gaji ? (int) $this->gaji : null);

        $user->save();
    }

    private function updateUserPassword(User $user, bool $isChangingPassword): void
    {
        if (! $isChangingPassword) {
            return;
        }

        $user->update(['password' => Hash::make($this->password)]);
    }

    private function handleSaveError(Exception $e): void
    {
        $errorMessage = $e->getMessage();

        if ($errorMessage === 'SELF_EMAIL_CHANGE') {
            $this->error('Anda tidak dapat mengubah email akun Anda sendiri!', position: 'toast-bottom');

            return;
        }

        if (str_contains($errorMessage, 'Format nomor HP tidak valid') || str_contains($errorMessage, 'Gagal upload avatar')) {
            $this->error($errorMessage, position: 'toast-bottom');

            return;
        }

        Log::error('Staf Edit: Unexpected error during save', [
            'user_id' => $this->userId,
            'error' => $errorMessage,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $this->error('Gagal memperbarui staf. Silakan coba lagi atau hubungi administrator.', position: 'toast-bottom');
    }

    public function render(): mixed
    {
        $avatarUrl = $this->avatar
            ? $this->avatar->temporaryUrl()
            : AvatarPlaceholder::getAvatarOrPlaceholder($this->currentAvatarUrl, $this->name, 256);

        return view('livewire.management.pages.staf.edit', [
            'roleOptions' => $this->roleOptions(),
            'avatarMaxSizeMB' => UserHelper::AVATAR_MAX_SIZE_KB / 1024,
            'passwordMinLength' => UserHelper::PASSWORD_MIN_LENGTH,
            'avatarUrl' => $avatarUrl,
        ]);
    }
}
