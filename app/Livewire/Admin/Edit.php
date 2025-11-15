<?php

namespace App\Livewire\Admin;

use Exception;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

#[Title('Edit User')]
class Edit extends Component
{
    use Toast, WithFileUploads;

    public int $userId;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $currentAvatarUrl = '';

    #[Rule('nullable|image|max:2048')]
    public $avatar = null;

    public bool $super_admin = false;

    public function roleOptions(): array
    {
        return [
            ['id' => 0, 'name' => 'User'],
            ['id' => 1, 'name' => 'Super Admin'],
        ];
    }

    public function mount($id)
    {
        $this->userId = $id;
        $user = User::findOrFail($id);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->currentAvatarUrl = $user->avatar_url ?? '';
        $this->super_admin = $user->super_admin;
    }

    public function save()
    {
        // Validasi
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'avatar' => 'nullable|image|max:2048',
            'super_admin' => 'boolean',
        ];

        $messages = [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'avatar.image' => 'File harus berupa gambar',
            'avatar.max' => 'Ukuran gambar maksimal 2MB',
        ];

        // Jika password diisi, tambahkan validasi password
        if (!empty($this->password)) {
            $rules['password'] = 'min:8|confirmed';
            $messages['password.min'] = 'Password minimal 8 karakter';
            $messages['password.confirmed'] = 'Konfirmasi password tidak cocok';
        }

        $this->validate($rules, $messages);

        try {
            $user = User::findOrFail($this->userId);

            // Cek jika mengedit diri sendiri
            if ($user->id === Auth::id() && $user->email !== $this->email) {
                $this->error('Anda tidak dapat mengubah email akun Anda sendiri!', position: 'toast-bottom');
                return;
            }

            // Upload avatar baru jika ada
            if ($this->avatar) {
                // Observer akan otomatis hapus avatar lama
                $avatarPath = $this->avatar->store('avatars', 'public');
            } else {
                $avatarPath = $user->avatar_url;
            }

            // Update user
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'avatar_url' => $avatarPath,
                'super_admin' => $this->super_admin,
            ];

            // Jika password diisi, update password
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            $user->update($data);

            $this->success('User berhasil diperbarui!', redirectTo: route('admin.index'), position: 'toast-bottom');
        } catch (Exception $e) {
            $this->error('Gagal memperbarui user: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.admin.edit', [
            'roleOptions' => $this->roleOptions(),
        ]);
    }
}
