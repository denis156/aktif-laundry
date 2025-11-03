<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

#[Title('Edit User')]
class Edit extends Component
{
    use Toast;

    public int $userId;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount($id)
    {
        $this->userId = $id;
        $user = User::findOrFail($id);

        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function save()
    {
        // Validasi
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
        ];

        $messages = [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
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

            // Update user
            $data = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            // Jika password diisi, update password
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            $user->update($data);

            $this->success('User berhasil diperbarui!', redirectTo: route('admin.index'), position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Gagal memperbarui user: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.admin.edit');
    }
}
