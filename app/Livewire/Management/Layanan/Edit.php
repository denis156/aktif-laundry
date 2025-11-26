<?php

declare(strict_types=1);

namespace App\Livewire\Management\Layanan;

use App\Models\Layanan;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Edit Layanan')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;

    public int $layananId;

    public array $formData = [
        'kode_layanan' => '',
        'nama_layanan' => '',
        'tipe_layanan' => 'per_kg',
        'harga_per_kg' => '',
        'harga_per_satuan' => '',
        'satuan' => '',
        'durasi_jam' => '',
        'deskripsi' => '',
        'status' => 'Aktif',
        'include' => [],
        'exclude' => [],
        'min_order' => null,
        'max_order' => null,
        'is_popular' => false,
        'icon' => '',
    ];

    // Options for group components
    public array $popularOptions = [
        ['id' => 0, 'name' => 'Tidak'],
        ['id' => 1, 'name' => 'Ya'],
    ];

    public array $tipeLayananOptions = [
        ['id' => 'per_kg', 'name' => 'Per Kilogram (Kg)'],
        ['id' => 'per_satuan', 'name' => 'Per Satuan'],
    ];

    public function mount(int $id): void
    {
        $this->layananId = $id;
        $this->loadLayanan();
    }

    #[On('includeUpdated')]
    public function includeUpdated(array $data): void
    {
        $this->formData['include'] = $data;
    }

    #[On('excludeUpdated')]
    public function excludeUpdated(array $data): void
    {
        $this->formData['exclude'] = $data;
    }

    #[On('iconSelected')]
    public function iconSelected(?string $icon): void
    {
        $this->formData['icon'] = $icon ?? '';
    }

    protected function loadLayanan(): void
    {
        try {
            $layanan = Layanan::findOrFail($this->layananId);

            $this->formData = [
                'kode_layanan' => $layanan->kode_layanan,
                'nama_layanan' => $layanan->nama_layanan,
                'tipe_layanan' => $layanan->tipe_layanan,
                'harga_per_kg' => $layanan->harga_per_kg,
                'harga_per_satuan' => $layanan->harga_per_satuan,
                'satuan' => $layanan->satuan,
                'durasi_jam' => $layanan->durasi_jam,
                'deskripsi' => $layanan->deskripsi,
                'status' => $layanan->status,
                'include' => $layanan->include ?? [],
                'exclude' => $layanan->exclude ?? [],
                'min_order' => $layanan->min_order,
                'max_order' => $layanan->max_order,
                'is_popular' => $layanan->is_popular ?? false,
                'icon' => $layanan->icon ?? '',
            ];
        } catch (Exception $e) {
            Log::error('Failed to load layanan', [
                'layanan_id' => $this->layananId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Layanan tidak ditemukan', position: 'toast-bottom');
            $this->redirect('/management/layanan', navigate: true);
        }
    }

    public function save(): void
    {
        // Build validation rules based on tipe layanan
        $rules = [
            'formData.nama_layanan' => 'required|string|max:255',
            'formData.tipe_layanan' => 'required|in:per_kg,per_satuan',
            'formData.durasi_jam' => 'required|integer|min:1',
            'formData.deskripsi' => 'nullable|string',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
            'formData.include' => 'nullable|array',
            'formData.exclude' => 'nullable|array',
            'formData.min_order' => 'nullable|integer|min:1',
            'formData.max_order' => 'nullable|integer|min:1',
            'formData.is_popular' => 'nullable|boolean',
            'formData.icon' => 'nullable|string|max:100',
        ];

        // Add conditional validation based on tipe layanan
        if ($this->formData['tipe_layanan'] === 'per_kg') {
            $rules['formData.harga_per_kg'] = 'required|numeric|min:0';
            $rules['formData.harga_per_satuan'] = 'nullable|numeric|min:0';
            $rules['formData.satuan'] = 'nullable|string|max:10';
        } else {
            $rules['formData.harga_per_kg'] = 'nullable|numeric|min:0';
            $rules['formData.harga_per_satuan'] = 'required|numeric|min:0';
            $rules['formData.satuan'] = 'required|string|max:10';
        }

        // Validate max_order > min_order if both set
        if ($this->formData['min_order'] && $this->formData['max_order'] && $this->formData['max_order'] <= $this->formData['min_order']) {
            $this->error('Max order harus lebih besar dari min order', position: 'toast-bottom');

            return;
        }

        $this->validate($rules);

        try {
            // Set default values based on tipe layanan
            if ($this->formData['tipe_layanan'] === 'per_kg') {
                $this->formData['harga_per_satuan'] = null;
                $this->formData['satuan'] = 'kg';
            } else {
                $this->formData['harga_per_kg'] = 0;
            }

            // Convert is_popular to boolean
            $this->formData['is_popular'] = (bool) $this->formData['is_popular'];

            DB::transaction(function (): void {
                $layanan = Layanan::findOrFail($this->layananId);
                $layanan->update($this->formData);
            });

            $this->success('Layanan berhasil diupdate!', position: 'toast-bottom');
            $this->redirect('/management/layanan', navigate: true);
        } catch (QueryException $e) {
            Log::error('Database error updating layanan', [
                'layanan_id' => $this->layananId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'formData' => $this->formData,
            ]);
            $this->error('Gagal memperbarui layanan. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('General error updating layanan', [
                'layanan_id' => $this->layananId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
            ]);
            $this->error('Gagal memperbarui layanan. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.layanan.edit');
    }
}
