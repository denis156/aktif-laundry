<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pelanggan;

use App\Helper\Database\PelangganHelper;
use App\Helper\PhoneNumber;
use App\Helper\RegionalLocation;
use App\Models\Pelanggan;
use Exception;
use Illuminate\Database\QueryException;
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
        'alamat' => '',
        'password' => '',
        'password_confirmation' => '',
        'tanggal_daftar' => '',
        'status' => 'Aktif',
        'total_transaksi' => 0,
        'kode_referral_dipakai' => '',
        'direferensikan_oleh' => null,
    ];

    public $avatar;

    public ?string $currentAvatarUrl = null;

    public function mount($id): void
    {
        $this->pelangganId = (int) $id;
        $this->loadPelanggan();
    }

    protected function loadPelanggan(): void
    {
        try {
            $pelanggan = Pelanggan::findOrFail($this->pelangganId);

            $this->formData = [
                'kode_pelanggan' => $pelanggan->kode_pelanggan,
                'nama' => $pelanggan->nama,
                'no_hp' => PhoneNumber::formatLocal($pelanggan->no_hp) ?? $pelanggan->no_hp,
                'email' => $pelanggan->email ?? '',
                'detail_alamat' => $pelanggan->detail_alamat ?? '',
                'kelurahan' => $pelanggan->kelurahan ?? '',
                'kecamatan' => $pelanggan->kecamatan ?? '',
                'kabupaten_kota' => $pelanggan->kabupaten_kota ?? RegionalLocation::getRegencyName(),
                'provinsi' => $pelanggan->provinsi ?? RegionalLocation::getProvinceName(),
                'alamat' => $pelanggan->alamat ?? '',
                'tanggal_daftar' => $pelanggan->tanggal_daftar?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'),
                'status' => $pelanggan->status,
                'total_transaksi' => $pelanggan->total_transaksi ?? 0,
                'kode_referral_dipakai' => $pelanggan->kode_referral_dipakai ?? '',
                'direferensikan_oleh' => $pelanggan->direferensikan_oleh,
            ];

            // Load current avatar URL
            $this->currentAvatarUrl = $pelanggan->avatar_url;
        } catch (Exception $e) {
            Log::error('Failed to load pelanggan for edit', [
                'pelanggan_id' => $this->pelangganId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Pelanggan tidak ditemukan', position: 'toast-bottom');
            $this->redirect('/management/pelanggan', navigate: true);
        }
    }

    public function save(): void
    {
        $rules = [
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
            $pelanggan = Pelanggan::findOrFail($this->pelangganId);

            // Normalize phone number menggunakan PhoneNumber helper
            $normalizedPhone = PhoneNumber::normalize($this->formData['no_hp']);
            if (! $normalizedPhone) {
                $this->error('Format nomor HP tidak valid. Gunakan format: +62, 62, 08, atau 8', position: 'toast-bottom');

                return;
            }

            // Prepare data untuk update pelanggan
            $data = [
                'nama' => $this->formData['nama'],
                'no_hp' => $normalizedPhone,
                'email' => $this->formData['email'],
                'tanggal_daftar' => $this->formData['tanggal_daftar'],
                'status' => $this->formData['status'],
            ];

            // Update password jika diisi
            if (! empty($this->formData['password'])) {
                $data['password'] = Hash::make($this->formData['password']);
            }

            $pelanggan->update($data);

            // Set alamat regional menggunakan PelangganHelper
            PelangganHelper::setAlamatRegional(
                $pelanggan,
                $this->formData['detail_alamat'],
                $this->formData['kelurahan'],
                $this->formData['kecamatan'],
                $this->formData['kabupaten_kota'],
                $this->formData['provinsi']
            );

            // Upload avatar baru jika ada
            if ($this->avatar) {
                $avatarPath = $this->avatar->store('avatars/pelanggan', 'public');
                $pelanggan->avatar_url = $avatarPath;
            }

            // Save changes
            $pelanggan->save();

            $this->success('Pelanggan berhasil diupdate!', position: 'toast-bottom');
            $this->redirect('/management/pelanggan', navigate: true);
        } catch (QueryException $e) {
            Log::error('Database error when updating pelanggan', [
                'pelanggan_id' => $this->pelangganId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->formData,
            ]);
            $this->error('Gagal menyimpan pelanggan. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Failed to update pelanggan', [
                'pelanggan_id' => $this->pelangganId,
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
        return view('livewire.management.pelanggan.edit', [
            'kecamatanOptions' => $this->getKecamatanOptions(),
            'kelurahanOptions' => $this->getKelurahanOptions(),
        ]);
    }
}
