<?php

namespace App\Livewire\Management;

use App\Helper\AddressMetadata;
use App\Helper\Database\UserHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Profil Saya')]
#[Layout('layouts.management.app')]
class Profile extends Component
{
    use Toast, WithFileUploads;

    // Profile Information
    public string $name = '';

    public string $email = '';

    public string $no_hp = '';

    public string $currentAvatarUrl = '';

    // Avatar validation akan dihandle di save() method untuk pakai constant
    public $avatar = null;

    // Address Information
    public string $detail_alamat = '';

    public string $kelurahan = '';

    public string $kecamatan = '';

    public string $kabupaten_kota = '';

    public string $provinsi = '';

    public string $alamat = ''; // Generated full address (read-only)

    // Options untuk select
    public array $kelurahanOptions = [];

    public array $kecamatanOptions = [];

    public array $kabupatenKotaOptions = [];

    public array $provinsiOptions = [];

    // Change Password
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Original data untuk compare
    public string $originalName = '';

    public string $originalEmail = '';

    public string $originalNoHp = '';

    public string $originalDetailAlamat = '';

    public string $originalKelurahan = '';

    public string $originalKecamatan = '';

    public string $originalKabupatenKota = '';

    public string $originalProvinsi = '';

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->no_hp = PhoneNumber::formatLocal($user->no_hp) ?? '';
        $this->currentAvatarUrl = $user->avatar_url ?? '';

        // Load address metadata
        $this->detail_alamat = AddressMetadata::getDetailAlamat($user) ?? '';
        $this->kelurahan = AddressMetadata::getKelurahan($user) ?? '';
        $this->kecamatan = AddressMetadata::getKecamatan($user) ?? '';
        $this->kabupaten_kota = AddressMetadata::getKabupatenKota($user) ?? RegionalLocation::getRegencyName();
        $this->provinsi = AddressMetadata::getProvinsi($user) ?? RegionalLocation::getProvinceName();

        // Load alamat lengkap dari kolom alamat (text)
        $this->alamat = $user->alamat ?? '';

        // Load options untuk select
        $this->loadRegionalOptions();

