<?php

namespace App\Livewire\Management\Staf;

use Exception;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helper\Database\UserHelper;
use App\Helper\PhoneNumber;
use App\Helper\AddressMetadata;
use App\Helper\RegionalLocation;

#[Title('Edit Staf')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast, WithFileUploads;

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

    public function mount($id)
    {
        $this->userId = $id;
        $user = User::findOrFail($id);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->no_hp = PhoneNumber::formatLocal($user->no_hp) ?? '';
        $this->currentAvatarUrl = $user->avatar_url ?? '';
        $this->super_admin = $user->super_admin;

        // Load address metadata
        $this->detail_alamat = AddressMetadata::getDetailAlamat($user) ?? '';
        $this->kelurahan = AddressMetadata::getKelurahan($user) ?? '';
        $this->kecamatan = AddressMetadata::getKecamatan($user) ?? '';
        $this->kabupaten_kota = AddressMetadata::getKabupatenKota($user) ?? RegionalLocation::getRegencyName();
        $this->provinsi = AddressMetadata::getProvinsi($user) ?? RegionalLocation::getProvinceName();

        // Load metadata jam kerja & gaji
        $this->jam_masuk = UserHelper::getMetadata($user, 'jam_masuk') ?? '';
        $this->jam_keluar = UserHelper::getMetadata($user, 'jam_keluar') ?? '';
        $this->gaji = UserHelper::getGaji($user) !== null ? (string) UserHelper::getGaji($user) : '';

        // Load alamat lengkap dari kolom alamat (text)
        $this->alamat = $user->alamat ?? '';

        // Load regional options
        $this->loadRegionalOptions();

        // Simpan data original
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
        // Load provinsi options (fixed ke Sulawesi Tenggara)
        $this->provinsiOptions = RegionalLocation::getProvinceOptions();

        // Load kabupaten/kota options (semua kabupaten di Sulawesi Tenggara)
        $this->kabupatenKotaOptions = RegionalLocation::getRegencyOptions();

        // Load kecamatan options jika kabupaten/kota sudah dipilih
        if (!empty($this->kabupaten_kota)) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->kabupaten_kota);
        }

        // Load kelurahan options jika kecamatan sudah dipilih
        if (!empty($this->kecamatan)) {
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
        if (!empty($this->kabupaten_kota)) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->kabupaten_kota);
        }
    }

    public function updatedKecamatan()
    {
        // Reset dependent field
        $this->kelurahan = '';
        $this->kelurahanOptions = [];

        // Load kelurahan options
        if (!empty($this->kecamatan)) {
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
        if (!empty($this->detail_alamat) || !empty($this->kelurahan) || !empty($this->kecamatan)) {
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


    public function save()
    {
        // Validasi menggunakan constants dari UserHelper
        $avatarMaxSizeMB = UserHelper::AVATAR_MAX_SIZE_KB / 1024;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'no_hp' => 'required|string|max:20',
            'avatar' => 'nullable|image|max:' . UserHelper::AVATAR_MAX_SIZE_KB,
            'super_admin' => 'boolean',
            'detail_alamat' => 'required|string|max:500',
            'kelurahan' => 'required|string',
            'kecamatan' => 'required|string',
            'kabupaten_kota' => 'required|string',
            'provinsi' => 'required|string',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'gaji' => 'nullable|integer|min:0',
        ];

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
            'detail_alamat.required' => 'Detail alamat wajib diisi',
            'detail_alamat.max' => 'Detail alamat maksimal 500 karakter',
            'kelurahan.required' => 'Kelurahan wajib dipilih',
            'kecamatan.required' => 'Kecamatan wajib dipilih',
            'kabupaten_kota.required' => 'Kabupaten/Kota wajib dipilih',
            'provinsi.required' => 'Provinsi wajib dipilih',
            'jam_masuk.date_format' => 'Format jam masuk tidak valid (HH:MM)',
            'jam_keluar.date_format' => 'Format jam keluar tidak valid (HH:MM)',
            'gaji.integer' => 'Gaji harus berupa angka',
            'gaji.min' => 'Gaji tidak boleh negatif',
        ];

        // Jika password diisi, tambahkan validasi password
        $isChangingPassword = !empty($this->password);
        if ($isChangingPassword) {
            $rules['password'] = 'min:' . UserHelper::PASSWORD_MIN_LENGTH . '|confirmed';
            $messages['password.min'] = 'Password minimal ' . UserHelper::PASSWORD_MIN_LENGTH . ' karakter';
            $messages['password.confirmed'] = 'Konfirmasi password tidak cocok';
        }

        $this->validate($rules, $messages);

        try {
            DB::transaction(function () use ($isChangingPassword) {
                $user = User::findOrFail($this->userId);

                // Cek jika mengedit diri sendiri
                if ($user->id === Auth::id() && $user->email !== $this->email) {
                    throw new Exception('SELF_EMAIL_CHANGE');
                }

                // Validasi dan normalize nomor HP
                $normalizedPhone = PhoneNumber::normalize($this->no_hp);
                if (!$normalizedPhone) {
                    Log::warning('Staf Edit: Invalid phone number format', [
                        'user_id' => $this->userId,
                        'no_hp' => $this->no_hp,
                    ]);
                    throw new Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
                }

                // Upload avatar baru jika ada
                $avatarPath = null;
                if ($this->avatar) {
                    try {
                        // Observer akan otomatis hapus avatar lama
                        $avatarPath = $this->avatar->store('avatars', 'public');
                    } catch (Exception $e) {
                        Log::error('Staf Edit: Failed to upload avatar', [
                            'user_id' => $this->userId,
                            'error' => $e->getMessage(),
                        ]);
                        throw new Exception('Gagal upload avatar');
                    }
                }

                // Update profil menggunakan UserHelper
                UserHelper::updateProfile(
                    $user,
                    $this->name,
                    $this->email,
                    $avatarPath
                );

                // Update no_hp
                $user->update(['no_hp' => $normalizedPhone]);

                // Update alamat regional menggunakan UserHelper
                UserHelper::updateAlamatRegional($user, [
                    'detail_alamat' => $this->detail_alamat,
                    'kelurahan' => $this->kelurahan,
                    'kecamatan' => $this->kecamatan,
                    'kabupaten_kota' => $this->kabupaten_kota,
                    'provinsi' => $this->provinsi,
                ]);

                // Update metadata jam kerja & gaji
                $metadata = [];
                if (!empty($this->jam_masuk)) {
                    $metadata['jam_masuk'] = $this->jam_masuk;
                }
                if (!empty($this->jam_keluar)) {
                    $metadata['jam_keluar'] = $this->jam_keluar;
                }
                if (!empty($this->gaji)) {
                    $metadata['gaji'] = (int) $this->gaji;
                }

                // Merge metadata
                if (!empty($metadata)) {
                    UserHelper::mergeMetadata($user, $metadata);
                }

                // Save user SEKALI SAJA - Observer akan auto-sync kolom alamat dari metadata
                $user->save();

                // Reload user dari database untuk get alamat yang sudah di-sync
                $user->refresh();

                // Update alamat lengkap untuk display
                $this->alamat = $user->alamat ?? '';

                // Update super_admin flag
                $user->update(['super_admin' => $this->super_admin]);

                // Update password jika diisi
                if ($isChangingPassword) {
                    // Untuk edit staf, tidak perlu validasi current password
                    // Langsung update password
                    $user->update(['password' => \Illuminate\Support\Facades\Hash::make($this->password)]);
                }
            });

            $this->success('Staf berhasil diperbarui!', redirectTo: route('staf.index'), position: 'toast-bottom');
        } catch (Exception $e) {
            // Handle specific error messages
            if ($e->getMessage() === 'SELF_EMAIL_CHANGE') {
                $this->error('Anda tidak dapat mengubah email akun Anda sendiri!', position: 'toast-bottom');
                return;
            }

            // Handle phone number format error
            if (str_contains($e->getMessage(), 'Format nomor HP tidak valid')) {
                $this->error($e->getMessage(), position: 'toast-bottom');
                return;
            }

            // Handle avatar upload error
            if (str_contains($e->getMessage(), 'Gagal upload avatar')) {
                $this->error($e->getMessage(), position: 'toast-bottom');
                return;
            }

            // Log unexpected errors
            Log::error('Staf Edit: Unexpected error during save', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Generic user-friendly message (production-ready)
            $this->error('Gagal memperbarui staf. Silakan coba lagi atau hubungi administrator.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.management.staf.edit', [
            'roleOptions' => $this->roleOptions(),
            'avatarMaxSizeMB' => UserHelper::AVATAR_MAX_SIZE_KB / 1024,
            'passwordMinLength' => UserHelper::PASSWORD_MIN_LENGTH,
        ]);
    }
}
