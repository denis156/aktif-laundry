<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pelanggan;

use App\Helper\Database\PelangganHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\Pelanggan;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
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

    public array $formData = [
        'kode_pelanggan' => '',
        'nama' => '',
        'no_hp' => '',
        'email' => '',
        'detail_alamat' => '',
        'kelurahan' => '',
        'kecamatan' => '',
        'kabupaten_kota' => '',
        'provinsi' => '',
        'password' => '',
        'password_confirmation' => '',
        'tanggal_daftar' => '',
        'status' => 'Aktif',
    ];

    public $avatar;

    public function mount(): void
    {
        $this->refreshKodePelanggan();
        $this->formData['tanggal_daftar'] = now()->format('Y-m-d\TH:i');

        // Set default kabupaten/kota dan provinsi menggunakan RegionalLocation Helper
        $this->formData['kabupaten_kota'] = RegionalLocation::getRegencyName();
        $this->formData['provinsi'] = RegionalLocation::getProvinceName();
    }

    public function refreshKodePelanggan(): void
    {
        try {
            $this->formData['kode_pelanggan'] = PelangganHelper::generateKodePelanggan();
        } catch (Exception $e) {
            Log::error('Failed to generate kode pelanggan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal generate kode pelanggan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function save(): void
    {
        $rules = [
            'formData.kode_pelanggan' => 'required|unique:pelanggan,kode_pelanggan',
            'formData.nama' => 'required|string|max:255',
            'formData.no_hp' => 'required|string|max:15',
            'formData.detail_alamat' => 'required|string',
            'formData.email' => 'nullable|email|max:255',
            'formData.tanggal_daftar' => 'required|date',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
            'avatar' => 'nullable|image|max:'.PelangganHelper::AVATAR_MAX_SIZE_KB,
        ];

        // Jika password diisi, tambahkan validasi password confirmation
        if (! empty($this->formData['password'])) {
            $rules['formData.password'] = 'min:'.PelangganHelper::PASSWORD_MIN_LENGTH.'|confirmed';
        }

        $this->validate($rules);

        try {
            // Normalize phone number menggunakan PhoneNumber helper
            $normalizedPhone = PhoneNumber::normalize($this->formData['no_hp']);
            if (! $normalizedPhone) {
                $this->error('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8', position: 'toast-bottom');

                return;
            }

            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function () use ($normalizedPhone) {
                // Cek ulang apakah kode pelanggan sudah ada, jika ya generate ulang
                if (Pelanggan::where('kode_pelanggan', $this->formData['kode_pelanggan'])->exists()) {
                    $this->refreshKodePelanggan();
                }

                // Prepare data untuk create pelanggan menggunakan PelangganHelper
                $data = [
                    'nama' => $this->formData['nama'],
                    'no_hp' => $normalizedPhone,
                    'email' => $this->formData['email'],
                    'detail_alamat' => $this->formData['detail_alamat'],
                    'kelurahan' => $this->formData['kelurahan'],
                    'kecamatan' => $this->formData['kecamatan'],
                    'kabupaten_kota' => $this->formData['kabupaten_kota'],
                    'provinsi' => $this->formData['provinsi'],
                ];

                // Tambahkan password jika diisi
                if (! empty($this->formData['password'])) {
                    $data['password'] = $this->formData['password'];
                }

                // Tambahkan avatar jika ada
                if ($this->avatar) {
                    $avatarPath = $this->avatar->store('avatars/pelanggan', 'public');
                    $data['avatar_url'] = $avatarPath;
                }

                // Create pelanggan menggunakan PelangganHelper (sudah handle alamat regional otomatis)
                $pelanggan = PelangganHelper::createPelanggan(
                    $data,
                    $this->formData['tanggal_daftar']
                );
            });

            $this->success('Pelanggan berhasil ditambahkan!', position: 'toast-bottom');
            $this->redirect('/management/pelanggan', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                Log::warning('Duplicate entry detected when creating pelanggan', [
                    'kode_pelanggan' => $this->formData['kode_pelanggan'],
                    'error' => $e->getMessage(),
                ]);
                $this->refreshKodePelanggan();
                $this->warning('Kode pelanggan di-regenerate, silakan coba lagi', position: 'toast-bottom');

                return;
            }

            Log::error('Database error when creating pelanggan', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->formData,
            ]);
            $this->error('Gagal menyimpan pelanggan. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Failed to create pelanggan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->formData,
            ]);
            $this->error('Gagal menyimpan pelanggan. Silakan coba lagi.', position: 'toast-bottom');
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

    public function render(): mixed
    {
        return view('livewire.management.pelanggan.create', [
            'kecamatanOptions' => $this->getKecamatanOptions(),
            'kelurahanOptions' => $this->getKelurahanOptions(),
        ]);
    }
}
