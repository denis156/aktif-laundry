<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pelanggan;

use App\Helper\AvatarPlaceholder;
use App\Helper\Database\PelangganHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\Pelanggan;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Edit Pelanggan')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;
    use WithFileUploads;

    public int $pelangganId;

    public string $kode_pelanggan = '';

    public string $nama = '';

    public string $no_hp = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $currentAvatarUrl = '';

    public $avatar = null;

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

    public string $tanggal_daftar = '';

    public string $status = 'Aktif';

    // Alamat lengkap (read-only, auto-generated)
    public string $alamat = '';

    public function mount($id): void
    {
        $this->pelangganId = (int) $id;
        $pelanggan = Pelanggan::findOrFail($this->pelangganId);

        $this->loadPelangganData($pelanggan);
        $this->loadRegionalOptions();
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

        $this->tanggal_daftar = $pelanggan->tanggal_daftar?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
        $this->status = $pelanggan->status;

        $this->alamat = $pelanggan->alamat ?? '';
    }

    private function loadRegionalOptions(): void
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

    public function updatedKabupatenKota(): void
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

    public function updatedKecamatan(): void
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

    public function statusOptions(): array
    {
        return [
            ['id' => 'Aktif', 'name' => 'Aktif'],
            ['id' => 'Tidak Aktif', 'name' => 'Tidak Aktif'],
        ];
    }

    public function save(): void
    {
        $isChangingPassword = ! empty($this->password);
        $this->validate($this->validationRules($isChangingPassword), $this->validationMessages($isChangingPassword));

        try {
            DB::transaction(function () use ($isChangingPassword) {
                $pelanggan = Pelanggan::findOrFail($this->pelangganId);

                $normalizedPhone = $this->validateAndNormalizePhone();
                $avatarPath = $this->uploadAvatar();

                $this->updatePelangganProfile($pelanggan, $avatarPath);
                $this->updatePelangganAddress($pelanggan, $normalizedPhone);
                $this->updatePelangganPassword($pelanggan, $isChangingPassword);

                $pelanggan->update(['status' => $this->status]);
                $this->alamat = $pelanggan->fresh()->alamat ?? '';
            });

            $this->success('Pelanggan berhasil diperbarui!', redirectTo: route('pelanggan.index'), position: 'toast-bottom');
        } catch (Exception $e) {
            $this->handleSaveError($e);
        }
    }

    private function validationRules(bool $isChangingPassword): array
    {
        $rules = [
            'kode_pelanggan' => 'required|unique:pelanggan,kode_pelanggan,'.$this->pelangganId,
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'nullable|email|max:255|unique:pelanggan,email,'.$this->pelangganId,
            'avatar' => 'nullable|image|max:'.PelangganHelper::AVATAR_MAX_SIZE_KB,
            'detail_alamat' => 'required|string|max:500',
            'kelurahan' => 'required|string',
            'kecamatan' => 'required|string',
            'kabupaten_kota' => 'nullable|string',
            'provinsi' => 'nullable|string',
            'tanggal_daftar' => 'required|date',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ];

        if ($isChangingPassword) {
            $rules['password'] = 'min:'.PelangganHelper::PASSWORD_MIN_LENGTH.'|confirmed';
        }

        return $rules;
    }

    private function validationMessages(bool $isChangingPassword): array
    {
        $avatarMaxSizeMB = PelangganHelper::AVATAR_MAX_SIZE_KB / 1024;

        $messages = [
            'kode_pelanggan.required' => 'Kode pelanggan wajib diisi',
            'kode_pelanggan.unique' => 'Kode pelanggan sudah digunakan',
            'nama.required' => 'Nama wajib diisi',
            'nama.string' => 'Nama harus berupa teks',
            'nama.max' => 'Nama maksimal 255 karakter',
            'no_hp.required' => 'Nomor HP wajib diisi',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'avatar.image' => 'File harus berupa gambar',
            'avatar.max' => "Ukuran file maksimal {$avatarMaxSizeMB} MB",
            'detail_alamat.required' => 'Detail alamat wajib diisi',
            'detail_alamat.max' => 'Detail alamat maksimal 500 karakter',
            'kelurahan.required' => 'Kelurahan wajib dipilih',
            'kecamatan.required' => 'Kecamatan wajib dipilih',
            'tanggal_daftar.required' => 'Tanggal daftar wajib diisi',
            'tanggal_daftar.date' => 'Format tanggal tidak valid',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
        ];

        if ($isChangingPassword) {
            $messages['password.min'] = 'Password minimal '.PelangganHelper::PASSWORD_MIN_LENGTH.' karakter';
            $messages['password.confirmed'] = 'Konfirmasi password tidak cocok';
        }

        return $messages;
    }

    private function validateAndNormalizePhone(): string
    {
        $normalizedPhone = PhoneNumber::normalize($this->no_hp);

        if (! $normalizedPhone) {
            Log::warning('Pelanggan Edit: Invalid phone number format', [
                'pelanggan_id' => $this->pelangganId,
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
            return $this->avatar->store('avatars/pelanggan', 'public');
        } catch (Exception $e) {
            Log::error('Pelanggan Edit: Failed to upload avatar', [
                'pelanggan_id' => $this->pelangganId,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Gagal upload avatar');
        }
    }

    private function updatePelangganProfile(Pelanggan $pelanggan, ?string $avatarPath): void
    {
        $pelanggan->update([
            'kode_pelanggan' => $this->kode_pelanggan,
            'nama' => $this->nama,
            'email' => $this->email ?: null,
            'tanggal_daftar' => $this->tanggal_daftar,
        ]);

        // Update avatar jika ada
        if ($avatarPath) {
            $pelanggan->avatar_url = $avatarPath;
            $pelanggan->save();
        }
    }

    private function updatePelangganAddress(Pelanggan $pelanggan, string $normalizedPhone): void
    {
        PelangganHelper::setAlamatRegional(
            $pelanggan,
            $this->detail_alamat,
            $this->kelurahan,
            $this->kecamatan,
            $this->kabupaten_kota,
            $this->provinsi
        );

        $pelanggan->no_hp = $normalizedPhone;
        $pelanggan->save();
    }

    private function updatePelangganPassword(Pelanggan $pelanggan, bool $isChangingPassword): void
    {
        if (! $isChangingPassword) {
            return;
        }

        $pelanggan->update(['password' => Hash::make($this->password)]);
    }

    private function handleSaveError(Exception $e): void
    {
        $errorMessage = $e->getMessage();

        if (str_contains($errorMessage, 'Format nomor HP tidak valid') || str_contains($errorMessage, 'Gagal upload avatar')) {
            $this->error($errorMessage, position: 'toast-bottom');

            return;
        }

        Log::error('Pelanggan Edit: Unexpected error during save', [
            'pelanggan_id' => $this->pelangganId,
            'error' => $errorMessage,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $this->error('Gagal memperbarui pelanggan. Silakan coba lagi atau hubungi administrator.', position: 'toast-bottom');
    }

    public function render(): mixed
    {
        $avatarUrl = $this->avatar
            ? $this->avatar->temporaryUrl()
            : AvatarPlaceholder::getAvatarOrPlaceholder($this->currentAvatarUrl, $this->nama, 256);

        return view('livewire.management.pelanggan.edit', [
            'statusOptions' => $this->statusOptions(),
            'avatarMaxSizeMB' => PelangganHelper::AVATAR_MAX_SIZE_KB / 1024,
            'passwordMinLength' => PelangganHelper::PASSWORD_MIN_LENGTH,
            'avatarUrl' => $avatarUrl,
        ]);
    }
}
