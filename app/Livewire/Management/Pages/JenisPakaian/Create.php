<?php

declare(strict_types=1);

namespace App\Livewire\Management\Pages\JenisPakaian;

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

#[Title('Tambah Jenis Pakaian')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast;

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

    public function mount(): void
    {
        $this->refreshKodeJenisPakaian();
    }

    public function refreshKodeJenisPakaian(): void
    {
        try {
            $this->kode_jenis = JenisPakaianHelper::generateKodeJenis();
        } catch (Exception $e) {
            Log::error('Error refreshing kode jenis pakaian', [
                'error' => $e->getMessage(),
            ]);
            $this->kode_jenis = 'JNS'.now()->format('His');
        }
    }

    public function save(): mixed
    {
        $validationRules = JenisPakaianHelper::validationRules();
        $this->validate($validationRules);

        try {
            DB::transaction(function (): void {
                if (JenisPakaian::where('kode_jenis', $this->kode_jenis)->exists()) {
                    $this->refreshKodeJenisPakaian();
                }

                $jenisPakaian = JenisPakaian::create([
                    'kode_jenis' => $this->kode_jenis,
                    'nama_jenis' => $this->nama_jenis,
                    'keterangan' => $this->keterangan,
                    'status' => $this->status,
                ]);

                if (! empty($this->penanganan_khusus)) {
                    JenisPakaianHelper::setPenangananKhusus($jenisPakaian, $this->penanganan_khusus);
                }

                if (! empty($this->icon)) {
                    JenisPakaianHelper::setIcon($jenisPakaian, $this->icon);
                }

                if (! empty($this->penanganan_khusus) || ! empty($this->icon)) {
                    $jenisPakaian->save();
                }

                Log::info('Jenis pakaian created', [
                    'kode_jenis' => $this->kode_jenis,
                    'nama_jenis' => $this->nama_jenis,
                ]);
            });

            $this->success('Jenis Pakaian berhasil ditambahkan!', position: 'toast-bottom');

            return $this->redirect('/management/jenis-pakaian', navigate: true);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                $this->refreshKodeJenisPakaian();
                $this->warning('Kode jenis pakaian sudah ada, silakan coba lagi', position: 'toast-bottom');

                return null;
            }

            Log::error('Database error saving jenis pakaian', [
                'error' => $e->getMessage(),
                'kode_jenis' => $this->kode_jenis,
            ]);

            $this->error('Gagal menyimpan jenis pakaian. Silakan coba lagi.', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Error saving jenis pakaian', [
                'error' => $e->getMessage(),
                'kode_jenis' => $this->kode_jenis,
            ]);

            $this->error('Terjadi kesalahan. Silakan coba lagi.', position: 'toast-bottom');
        }

        return null;
    }

    public function render(): mixed
    {
        return view('livewire.management.pages.jenis-pakaian.create');
    }
}
