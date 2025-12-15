<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\Pelanggan;

use App\Helper\AvatarPlaceholder;
use App\Helper\Database\PelangganHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Title('Tambah Pelanggan')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;
    use WithFileUploads;

    public string $kode_pelanggan = '';

    public string $nama = '';

    public string $no_hp = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public $avatar = null;

    // Address Information
    public string $detail_alamat = '';

    public string $kelurahan = '';

    public string $kecamatan = '';

    public string $kabupaten_kota = '';

    public string $provinsi = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $sharelok = '';

    // Options untuk select
    public array $kelurahanOptions = [];

    public array $kecamatanOptions = [];

    public array $kabupatenKotaOptions = [];

    public array $provinsiOptions = [];

    public string $tanggal_daftar = '';

    public string $status = 'Aktif';

    // Alamat lengkap (read-only, auto-generated)
    public string $alamat = '';

    public function mount(): void
    {
        $this->refreshKodePelanggan();
        $this->tanggal_daftar = now()->format('Y-m-d\TH:i');

        // Set default provinsi dan kabupaten/kota
        $this->provinsi = RegionalLocation::getProvinceName();
        $this->kabupaten_kota = RegionalLocation::getRegencyName();

        // Load regional options
        $this->loadRegionalOptions();
    }

    public function refreshKodePelanggan(): void
    {
        try {
            $this->kode_pelanggan = PelangganHelper::generateKodePelanggan();
        } catch (Exception $e) {
            Log::error('Failed to generate kode pelanggan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal generate kode pelanggan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    private function loadRegionalOptions(): void
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

    public function updatedProvinsi(): void
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

    public function updatedSharelok(): void
    {
        $this->extractCoordinatesFromUrl();
    }

    #[On('coordinates-updated')]
    public function onCoordinatesUpdated(array $data): void
    {
        $this->latitude = $data['latitude'] ?? '';
        $this->longitude = $data['longitude'] ?? '';
    }

    private function extractCoordinatesFromUrl(): void
    {
        if (empty($this->sharelok)) {
            return;
        }

        // Pattern untuk berbagai format Google Maps URL
        $patterns = [
            // Format: @-3.992409,122.517426
            '/@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            // Format: !3d-3.992409!4d122.517426
            '/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/',
            // Format: 3°59'32.7"S 122°31'02.7"E (DMS)
            '/(\d+)°(\d+)\'([\d.]+)"([NS])\s+(\d+)°(\d+)\'([\d.]+)"([EW])/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->sharelok, $matches)) {
                if (count($matches) === 9) {
                    // DMS format
                    $lat = $this->convertDMSToDecimal((int) $matches[1], (int) $matches[2], (float) $matches[3], $matches[4]);
                    $lon = $this->convertDMSToDecimal((int) $matches[5], (int) $matches[6], (float) $matches[7], $matches[8]);
                    $this->latitude = (string) $lat;
                    $this->longitude = (string) $lon;

                    return;
                } elseif (count($matches) === 3) {
                    // Decimal format
                    $this->latitude = $matches[1];
                    $this->longitude = $matches[2];

                    return;
                }
            }
        }
    }

    private function convertDMSToDecimal(int $degrees, int $minutes, float $seconds, string $direction): float
    {
        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if ($direction === 'S' || $direction === 'W') {
            $decimal *= -1;
        }

        return round($decimal, 6);
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
        $this->validate($this->validationRules(), $this->validationMessages());

        try {
            DB::transaction(function () {
                $normalizedPhone = $this->validateAndNormalizePhone();
                $avatarPath = $this->uploadAvatar();

                PelangganHelper::createPelanggan([
                    'nama' => $this->nama,
                    'no_hp' => $normalizedPhone,
                    'email' => $this->email ?: null,
                    'password' => $this->password ?: null,
                    'detail_alamat' => $this->detail_alamat,
                    'kelurahan' => $this->kelurahan,
                    'kecamatan' => $this->kecamatan,
                    'kabupaten_kota' => $this->kabupaten_kota,
                    'provinsi' => $this->provinsi,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'avatar_url' => $avatarPath,
                ], $this->tanggal_daftar);
            });

            $this->success('Pelanggan berhasil ditambahkan!', redirectTo: route('pelanggan.index'), position: 'toast-bottom');
        } catch (Exception $e) {
            $this->handleSaveError($e);
        }
    }

    private function validationRules(): array
    {
        $rules = [
            'kode_pelanggan' => 'required|unique:pelanggan,kode_pelanggan',
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'nullable|email|max:255|unique:pelanggan,email',
            'avatar' => 'nullable|image|max:'.PelangganHelper::AVATAR_MAX_SIZE_KB,
            'detail_alamat' => 'required|string|max:500',
            'kelurahan' => 'nullable|string',
            'kecamatan' => 'nullable|string',
            'kabupaten_kota' => 'nullable|string',
            'provinsi' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'tanggal_daftar' => 'required|date',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ];

        // Jika password diisi, tambahkan validasi
        if (! empty($this->password)) {
            $rules['password'] = 'min:'.PelangganHelper::PASSWORD_MIN_LENGTH.'|confirmed';
        }

        return $rules;
    }

    private function validationMessages(): array
    {
        $avatarMaxSizeMB = PelangganHelper::AVATAR_MAX_SIZE_KB / 1024;

        return [
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
            'latitude.required' => 'Latitude wajib diisi',
            'latitude.numeric' => 'Latitude harus berupa angka',
            'latitude.between' => 'Latitude harus antara -90 sampai 90',
            'longitude.required' => 'Longitude wajib diisi',
            'longitude.numeric' => 'Longitude harus berupa angka',
            'longitude.between' => 'Longitude harus antara -180 sampai 180',
            'tanggal_daftar.required' => 'Tanggal daftar wajib diisi',
            'tanggal_daftar.date' => 'Format tanggal tidak valid',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
            'password.min' => 'Password minimal '.PelangganHelper::PASSWORD_MIN_LENGTH.' karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ];
    }

    private function validateAndNormalizePhone(): string
    {
        $normalizedPhone = PhoneNumber::normalize($this->no_hp);

        if (! $normalizedPhone) {
            Log::warning('Pelanggan Create: Invalid phone number format', [
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
            Log::error('Pelanggan Create: Failed to upload avatar', [
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

        Log::error('Pelanggan Create: Unexpected error during save', [
            'error' => $errorMessage,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $this->error('Gagal menambahkan pelanggan. Silakan coba lagi atau hubungi administrator.', position: 'toast-bottom');
    }

    public function render(): mixed
    {
        $avatarUrl = $this->avatar
            ? $this->avatar->temporaryUrl()
            : AvatarPlaceholder::generate($this->nama, 256);

        return view('livewire.management.pages.pelanggan.create', [
            'statusOptions' => $this->statusOptions(),
            'avatarMaxSizeMB' => PelangganHelper::AVATAR_MAX_SIZE_KB / 1024,
            'passwordMinLength' => PelangganHelper::PASSWORD_MIN_LENGTH,
            'avatarUrl' => $avatarUrl,
        ]);
    }
}
