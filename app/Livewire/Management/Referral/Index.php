<?php

declare(strict_types=1);

namespace App\Livewire\Management\Referral;

use App\Models\Referral;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Daftar Referral')]
#[Layout('layouts.management.app')]
class Index extends Component
{
    use Toast;
    use WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public bool $deleteModal = false;

    public int $deleteId = 0;

    public string $deleteName = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public string $statusFilter = '';

    public int $perPage = 10;

    public function clear(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->success('Filter berhasil dibersihkan.', position: 'toast-bottom');
    }

    public function confirmDelete(int $id, string $kode): void
    {
        $this->deleteId = $id;
        $this->deleteName = $kode;
        $this->deleteModal = true;
    }

    public function delete(): void
    {
        try {
            $referral = Referral::findOrFail($this->deleteId);

            // Check if referral is being used
            if ($referral->total_referral > 0) {
                Log::warning('Attempt to delete referral with existing usage', [
                    'referral_id' => $this->deleteId,
                    'kode_referral' => $this->deleteName,
                    'total_referral' => $referral->total_referral,
                ]);
                $this->error('Tidak dapat menghapus referral yang sudah digunakan', position: 'toast-bottom');

                return;
            }

            $referral->delete();

            Log::info('Referral deleted successfully', [
                'referral_id' => $this->deleteId,
                'kode_referral' => $this->deleteName,
            ]);

            $this->success("Referral {$this->deleteName} berhasil dihapus!", position: 'toast-bottom');
            $this->deleteModal = false;
            $this->reset(['deleteId', 'deleteName']);
        } catch (ModelNotFoundException $e) {
            Log::error('Referral not found for deletion', [
                'referral_id' => $this->deleteId,
                'error' => $e->getMessage(),
            ]);
            $this->error('Referral tidak ditemukan', position: 'toast-bottom');
        } catch (Exception $e) {
            Log::error('Failed to delete referral', [
                'referral_id' => $this->deleteId,
                'kode_referral' => $this->deleteName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Gagal menghapus referral. Silakan coba lagi.', position: 'toast-bottom');
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'kode_referral', 'label' => 'Kode', 'class' => 'w-32'],
            ['key' => 'pelanggan', 'label' => 'Pelanggan', 'class' => 'w-40', 'sortable' => false],
            ['key' => 'promo_reward', 'label' => 'Promo Reward', 'class' => 'w-56', 'sortable' => false],
            ['key' => 'statistik', 'label' => 'Statistik', 'class' => 'w-36', 'sortable' => false],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24', 'sortable' => false],
        ];
    }

    public function referral(): LengthAwarePaginator
    {
        return Referral::query()
            ->with(['pelanggan', 'promoReferrer', 'promoReferee'])
            ->when($this->search, function (Builder $query): void {
                $query->where(function (Builder $q): void {
                    $q->where('kode_referral', 'like', "%{$this->search}%")
                        ->orWhereHas('pelanggan', function (Builder $pq): void {
                            $pq->where('nama', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->statusFilter, function (Builder $query): void {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function render(): mixed
    {
        return view('livewire.management.referral.index', [
            'referral' => $this->referral(),
            'headers' => $this->headers(),
        ]);
    }
}
