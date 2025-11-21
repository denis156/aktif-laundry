<?php

declare(strict_types=1);

namespace App\Livewire\Management\Layanan;

use App\Helper\Database\LayananHelper;
use App\Helper\Database\PengaturanHelper;
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

#[Title('Tambah Layanan')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;

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

    public function mount(): void
    {
        $this->refreshKodeLayanan();
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

    public function refreshKodeLayanan(): void
    {
        $this->formData['kode_layanan'] = $this->generateKode();
    }

    protected function generateKode(): string
    {
        // Get prefix from Setting, default 'LYN'
        $prefix = PengaturanHelper::getValue('format_id_layanan', 'LYN');
        $prefixLength = strlen($prefix);

        // Get latest kode from database, including soft-deleted records to handle gaps
        $lastLayanan = Layanan::withTrashed()->orderBy('kode_layanan', 'desc')->first();

        if (! $lastLayanan) {
            return $prefix.'001';
        }

        // Extract number part after prefix
        $lastNumber = (int) substr($lastLayanan->kode_layanan, $prefixLength);

        // Check if there are any gaps in the numbering by finding the next available number
        $nextNumber = $lastNumber + 1;

        // Verify if this number is already used (in case of deletions)
        while (Layanan::where('kode_layanan', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
            $nextNumber++;
        }

        return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function save(): void
    {
        // Build validation rules based on tipe layanan
        $rules = [
            'formData.kode_layanan' => 'required|unique:layanan,kode_layanan',
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

            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function () use ($saveData): void {
                // Cek ulang apakah kode layanan sudah ada, jika ya generate ulang
                if (Layanan::where('kode_layanan', $saveData['kode_layanan'])->exists()) {
                    $this->refreshKodeLayanan();
                    $saveData['kode_layanan'] = $this->formData['kode_layanan'];
                }

                // Create layanan
                $layanan = Layanan::create($saveData);

                // Set metadata using LayananHelper
                $this->setMetadata($layanan);

                // Save metadata
                $layanan->save();
            });

            $this->success('Layanan berhasil ditambahkan!', position: 'toast-bottom');
            $this->redirect('/management/layanan', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                $this->refreshKodeLayanan();
                Log::warning('Duplicate kode layanan detected, regenerating', [
                    'kode_layanan' => $this->formData['kode_layanan'],
                    'error_code' => $e->errorInfo[1],
                ]);
                $this->success('Kode layanan di-regenerate, silakan coba lagi', position: 'toast-bottom');

                return;
            }

            Log::error('Database error saving layanan', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'formData' => $this->formData,
            ]);
            $this->error('Gagal menyimpan layanan. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('General error saving layanan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'formData' => $this->formData,
            ]);
            $this->error('Terjadi kesalahan. Silakan coba lagi.', position: 'toast-bottom');
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
            }

            if (! empty($this->exclude)) {
                LayananHelper::setExclude($layanan, $this->exclude);
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
        return view('livewire.management.layanan.create');
    }
}
