<?php

declare(strict_types=1);

namespace App\Livewire\Pelanggan;

use App\Helper\AvatarPlaceholder;
use App\Helper\Database\PelangganHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\Pelanggan;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Profil Saya')]
#[Layout('layouts.pelanggan.app')]
class Profile extends Component
{
    use Toast;
    use WithFileUploads;

    public string $nama = '';

    public string $no_hp = '';

    public string $email = '';

    public string $kode_pelanggan = '';

    public string $currentAvatarUrl = '';

    public $avatar = null;

    // Address Information
    public string $detail_alamat = '';

    public string $kelurahan = '';

    public string $kecamatan = '';

    public string $kabupaten_kota = '';

    public string $provinsi = '';

    public string $latitude = '';

    public string $longitude = '';

    // Options untuk select
    public array $kelurahanOptions = [];

    public array $kecamatanOptions = [];

    public array $kabupatenKotaOptions = [];

    public array $provinsiOptions = [];

    // Alamat lengkap (read-only, auto-generated)
    public string $alamat = '';

    // Modal states
    public bool $editNamaModal = false;

    public bool $editNoHpModal = false;

    public bool $editEmailModal = false;

    public bool $editAlamatModal = false;

    /**
     * Mount component and load pelanggan data
     */
    public function mount(): void
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        if ($pelanggan) {
            $this->loadPelangganData($pelanggan);
            $this->loadRegionalOptions();
        }
    }

    private function loadPelangganData(Pelanggan $pelanggan): void
    {
        $this->kode_pelanggan = $pelanggan->kode_pelanggan;
        $this->nama = $pelanggan->nama;
        $this->no_hp = PhoneNumber::formatLocal($pelanggan->no_hp) ?? '';
        $this->email = $pelanggan->email ?? '';
        $this->currentAvatarUrl = $pelanggan->avatar_url ?? '';

        $this->detail_alamat = $pelanggan->detail_alamat ?? '';
        $this->kelurahan = $pelanggan->kelurahan ?? '';
        $this->kecamatan = $pelanggan->kecamatan ?? '';
        $this->kabupaten_kota = $pelanggan->kabupaten_kota ?? RegionalLocation::getRegencyName();
        $this->provinsi = $pelanggan->provinsi ?? RegionalLocation::getProvinceName();
        $this->latitude = $pelanggan->latitude ? (string) $pelanggan->latitude : '';
        $this->longitude = $pelanggan->longitude ? (string) $pelanggan->longitude : '';

        $this->alamat = $pelanggan->alamat ?? '';
    }

    private function loadRegionalOptions(): void
    {
        $this->provinsiOptions = RegionalLocation::getProvinceOptions();
        $this->kabupatenKotaOptions = RegionalLocation::getRegencyOptions();

        if (! empty($this->kabupaten_kota)) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->kabupaten_kota);
        }

        if (! empty($this->kecamatan)) {
            $this->kelurahanOptions = RegionalLocation::getVillageOptions($this->kecamatan);
        }
    }

    public function updatedKabupatenKota(): void
    {
        $this->kecamatan = '';
        $this->kelurahan = '';
        $this->kecamatanOptions = [];
        $this->kelurahanOptions = [];

        if (! empty($this->kabupaten_kota)) {
            $this->kecamatanOptions = RegionalLocation::getDistrictOptions($this->kabupaten_kota);
        }
    }

    public function updatedKecamatan(): void
    {
        $this->kelurahan = '';
        $this->kelurahanOptions = [];

        if (! empty($this->kecamatan)) {
            $this->kelurahanOptions = RegionalLocation::getVillageOptions($this->kecamatan);
        }

        $this->updateAlamatPreview();
    }

    public function updatedDetailAlamat(): void
    {
        $this->updateAlamatPreview();
    }

    public function updatedKelurahan(): void
    {
        $this->updateAlamatPreview();
    }

    private function updateAlamatPreview(): void
    {
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

    public function updatedAvatar(): void
    {
        $this->validate([
            'avatar' => 'image|max:'.PelangganHelper::AVATAR_MAX_SIZE_KB,
        ], [
            'avatar.image' => 'File harus berupa gambar',
            'avatar.max' => 'Ukuran file maksimal '.(PelangganHelper::AVATAR_MAX_SIZE_KB / 1024).' MB',
        ]);

        try {
            DB::transaction(function () {
                $pelanggan = Auth::guard('pelanggan')->user();

                $avatarPath = $this->avatar->store('avatars/pelanggan', 'public');
                $pelanggan->avatar_url = $avatarPath;
                $pelanggan->save();

                $this->currentAvatarUrl = $avatarPath;
                $this->avatar = null;
            });

            $this->success('Avatar berhasil diperbarui!', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Profile Update: Failed to update avatar', [
                'error' => $e->getMessage(),
            ]);
            $this->error('Gagal memperbarui avatar. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function saveNama(): void
    {
        $this->validate([
            'nama' => 'required|string|max:255',
        ], [
            'nama.required' => 'Nama wajib diisi',
            'nama.max' => 'Nama maksimal 255 karakter',
        ]);

        try {
            DB::transaction(function () {
                $pelanggan = Auth::guard('pelanggan')->user();
                $pelanggan->update(['nama' => $this->nama]);
            });

            $this->editNamaModal = false;
            $this->success('Nama berhasil diperbarui!', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Profile Update: Failed to update nama', [
                'error' => $e->getMessage(),
            ]);
            $this->error('Gagal memperbarui nama. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function saveNoHp(): void
    {
        $this->validate([
            'no_hp' => 'required|string|max:20',
        ], [
            'no_hp.required' => 'Nomor HP wajib diisi',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter',
        ]);

        try {
            DB::transaction(function () {
                $pelanggan = Auth::guard('pelanggan')->user();

                $normalizedPhone = PhoneNumber::normalize($this->no_hp);
                if (! $normalizedPhone) {
                    throw new Exception('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8');
                }

                $pelanggan->update(['no_hp' => $normalizedPhone]);
                $this->no_hp = PhoneNumber::formatLocal($normalizedPhone) ?? '';
            });

            $this->editNoHpModal = false;
            $this->success('No. HP berhasil diperbarui!', position: 'toast-bottom');
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'Format nomor HP tidak valid')) {
                $this->error($errorMessage, position: 'toast-bottom');
            } else {
                Log::error('Profile Update: Failed to update no_hp', [
                    'error' => $e->getMessage(),
                ]);
                $this->error('Gagal memperbarui No. HP. Silakan coba lagi.', position: 'toast-bottom');
            }
        }
    }

    public function saveEmail(): void
    {
        $this->validate([
            'email' => 'nullable|email|max:255|unique:pelanggan,email,'.Auth::guard('pelanggan')->id(),
        ], [
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
        ]);

        try {
            DB::transaction(function () {
                $pelanggan = Auth::guard('pelanggan')->user();
                $pelanggan->update(['email' => $this->email ?: null]);
            });

            $this->editEmailModal = false;
            $this->success('Email berhasil diperbarui!', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Profile Update: Failed to update email', [
                'error' => $e->getMessage(),
            ]);
            $this->error('Gagal memperbarui email. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function saveAlamat(): void
    {
        $this->validate([
            'detail_alamat' => 'required|string|max:500',
            'kelurahan' => 'required|string',
            'kecamatan' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'detail_alamat.required' => 'Detail alamat wajib diisi',
            'detail_alamat.max' => 'Detail alamat maksimal 500 karakter',
            'kelurahan.required' => 'Kelurahan wajib dipilih',
            'kecamatan.required' => 'Kecamatan wajib dipilih',
            'latitude.numeric' => 'Latitude harus berupa angka',
            'latitude.between' => 'Latitude harus antara -90 sampai 90',
            'longitude.numeric' => 'Longitude harus berupa angka',
            'longitude.between' => 'Longitude harus antara -180 sampai 180',
        ]);

        try {
            DB::transaction(function () {
                $pelanggan = Auth::guard('pelanggan')->user();

                PelangganHelper::setAlamatRegional(
                    $pelanggan,
                    $this->detail_alamat,
                    $this->kelurahan,
                    $this->kecamatan,
                    $this->kabupaten_kota,
                    $this->provinsi,
                    ! empty($this->latitude) ? (float) $this->latitude : null,
                    ! empty($this->longitude) ? (float) $this->longitude : null
                );

                $pelanggan->save();
                $this->alamat = $pelanggan->fresh()->alamat ?? '';
            });

            $this->editAlamatModal = false;
            $this->success('Alamat berhasil diperbarui!', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Profile Update: Failed to update address', [
                'error' => $e->getMessage(),
            ]);
            $this->error('Gagal memperbarui alamat. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        $avatarUrl = $this->avatar
            ? $this->avatar->temporaryUrl()
            : AvatarPlaceholder::getAvatarOrPlaceholder($this->currentAvatarUrl, $this->nama, 256);

        return view('livewire.pelanggan.profile', [
            'avatarUrl' => $avatarUrl,
            'avatarMaxSizeMB' => PelangganHelper::AVATAR_MAX_SIZE_KB / 1024,
        ]);
    }
}
