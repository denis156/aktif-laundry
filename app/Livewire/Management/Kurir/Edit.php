<?php

declare(strict_types=1);

namespace App\Livewire\Management\Kurir;

use Exception;
use Carbon\Carbon;
use App\Models\Kurir;
use Mary\Traits\Toast;
use Livewire\Component;
use App\Helper\PhoneNumber;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use App\Helper\AddressMetadata;
use Livewire\Attributes\Layout;
use App\Helper\RegionalLocation;
use Illuminate\Support\Facades\Log;
use App\Helper\Database\KurirHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

#[Title('Edit Kurir')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast, WithFileUploads;

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
        'tanggal_bergabung' => '',
        'status' => 'Aktif',
        'total_jemput' => 0,
        'total_antar' => 0,
    ];

    public $avatar;

    public ?string $currentAvatarUrl = null;

    public string $password_confirmation = '';

    // * Coverage Area - untuk area layanan kurir
    public array $coverageKecamatan = []; // Motor: single, Mobil: multiple

    public array $coverageKelurahan = []; // Kelurahan yang dilayani (multiple)

    public function mount($id): void
    {
        $this->kurirId = (int) $id;
        $this->loadKurir();
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
                'detail_alamat' => AddressMetadata::getDetailAlamat($kurir),
                'kelurahan' => AddressMetadata::getKelurahan($kurir),
                'kecamatan' => AddressMetadata::getKecamatan($kurir),
                'kabupaten_kota' => AddressMetadata::getKabupatenKota($kurir)
                    ?: RegionalLocation::getRegencyName(),
                'provinsi' => AddressMetadata::getProvinsi($kurir)
                    ?: RegionalLocation::getProvinceName(),
                'alamat' => $kurir->alamat ?? '',
                'no_kendaraan' => $kurir->no_kendaraan ?? '',
                'jenis_kendaraan' => $kurir->jenis_kendaraan ?? '',
                'password' => '',
                'tanggal_bergabung' => $kurir->tanggal_bergabung?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
                'status' => $kurir->status,
                'total_jemput' => $kurir->total_jemput ?? 0,
                'total_antar' => $kurir->total_antar ?? 0,
            ];

            // Load current foto profil URL
            $this->currentAvatarUrl = KurirHelper::getAvatarUrl($kurir);

            // Load coverage area from metadata
            $coverageArea = KurirHelper::getAreaCoverage($kurir);
            if (! empty($coverageArea)) {
                $this->coverageKecamatan = $coverageArea['kecamatan'] ?? [];
                // Flatten kelurahan array
                $this->coverageKelurahan = [];
                foreach (($coverageArea['kelurahan'] ?? []) as $kecamatan => $kelurahanList) {
                    $this->coverageKelurahan = array_merge($this->coverageKelurahan, $kelurahanList);
                }
            }
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
            'formData.detail_alamat' => 'required|string',
            'formData.email' => 'nullable|email|max:255|unique:kurir,email,'.$this->kurirId,
            'formData.no_kendaraan' => 'nullable|string|max:20',
            'formData.jenis_kendaraan' => 'nullable|string|max:50',
            'formData.tanggal_bergabung' => 'required|date',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
            'avatar' => 'nullable|image|max:'.KurirHelper::AVATAR_MAX_SIZE_KB,
            'coverageKecamatan' => 'nullable|array',
            'coverageKelurahan' => 'nullable|array',
        ];

        // Jika password diisi, tambahkan validasi password confirmation
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

            // Build alamat lengkap dari komponen regional
            $alamatLengkap = AddressMetadata::buildAlamatFromArray($this->formData);

            // Prepare data untuk update kurir
            $data = [
                'nama' => $this->formData['nama'],
                'no_hp' => $normalizedPhone,
                'alamat' => $alamatLengkap,
                'email' => $this->formData['email'],
                'no_kendaraan' => $this->formData['no_kendaraan'],
                'jenis_kendaraan' => $this->formData['jenis_kendaraan'],
                'tanggal_bergabung' => Carbon::parse($this->formData['tanggal_bergabung'])->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s'),
                'status' => $this->formData['status'],
            ];

            // Update password jika diisi
            if (! empty($this->formData['password'])) {
                $data['password'] = Hash::make($this->formData['password']);
            }

            $kurir->update($data);

            // Set metadata alamat menggunakan AddressMetadata helper
            AddressMetadata::set(
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
                KurirHelper::setAvatarUrl($kurir, $avatarPath);
            }

            // Set coverage area ke metadata
            $coverageArea = $this->buildCoverageArea();
            if (! empty($coverageArea)) {
                KurirHelper::setAreaCoverage($kurir, $coverageArea);
            }

            // Save metadata
            $kurir->save();

            $this->success('Kurir berhasil diupdate!', position: 'toast-bottom');
            $this->redirect('/management/kurir', navigate: true);
        } catch (QueryException $e) {
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
        return view('livewire.management.kurir.edit', [
            'kecamatanOptions' => $this->getKecamatanOptions(),
            'kelurahanOptions' => $this->getKelurahanOptions(),
            'coverageKecamatanOptions' => $this->getKecamatanOptions(),
            'coverageKelurahanOptions' => $this->getCoverageKelurahanOptions(),
        ]);
    }
}
