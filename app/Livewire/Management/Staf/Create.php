<?php

namespace App\Livewire\Management\Staf;

use App\Helper\Database\UserHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\User;
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
    use Toast, WithFileUploads;

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

    public function save()
    {
        // Validasi menggunakan constants dari UserHelper
        $avatarMaxSizeMB = UserHelper::AVATAR_MAX_SIZE_KB / 1024;

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'no_hp' => 'required|string|max:20',
            'password' => 'required|min:'.UserHelper::PASSWORD_MIN_LENGTH.'|confirmed',
            'avatar' => 'nullable|image|max:'.UserHelper::AVATAR_MAX_SIZE_KB,
            'super_admin' => 'boolean',
            'detail_alamat' => 'required|string|max:500',
            'kelurahan' => 'required|string',
            'kecamatan' => 'required|string',
            'kabupaten_kota' => 'nullable|string',
            'provinsi' => 'nullable|string',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'gaji' => 'nullable|integer|min:0',
        ], [
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
            'detail_alamat.required' => 'Detail alamat wajib diisi',
            'detail_alamat.max' => 'Detail alamat maksimal 500 karakter',
            'kelurahan.required' => 'Kelurahan wajib dipilih',
            'kecamatan.required' => 'Kecamatan wajib dipilih',
            'jam_masuk.date_format' => 'Format jam masuk tidak valid (HH:MM)',
            'jam_keluar.date_format' => 'Format jam keluar tidak valid (HH:MM)',
            'gaji.integer' => 'Gaji harus berupa angka',
            'gaji.min' => 'Gaji tidak boleh negatif',
        ]);

        try {
            DB::transaction(function () {
                // Validasi dan normalize nomor HP
                $normalizedPhone = PhoneNumber::normalize($this->no_hp);
                if (! $normalizedPhone) {
                    Log::warning('Staf Create: Invalid phone number format', [
                        'no_hp' => $this->no_hp,
                    ]);
                    throw new Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
                }

                // Upload avatar jika ada
                $avatarPath = null;
                if ($this->avatar) {
                    try {
                        $avatarPath = $this->avatar->store('avatars', 'public');
                    } catch (Exception $e) {
                        Log::error('Staf Create: Failed to upload avatar', [
                            'error' => $e->getMessage(),
                        ]);
                        throw new Exception('Gagal upload avatar');
                    }
                }

                // Prepare metadata jam kerja & gaji
                $metadata = [];
                if (! empty($this->jam_masuk)) {
                    $metadata['jam_masuk'] = $this->jam_masuk;
                }
                if (! empty($this->jam_keluar)) {
                    $metadata['jam_keluar'] = $this->jam_keluar;
                }
                if (! empty($this->gaji)) {
                    $metadata['gaji'] = (int) $this->gaji;
                }

                // Buat user baru menggunakan UserHelper dengan alamat lengkap
                $user = UserHelper::createUser([
                    'name' => $this->name,
                    'email' => $this->email,
                    'no_hp' => $normalizedPhone,
                    'password' => $this->password,
                    'avatar_url' => $avatarPath,
                    'super_admin' => $this->super_admin,
                    // Alamat regional
                    'detail_alamat' => $this->detail_alamat,
                    'kelurahan' => $this->kelurahan,
                    'kecamatan' => $this->kecamatan,
                    'kabupaten_kota' => $this->kabupaten_kota,
                    'provinsi' => $this->provinsi,
                ]);

                // Update metadata jam kerja & gaji
                if (! empty($metadata)) {
                    UserHelper::mergeMetadata($user, $metadata);
                    $user->save();
                }
            });

            $this->success('Staf berhasil ditambahkan!', redirectTo: route('staf.index'), position: 'toast-bottom');
        } catch (Exception $e) {
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
            Log::error('Staf Create: Unexpected error during save', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Generic user-friendly message (production-ready)
            $this->error('Gagal menambahkan staf. Silakan coba lagi atau hubungi administrator.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.management.staf.create', [
            'roleOptions' => $this->roleOptions(),
            'avatarMaxSizeMB' => UserHelper::AVATAR_MAX_SIZE_KB / 1024,
            'passwordMinLength' => UserHelper::PASSWORD_MIN_LENGTH,
        ]);
    }
}
