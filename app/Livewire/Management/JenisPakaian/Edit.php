<?php

declare(strict_types=1);

namespace App\Livewire\Management\JenisPakaian;

use App\Helper\Database\JenisPakaianHelper;
use App\Models\JenisPakaian;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Edit Jenis Pakaian')]
#[Layout('layouts.management.app')]
class Edit extends Component
{
    use Toast;

    public int $jenisPakaianId;

    public array $formData = [
        'kode_jenis' => '',
        'nama_jenis' => '',
        'keterangan' => '',
        'status' => 'Aktif',
    ];

    // Metadata fields
    public string $penangananKhusus = '';

    public string $icon = '';

    #[On('iconSelected')]
    public function iconSelected(?string $icon): void
    {
        $this->icon = $icon ?? '';
    }

    public function mount(int $id): void
    {
        $this->jenisPakaianId = $id;
        $this->loadJenisPakaian();
    }

    protected function loadJenisPakaian(): mixed
    {
        try {
            $jenisPakaian = JenisPakaian::findOrFail($this->jenisPakaianId);

            $this->formData = [
                'kode_jenis' => $jenisPakaian->kode_jenis,
                'nama_jenis' => $jenisPakaian->nama_jenis,
                'keterangan' => $jenisPakaian->keterangan ?? '',
                'status' => $jenisPakaian->status,
            ];

            // Load metadata using JenisPakaianHelper
            $this->penangananKhusus = JenisPakaianHelper::getPenangananKhusus($jenisPakaian) ?? '';
            $this->icon = JenisPakaianHelper::getIcon($jenisPakaian) ?? '';

            return null;
        } catch (Exception $e) {
            Log::error('Error loading jenis pakaian for edit', [
                'jenis_pakaian_id' => $this->jenisPakaianId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Jenis Pakaian tidak ditemukan', position: 'toast-bottom');

            return $this->redirect('/management/jenis-pakaian', navigate: true);
        }
    }

    public function save(): mixed
    {
        $this->validate([
            'formData.nama_jenis' => 'required|string|max:255',
            'formData.keterangan' => 'nullable|string',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
            'penangananKhusus' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
        ]);

        try {
            DB::transaction(function (): void {
                $jenisPakaian = JenisPakaian::findOrFail($this->jenisPakaianId);

                // Update basic fields
                $jenisPakaian->update($this->formData);

                // Update metadata using JenisPakaianHelper
                $metadata = $jenisPakaian->metadata ?? [];

                // Handle penanganan khusus
                if (! empty($this->penangananKhusus)) {
                    JenisPakaianHelper::setPenangananKhusus($jenisPakaian, $this->penangananKhusus);
                } else {
                    unset($metadata[JenisPakaianHelper::META_PENANGANAN_KHUSUS]);
                    $jenisPakaian->metadata = $metadata;
                }

                // Handle icon
                if (! empty($this->icon)) {
                    JenisPakaianHelper::setIcon($jenisPakaian, $this->icon);
                } else {
                    $metadata = $jenisPakaian->metadata ?? [];
                    unset($metadata[JenisPakaianHelper::META_ICON]);
                    $jenisPakaian->metadata = $metadata;
                }

                $jenisPakaian->save();

                Log::info('Jenis pakaian updated successfully', [
                    'id' => $jenisPakaian->id,
                    'kode_jenis' => $jenisPakaian->kode_jenis,
                    'nama_jenis' => $jenisPakaian->nama_jenis,
                    'has_metadata' => ! empty($this->penangananKhusus) || ! empty($this->icon),
                ]);
            });

            $this->success('Jenis Pakaian berhasil diupdate!', position: 'toast-bottom');

            return $this->redirect('/management/jenis-pakaian', navigate: true);
        } catch (QueryException $e) {
            Log::error('Database error updating jenis pakaian', [
                'id' => $this->jenisPakaianId,
                'error' => $e->getMessage(),
                'error_code' => $e->errorInfo[1] ?? null,
                'form_data' => $this->formData,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Gagal menyimpan jenis pakaian. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('General error updating jenis pakaian', [
                'id' => $this->jenisPakaianId,
                'error' => $e->getMessage(),
                'form_data' => $this->formData,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Terjadi kesalahan. Silakan coba lagi.', position: 'toast-bottom');
        }

        return null;
    }

    public function render(): mixed
    {
        return view('livewire.management.jenis-pakaian.edit');
    }
}
