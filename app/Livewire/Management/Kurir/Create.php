<?php

declare(strict_types=1);

namespace App\Livewire\Management\Kurir;

use App\Helper\AddressMetadata;
use App\Helper\Database\KurirHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\Kurir;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
    ];

    public $avatar;

    // * Coverage Area - untuk area layanan kurir
    public array $coverageKecamatan = []; // Motor: single, Mobil: multiple

    public array $coverageKelurahan = []; // Kelurahan yang dilayani (multiple)

    public function mount(): void
    {
        $this->refreshKodeKurir();
        $this->formData['tanggal_bergabung'] = now()->format('Y-m-d\TH:i');

        // Set default kabupaten/kota dan provinsi menggunakan RegionalLocation Helper
        $this->formData['kabupaten_kota'] = RegionalLocation::getRegencyName();
        $this->formData['provinsi'] = RegionalLocation::getProvinceName();
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
        $this->validate([
            'formData.kode_kurir' => 'required|unique:kurir,kode_kurir',
            'formData.nama' => 'required|string|max:255',
            'formData.no_hp' => 'required|string|max:15|unique:kurir,no_hp',
            'formData.detail_alamat' => 'required|string',
            'formData.email' => 'nullable|email|max:255|unique:kurir,email',
            'formData.no_kendaraan' => 'nullable|string|max:20',
            'formData.jenis_kendaraan' => 'nullable|string|max:50',
            'formData.password' => 'required|min:'.KurirHelper::PASSWORD_MIN_LENGTH.'|confirmed',
            'formData.tanggal_bergabung' => 'required|date',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
            'avatar' => 'nullable|image|max:'.KurirHelper::AVATAR_MAX_SIZE_KB,
            'coverageKecamatan' => 'nullable|array',
            'coverageKelurahan' => 'nullable|array',
        ]);

        try {
            // Normalize phone number menggunakan PhoneNumber helper
            $normalizedPhone = PhoneNumber::normalize($this->formData['no_hp']);
            if (! $normalizedPhone) {
                $this->error('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8', position: 'toast-bottom');

                return;
            }

            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function () use ($normalizedPhone) {
                // Cek ulang apakah kode kurir sudah ada, jika ya generate ulang
                if (Kurir::where('kode_kurir', $this->formData['kode_kurir'])->exists()) {
                    $this->refreshKodeKurir();
                }

                // Build alamat lengkap dari komponen regional
                $alamatLengkap = AddressMetadata::buildAlamatFromArray($this->formData);

                // Prepare data untuk create kurir
                $data = [
                    'kode_kurir' => $this->formData['kode_kurir'],
                    'nama' => $this->formData['nama'],
                    'no_hp' => $normalizedPhone,
                    'alamat' => $alamatLengkap,
                    'email' => $this->formData['email'],
                    'no_kendaraan' => $this->formData['no_kendaraan'],
                    'jenis_kendaraan' => $this->formData['jenis_kendaraan'],
                    'password' => Hash::make($this->formData['password']),
                    'tanggal_bergabung' => Carbon::parse($this->formData['tanggal_bergabung'])->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s'),
                    'status' => $this->formData['status'],
                    'total_jemput' => 0,
                    'total_antar' => 0,
                ];

                // Create kurir
                $kurir = Kurir::create($data);

                // Set metadata alamat menggunakan AddressMetadata helper
                AddressMetadata::set(
                    $kurir,
                    $this->formData['detail_alamat'],
                    $this->formData['kelurahan'],
                    $this->formData['kecamatan'],
                    $this->formData['kabupaten_kota'],
                    $this->formData['provinsi']
                );

                // Upload foto profil jika ada
                if ($this->avatar) {
                    $avatarPath = $this->avatar->store('avatars/kurir', 'public');
                    KurirHelper::setAvatarUrl($kurir, $avatarPath);
                }

                // Set coverage area ke metadata
                $coverageArea = $this->buildCoverageArea();
                if (! empty($coverageArea)) {
                    KurirHelper::setAreaCoverage($kurir, $coverageArea);
                }

                // Save metadata
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

    public function getKecamatanOptions(): array
    {
        // Get kecamatan di Kota Kendari dari API menggunakan RegionalLocation helper
        $districts = RegionalLocation::getKendariDistricts();

        // Transform ke format yang dibutuhkan oleh x-select component
        return collect($districts)->map(fn (array $district) => [
            'id' => $district['name'] ?? '',
            'name' => $district['name'] ?? '',
        ])->toArray();
    }

    public function getKelurahanOptions(): array
    {
        // Kelurahan berdasarkan kecamatan yang dipilih menggunakan RegionalLocation Helper
        $kecamatanName = $this->formData['kecamatan'] ?? '';

        if (empty($kecamatanName)) {
            return [];
        }

        // Cari district code berdasarkan nama kecamatan
        $districts = RegionalLocation::getKendariDistricts();
        $districtCode = null;

        foreach ($districts as $district) {
            if (($district['name'] ?? '') === $kecamatanName) {
                $districtCode = $district['code'] ?? null;
                break;
            }
        }

        if (! $districtCode) {
            return [];
        }

        // Get kelurahan/desa berdasarkan district code dari API
        $villages = RegionalLocation::getVillagesByDistrict($districtCode);

        // Transform ke format yang dibutuhkan oleh x-select component
        return collect($villages)->map(fn (array $village) => [
            'id' => $village['name'] ?? '',
            'name' => $village['name'] ?? '',
        ])->toArray();
    }

    // * Get coverage kelurahan options berdasarkan kecamatan yang dipilih di coverage area
    public function getCoverageKelurahanOptions(): array
    {
        // Jika tidak ada kecamatan yang dipilih, return empty
        if (empty($this->coverageKecamatan)) {
            return [];
        }

        // Get semua kecamatan di Kendari
        $districts = RegionalLocation::getKendariDistricts();
        $allVillages = [];

        // Loop semua kecamatan yang dipilih
        foreach ($this->coverageKecamatan as $kecamatanName) {
            // Cari district code
            $districtCode = null;
            foreach ($districts as $district) {
                if (($district['name'] ?? '') === $kecamatanName) {
                    $districtCode = $district['code'] ?? null;
                    break;
                }
            }

            // Get villages dari kecamatan ini
            if ($districtCode) {
                $villages = RegionalLocation::getVillagesByDistrict($districtCode);

                foreach ($villages as $village) {
                    $villageName = $village['name'] ?? '';
                    if ($villageName) {
                        // Format: "Kelurahan Name (Kecamatan Name)"
                        $allVillages[] = [
                            'id' => $villageName,
                            'name' => $villageName.' ('.$kecamatanName.')',
                            'kecamatan' => $kecamatanName,
                        ];
                    }
                }
            }
        }

        return $allVillages;
    }

    // * Build coverage area dari input
    protected function buildCoverageArea(): array
    {
        if (empty($this->coverageKecamatan)) {
            return [];
        }

        // Kelompokkan kelurahan berdasarkan kecamatan
        $kelurahanByKecamatan = [];
        $allKecamatanInCoverage = [];

        // Get semua kecamatan untuk mapping
        $districts = RegionalLocation::getKendariDistricts();

        foreach ($this->coverageKecamatan as $kecamatanName) {
            $allKecamatanInCoverage[] = $kecamatanName;
            $kelurahanByKecamatan[$kecamatanName] = [];
        }

        // Map kelurahan ke kecamatan masing-masing
        foreach ($this->coverageKelurahan as $kelurahanName) {
            // Find kecamatan untuk kelurahan ini
            foreach ($allKecamatanInCoverage as $kecamatanName) {
                // Cari district code
                $districtCode = null;
                foreach ($districts as $district) {
                    if (($district['name'] ?? '') === $kecamatanName) {
                        $districtCode = $district['code'] ?? null;
                        break;
                    }
                }

                if ($districtCode) {
                    $villages = RegionalLocation::getVillagesByDistrict($districtCode);
                    foreach ($villages as $village) {
                        if (($village['name'] ?? '') === $kelurahanName) {
                            $kelurahanByKecamatan[$kecamatanName][] = $kelurahanName;
                            break 2;
                        }
                    }
                }
            }
        }

        return [
            'kecamatan' => $allKecamatanInCoverage,
            'kelurahan' => $kelurahanByKecamatan,
        ];
    }

    // * Updated when coverageKecamatan changes
    public function updatedCoverageKecamatan(): void
    {
        // Reset kelurahan yang tidak termasuk dalam kecamatan terpilih
        $this->resetCoverageKelurahan();
    }

    // * Reset coverage kelurahan
    protected function resetCoverageKelurahan(): void
    {
        if (empty($this->coverageKecamatan)) {
            $this->coverageKelurahan = [];

            return;
        }

        // Get semua kelurahan yang valid dari kecamatan terpilih
        $validKelurahan = collect($this->getCoverageKelurahanOptions())
            ->pluck('id')
            ->toArray();

        // Filter hanya kelurahan yang valid
        $this->coverageKelurahan = array_intersect($this->coverageKelurahan, $validKelurahan);
    }

    public function render(): mixed
    {
        return view('livewire.management.kurir.create', [
            'kecamatanOptions' => $this->getKecamatanOptions(),
            'kelurahanOptions' => $this->getKelurahanOptions(),
            'coverageKecamatanOptions' => $this->getKecamatanOptions(),
            'coverageKelurahanOptions' => $this->getCoverageKelurahanOptions(),
        ]);
    }
}
