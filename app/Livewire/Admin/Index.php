<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

#[Title('Daftar User')]
class Index extends Component
{
    use Toast, WithPagination;

    public string $search = '';
    public bool $deleteModal = false;
    public ?int $deleteId = null;
    public string $deleteName = '';

    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public int $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id, $name)
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->deleteModal = true;
    }

    public function delete()
    {
        if ($this->deleteId) {
            try {
                $user = User::findOrFail($this->deleteId);

                // Prevent deleting yourself
                if ($user->id === Auth::id()) {
                    $this->error('Tidak dapat menghapus akun Anda sendiri!', position: 'toast-bottom');
                    return;
                }

                $user->delete();
                $this->success('User berhasil dihapus!', position: 'toast-bottom');
            } catch (\Exception $e) {
                $this->error('Gagal menghapus user: ' . $e->getMessage(), position: 'toast-bottom');
            }
        }

        $this->deleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Nama', 'class' => 'w-1/3'],
            ['key' => 'email', 'label' => 'Email', 'class' => 'w-1/3'],
            ['key' => 'created_at', 'label' => 'Dibuat', 'class' => 'w-1/4']
        ];
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);

        return view('livewire.admin.index', [
            'users' => $users,
            'headers' => $this->headers(),
        ]);
    }
}
