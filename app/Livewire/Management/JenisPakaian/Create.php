<?php

declare(strict_types=1);

namespace App\Livewire\Management\JenisPakaian;

use App\Helper\Database\JenisPakaianHelper;
use App\Helper\Database\PengaturanHelper;
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

#[Title('Tambah Jenis Pakaian')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;

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

    public function mount(): void
    {
        $this->refreshKodeJenisPakaian();
    }

    public function refreshKodeJenisPakaian(): void
    {
        $this->formData['kode_jenis'] = $this->generateKode();
    }

    protected function generateKode(): string
    {
        try {
            // Get prefix from Setting, default 'JNS'
            $prefix = PengaturanHelper::getValue('format_id_jenis_pakaian', 'JNS');
            $prefixLength = strlen($prefix);

            // Get latest kode from database, including soft-deleted records to handle gaps
            $lastJenisPakaian = JenisPakaian::withTrashed()->orderBy('kode_jenis', 'desc')->first();

            if (! $lastJenisPakaian) {
                return $prefix.'001';
            }

            // Extract number part after prefix
            $lastNumber = (int) substr($lastJenisPakaian->kode_jenis, $prefixLength);

            // Check if there are any gaps in the numbering by finding the next available number
            $nextNumber = $lastNumber + 1;

            // Verify if this number is already used (in case of deletions)
            while (JenisPakaian::where('kode_jenis', $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT))->exists()) {
                $nextNumber++;
            }

            return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            Log::error('Error generating kode jenis pakaian', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Fallback to simple timestamp-based code
            return 'JNS'.now()->format('His');
        }
    }

    public function save(): mixed
    {
        $this->validate([
            'formData.kode_jenis' => 'required|unique:jenis_pakaian,kode_jenis',
            'formData.nama_jenis' => 'required|string|max:255',
            'formData.keterangan' => 'nullable|string',
            'formData.status' => 'required|in:Aktif,Tidak Aktif',
            'penangananKhusus' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
        ]);

        try {
            // Gunakan database transaction untuk mencegah race condition
            DB::transaction(function (): void {
                // Cek ulang apakah kode jenis pakaian sudah ada, jika ya generate ulang
                if (JenisPakaian::where('kode_jenis', $this->formData['kode_jenis'])->exists()) {
                    $this->refreshKodeJenisPakaian();

                    Log::warning('Kode jenis pakaian sudah ada, regenerate kode', [
                        'old_kode' => $this->formData['kode_jenis'],
                        'new_kode' => $this->formData['kode_jenis'],
                    ]);
                }

                // Create jenis pakaian
                $jenisPakaian = JenisPakaian::create($this->formData);

                // Set metadata using JenisPakaianHelper
                if (! empty($this->penangananKhusus)) {
                    JenisPakaianHelper::setPenangananKhusus($jenisPakaian, $this->penangananKhusus);
                }

                if (! empty($this->icon)) {
                    JenisPakaianHelper::setIcon($jenisPakaian, $this->icon);
                }

                // Save metadata if any
                if (! empty($this->penangananKhusus) || ! empty($this->icon)) {
                    $jenisPakaian->save();
                }

                Log::info('Jenis pakaian created successfully', [
                    'kode_jenis' => $jenisPakaian->kode_jenis,
                    'nama_jenis' => $jenisPakaian->nama_jenis,
                    'has_metadata' => ! empty($this->penangananKhusus) || ! empty($this->icon),
                ]);
            });

            $this->success('Jenis Pakaian berhasil ditambahkan!', position: 'toast-bottom');

            return $this->redirect('/management/jenis-pakaian', navigate: true);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                $this->refreshKodeJenisPakaian();

                Log::warning('Duplicate kode jenis pakaian, regenerating', [
                    'error_code' => $e->errorInfo[1],
                    'new_kode' => $this->formData['kode_jenis'],
                ]);

                $this->success('Kode jenis pakaian di-regenerate, silakan coba lagi', position: 'toast-bottom');

                return null;
            }

            Log::error('Database error saving jenis pakaian', [
                'error' => $e->getMessage(),
                'error_code' => $e->errorInfo[1] ?? null,
                'form_data' => $this->formData,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Gagal menyimpan jenis pakaian. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('General error saving jenis pakaian', [
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
        return view('livewire.management.jenis-pakaian.create');
    }
}
