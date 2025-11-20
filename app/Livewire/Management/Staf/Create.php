<?php

namespace App\Livewire\Management\Staf;

use Exception;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;

#[Title('Tambah Staf')]
#[Layout('layouts.management.app')]
class Create extends Component
{
    use Toast, WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    #[Rule('nullable|image|max:2048')]
    public $avatar = null;

    public bool $super_admin = false;

    public function roleOptions(): array
    {
        return [
            ['id' => 0, 'name' => 'Staf'],
            ['id' => 1, 'name' => 'Super Admin'],
        ];
    }

    public function save()
    {
        // Validasi
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'avatar' => 'nullable|image|max:2048',
            'super_admin' => 'boolean',
        ], [
            'name.required' => 'Nama wajib diisi',
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'file.image' => 'File harus berupa gambar',
            'file.max' => 'Ukuran file maksimal 2MB',
            'super_admin.boolean' => 'Role harus berupa pilihan yang valid',
        ]);

        try {
            // Upload avatar jika ada
            $avatarPath = null;
            if ($this->avatar) {
                $avatarPath = $this->avatar->store('avatars', 'public');
            }

            // Buat user baru
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'avatar_url' => $avatarPath,
                'super_admin' => $this->super_admin,
                'email_verified_at' => now(), // Otomatis verified saat create
            ]);

            $this->success('Staf berhasil ditambahkan!', redirectTo: route('staf.index'), position: 'toast-bottom');
        } catch (Exception $e) {
            $this->error('Gagal menambahkan staf: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.management.staf.create', [
            'roleOptions' => $this->roleOptions(),
        ]);
    }
}
