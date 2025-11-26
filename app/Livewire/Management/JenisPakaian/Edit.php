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

    public string $kode_jenis = '';

    public string $nama_jenis = '';

    public string $keterangan = '';

    public string $status = 'Aktif';

    public string $penanganan_khusus = '';

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

            $this->kode_jenis = $jenisPakaian->kode_jenis;
            $this->nama_jenis = $jenisPakaian->nama_jenis;
            $this->keterangan = $jenisPakaian->keterangan ?? '';
            $this->status = $jenisPakaian->status;
            $this->penanganan_khusus = JenisPakaianHelper::getPenangananKhusus($jenisPakaian) ?? '';
            $this->icon = JenisPakaianHelper::getIcon($jenisPakaian) ?? '';

            return null;
        } catch (Exception $e) {
            Log::error('Error loading jenis pakaian for edit', [
                'jenis_pakaian_id' => $this->jenisPakaianId,
                'error' => $e->getMessage(),
            ]);

            $this->error('Jenis Pakaian tidak ditemukan', position: 'toast-bottom');

            return $this->redirect('/management/jenis-pakaian', navigate: true);
        }
    }

    public function save(): mixed
    {
        $validationRules = JenisPakaianHelper::validationRules(isEdit: true);
        $this->validate($validationRules);

        try {
            DB::transaction(function (): void {
                $jenisPakaian = JenisPakaian::findOrFail($this->jenisPakaianId);

                $jenisPakaian->update([
                    'kode_jenis' => $this->kode_jenis,
                    'nama_jenis' => $this->nama_jenis,
                    'keterangan' => $this->keterangan,
                    'status' => $this->status,
                ]);

                JenisPakaianHelper::setPenangananKhusus($jenisPakaian, $this->penanganan_khusus);
                JenisPakaianHelper::setIcon($jenisPakaian, $this->icon);
                $jenisPakaian->save();

                Log::info('Jenis pakaian updated', [
                    'id' => $jenisPakaian->id,
                    'kode_jenis' => $jenisPakaian->kode_jenis,
                    'nama_jenis' => $jenisPakaian->nama_jenis,
                ]);
            });

            $this->success('Jenis Pakaian berhasil diupdate!', position: 'toast-bottom');

            return $this->redirect('/management/jenis-pakaian', navigate: true);
        } catch (QueryException $e) {
            Log::error('Database error updating jenis pakaian', [
                'id' => $this->jenisPakaianId,
                'error' => $e->getMessage(),
            ]);

            $this->error('Gagal menyimpan jenis pakaian. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Error updating jenis pakaian', [
                'id' => $this->jenisPakaianId,
                'error' => $e->getMessage(),
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