        // Simpan data original
        $this->originalName = $user->name;
        $this->originalEmail = $user->email;
        $this->originalNoHp = $this->no_hp;
        $this->originalDetailAlamat = $this->detail_alamat;
        $this->originalKelurahan = $this->kelurahan;
        $this->originalKecamatan = $this->kecamatan;
        $this->originalKabupatenKota = $this->kabupaten_kota;
        $this->originalProvinsi = $this->provinsi;
    }

    private function loadRegionalOptions()
    {
        // Load provinsi options (fixed ke Sulawesi Tenggara)
        $this->provinsiOptions = RegionalLocation::getProvinceOptions();

        // Load kabupaten/kota options (semua kabupaten di Sulawesi Tenggara)
        $this->kabupatenKotaOptions = RegionalLocation::getRegencyOptions();

        // Load kecamatan options jika kabupaten/kota sudah dipilih
        if (! empty($this->kabupaten_kota)) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->kabupaten_kota);
        }

        // Load kelurahan options jika kecamatan sudah dipilih
        if (! empty($this->kecamatan)) {
            $this->kelurahanOptions = RegionalLocation::getVillageOptions($this->kecamatan);
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
    }

    public function hasChanges()
    {
        // Cek perubahan profil
        $profileChanged = $this->name !== $this->originalName ||
                         $this->email !== $this->originalEmail ||
                         $this->no_hp !== $this->originalNoHp ||
                         $this->avatar !== null;

        // Cek perubahan alamat
        $addressChanged = $this->detail_alamat !== $this->originalDetailAlamat ||
                         $this->kelurahan !== $this->originalKelurahan ||
                         $this->kecamatan !== $this->originalKecamatan ||
                         $this->kabupaten_kota !== $this->originalKabupatenKota ||
                         $this->provinsi !== $this->originalProvinsi;

        // Cek perubahan password
        $passwordChanged = ! empty($this->current_password) ||
                          ! empty($this->password) ||
                          ! empty($this->password_confirmation);

        return $profileChanged || $addressChanged || $passwordChanged;
    }

    public function save()
    {
        // Validasi profil
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.Auth::id(),
            'no_hp' => 'required|string|max:20',
            'avatar' => 'nullable|image|max:'.UserHelper::AVATAR_MAX_SIZE_KB,
            'detail_alamat' => 'required|string|max:500',
            'kelurahan' => 'required|string',
            'kecamatan' => 'required|string',
            'kabupaten_kota' => 'required|string',
            'provinsi' => 'required|string',
        ];

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
            'detail_alamat.required' => 'Detail alamat wajib diisi',
            'detail_alamat.max' => 'Detail alamat maksimal 500 karakter',
            'kelurahan.required' => 'Kelurahan wajib dipilih',
            'kecamatan.required' => 'Kecamatan wajib dipilih',
            'kabupaten_kota.required' => 'Kabupaten/Kota wajib dipilih',
            'provinsi.required' => 'Provinsi wajib dipilih',
        ];

        // Validasi password jika diisi
        $isChangingPassword = ! empty($this->current_password) || ! empty($this->password) || ! empty($this->password_confirmation);

        if ($isChangingPassword) {
            $rules['current_password'] = 'required';
            $rules['password'] = 'required|min:'.UserHelper::PASSWORD_MIN_LENGTH.'|confirmed';
            $messages['current_password.required'] = 'Password saat ini wajib diisi';
            $messages['password.required'] = 'Password baru wajib diisi';
            $messages['password.min'] = 'Password baru minimal '.UserHelper::PASSWORD_MIN_LENGTH.' karakter';
            $messages['password.confirmed'] = 'Konfirmasi password tidak cocok';
        }

        $this->validate($rules, $messages);

        try {
            DB::transaction(function () use ($isChangingPassword) {
                $user = User::findOrFail(Auth::id());
                $oldAvatarPath = $user->avatar_url;

                // Validasi dan normalize nomor HP
                $normalizedPhone = PhoneNumber::normalize($this->no_hp);
                if (! $normalizedPhone) {
                    Log::warning('Profile: Invalid phone number format', [
                        'user_id' => $user->id,
                        'no_hp' => $this->no_hp,
                    ]);
                    throw new Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
                }

                // Handle upload avatar baru jika ada
                $avatarPath = null;
                if ($this->avatar) {
                    try {
                        // Upload avatar baru
                        $avatarPath = $this->avatar->store('avatars', 'public');

                        // Hapus avatar lama jika ada dan berbeda
                        if ($oldAvatarPath && $oldAvatarPath !== $avatarPath && Storage::disk('public')->exists($oldAvatarPath)) {
                            Storage::disk('public')->delete($oldAvatarPath);
                        }
                    } catch (Exception $e) {
                        Log::error('Profile: Failed to upload avatar', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                        throw new Exception('Gagal upload avatar');
                    }
                }

                // Update profil data di memory dulu (jangan save dulu)
                $user->name = $this->name;
                $user->email = $this->email;
                if ($avatarPath) {
                    $user->avatar_url = $avatarPath;
                }
                $user->no_hp = $normalizedPhone;

                // Update alamat regional menggunakan UserHelper (update metadata di memory)
                UserHelper::updateAlamatRegional($user, [
                    'detail_alamat' => $this->detail_alamat,
                    'kelurahan' => $this->kelurahan,
                    'kecamatan' => $this->kecamatan,
                    'kabupaten_kota' => $this->kabupaten_kota,
                    'provinsi' => $this->provinsi,
                ]);

                // Save user SEKALI SAJA - UserObserver akan auto-sync kolom alamat dari metadata
                $user->save();

                // Reload user dari database untuk get alamat yang sudah di-sync
                $user->refresh();

                // Update alamat lengkap untuk display
                $this->alamat = $user->alamat ?? '';

                // Update password jika diperlukan menggunakan UserHelper
                if ($isChangingPassword) {
                    $passwordUpdated = UserHelper::updatePassword(
                        $user,
                        $this->current_password,
                        $this->password
                    );

                    if (! $passwordUpdated) {
                        throw new Exception('WRONG_PASSWORD');
                    }
                }

                // Update current avatar URL untuk preview
                $this->currentAvatarUrl = $user->avatar_url ?? '';

                // Update original data
                $this->originalName = $this->name;
                $this->originalEmail = $this->email;
                $this->originalNoHp = PhoneNumber::formatLocal($normalizedPhone) ?? '';
                $this->originalDetailAlamat = $this->detail_alamat;
                $this->originalKelurahan = $this->kelurahan;
                $this->originalKecamatan = $this->kecamatan;
                $this->originalKabupatenKota = $this->kabupaten_kota;
                $this->originalProvinsi = $this->provinsi;

                // Reset form password
                $this->reset(['current_password', 'password', 'password_confirmation', 'avatar']);
            });

            $this->success('Profil berhasil diperbarui!', position: 'toast-bottom');

        } catch (Exception $e) {
            // Handle specific error messages
            if ($e->getMessage() === 'WRONG_PASSWORD') {
                $this->error('Password saat ini tidak sesuai!', position: 'toast-bottom');

                return;
            }

            // Handle phone number format error
            if (str_contains($e->getMessage(), 'Format nomor HP tidak valid')) {
                $this->error($e->getMessage(), position: 'toast-bottom');

                return;
            }

            // Log unexpected errors
            Log::error('Profile: Unexpected error during save', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Generic user-friendly message (production-ready)
            $this->error('Gagal memperbarui profil. Silakan coba lagi atau hubungi administrator.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.management.profile', [
            'hasChanges' => $this->hasChanges(),
            'avatarMaxSizeMB' => UserHelper::AVATAR_MAX_SIZE_KB / 1024,
            'passwordMinLength' => UserHelper::PASSWORD_MIN_LENGTH,
        ]);
    }
}
