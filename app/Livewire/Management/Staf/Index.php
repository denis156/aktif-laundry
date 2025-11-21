<?php

declare(strict_types=1);

namespace App\Livewire\Management\Staf;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Daftar Staf')]
#[Layout('layouts.management.app')]
class Index extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public bool $deleteModal = false;

    public ?int $deleteId = null;

    public string $deleteName = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public int $perPage = 10;

    // Filters
    public string $roleFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clear(): void
    {
        $this->reset(['search', 'roleFilter']);
        $this->success('Filter berhasil dibersihkan.', position: 'toast-bottom');
    }

    public function confirmDelete($id, $name): void
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->deleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            try {
                $user = User::findOrFail($this->deleteId);

                // Prevent deleting yourself
                if ($user->id === Auth::id()) {
                    Log::warning('Staf Index: Attempted to delete own account', [
                        'user_id' => Auth::id(),
                    ]);
                    $this->error('Tidak dapat menghapus akun Anda sendiri!', position: 'toast-bottom');

                    return;
                }

                $user->delete();

                Log::info('Staf deleted successfully', [
                    'deleted_user_id' => $this->deleteId,
                    'deleted_by' => Auth::id(),
                ]);

                $this->success('Staf berhasil dihapus!', position: 'toast-bottom');
            } catch (Exception $e) {
                Log::error('Staf Index: Failed to delete user', [
                    'user_id' => $this->deleteId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error('Gagal menghapus staf. Silakan coba lagi.', position: 'toast-bottom');
            }
        }

        $this->deleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';
    }

    public function headers(): array
    {
        return [
            ['key' => 'avatar_url', 'label' => 'Avatar', 'class' => 'w-16', 'sortable' => false],
            ['key' => 'name', 'label' => 'Nama', 'sortable' => false],
            ['key' => 'no_hp', 'label' => 'No HP', 'sortable' => false],
            ['key' => 'alamat', 'label' => 'Alamat', 'sortable' => false],
            ['key' => 'jam_kerja', 'label' => 'Jam Kerja', 'class' => 'w-32', 'sortable' => false],
            ['key' => 'gaji', 'label' => 'Gaji', 'class' => 'w-32', 'sortable' => false],
            ['key' => 'super_admin', 'label' => 'Super Admin', 'class' => 'w-24', 'sortable' => false],
        ];
    }

    public function users(): mixed
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('no_hp', 'like', "%{$this->search}%")
                        ->orWhere('alamat', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter !== '', function ($query) {
                $isSuperAdmin = $this->roleFilter === '1';
                $query->where('super_admin', $isSuperAdmin);
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function render(): mixed
    {
        return view('livewire.management.staf.index', [
            'users' => $this->users(),
            'headers' => $this->headers(),
        ]);
    }
}
