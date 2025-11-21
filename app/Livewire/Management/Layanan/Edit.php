<?php

declare(strict_types=1);

namespace App\Livewire\Management\Layanan;

use App\Helper\Database\LayananHelper;
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
    ];

    // Metadata fields
    public array $include = [];

    public array $exclude = [];

    public ?int $minOrder = null;

    public ?int $maxOrder = null;

    public int $popular = 0;

    public string $icon = '';

    public string $deskripsiDetail = '';

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
        $this->include = $data;
    }

    #[On('excludeUpdated')]
    public function excludeUpdated(array $data): void
    {
        $this->exclude = $data;
    }

    #[On('iconSelected')]
    public function iconSelected(?string $icon): void
    {
        $this->icon = $icon ?? '';
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
            ];

            // Load metadata
            $this->loadMetadata($layanan);
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

    /**
     * Load metadata dari layanan menggunakan LayananHelper
     */
    protected function loadMetadata(Layanan $layanan): void
    {
        try {
            $this->include = LayananHelper::getInclude($layanan);
            $this->exclude = LayananHelper::getExclude($layanan);
            $this->minOrder = LayananHelper::getMinOrder($layanan);
            $this->maxOrder = LayananHelper::getMaxOrder($layanan);
            $this->popular = LayananHelper::isPopular($layanan) ? 1 : 0;
            $this->icon = LayananHelper::getIcon($layanan) ?? '';
            $this->deskripsiDetail = LayananHelper::getMetadata($layanan, LayananHelper::META_DESKRIPSI_DETAIL, '') ?? '';
        } catch (Exception $e) {
            Log::warning('Failed to load layanan metadata, using defaults', [
                'layanan_id' => $layanan->id,
                'error' => $e->getMessage(),
            ]);
            // Continue with empty metadata if fails
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
            // Metadata validation
            'include' => 'nullable|array',
            'exclude' => 'nullable|array',
            'minOrder' => 'nullable|integer|min:1',
            'maxOrder' => 'nullable|integer|min:1',
            'popular' => 'nullable|boolean',
            'icon' => 'nullable|string|max:100',
            'deskripsiDetail' => 'nullable|string',
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
        if ($this->minOrder && $this->maxOrder && $this->maxOrder <= $this->minOrder) {
            $this->error('Max order harus lebih besar dari min order', position: 'toast-bottom');

            return;
        }

        $this->validate($rules);

        try {
            // Prepare data before saving
            $saveData = $this->formData;

            // Set default values for fields based on tipe layanan
            if ($saveData['tipe_layanan'] === 'per_kg') {
                $saveData['harga_per_satuan'] = null;
                $saveData['satuan'] = 'kg';
            } else {
                $saveData['harga_per_kg'] = 0;
            }

            DB::transaction(function () use ($saveData): void {
                $layanan = Layanan::findOrFail($this->layananId);
                $layanan->update($saveData);

                // Update metadata using LayananHelper
                $this->setMetadata($layanan);

                // Save metadata
                $layanan->save();
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

    /**
     * Set metadata untuk layanan menggunakan LayananHelper
     */
    protected function setMetadata(Layanan $layanan): void
    {
        try {
            // Set metadata using helper methods
            if (! empty($this->include)) {
                LayananHelper::setInclude($layanan, $this->include);
            } else {
                LayananHelper::setInclude($layanan, []);
            }

            if (! empty($this->exclude)) {
                LayananHelper::setExclude($layanan, $this->exclude);
            } else {
                LayananHelper::setExclude($layanan, []);
            }

            if ($this->minOrder !== null) {
                LayananHelper::setMinOrder($layanan, $this->minOrder);
            }

            if ($this->maxOrder !== null) {
                LayananHelper::setMaxOrder($layanan, $this->maxOrder);
            }

            LayananHelper::setPopular($layanan, (bool) $this->popular);

            if (! empty($this->icon)) {
                LayananHelper::setIcon($layanan, $this->icon);
            }

            if (! empty($this->deskripsiDetail)) {
                LayananHelper::setMetadata($layanan, LayananHelper::META_DESKRIPSI_DETAIL, $this->deskripsiDetail);
            }
        } catch (Exception $e) {
            Log::error('Failed to set layanan metadata', [
                'layanan_id' => $layanan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.layanan.edit');
    }
}
