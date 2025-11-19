<?php

namespace App\Livewire\Admin;

use Exception;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

#[Title('Profil Saya')]

#[Layout('layouts.admin.app')]
class Profile extends Component
{
    use Toast, WithFileUploads;

    // Profile Information
    public string $name = '';
    public string $email = '';
    public string $currentAvatarUrl = '';

    #[Rule('nullable|image|max:2048')]
    public $avatar = null;

    // Change Password
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Original data untuk compare
    public string $originalName = '';
    public string $originalEmail = '';

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->currentAvatarUrl = $user->avatar_url ?? '';

        // Simpan data original
        $this->originalName = $user->name;
        $this->originalEmail = $user->email;
    }

    public function hasChanges()
    {
        // Cek perubahan profil
        $profileChanged = $this->name !== $this->originalName ||
                         $this->email !== $this->originalEmail ||
                         $this->avatar !== null;

        // Cek perubahan password
        $passwordChanged = !empty($this->current_password) ||
                          !empty($this->password) ||
                          !empty($this->password_confirmation);

        return $profileChanged || $passwordChanged;
    }

    public function save()
    {
        // Validasi profil
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'avatar' => 'nullable|image|max:2048',
        ];

        $messages = [
            'name.required' => 'Nama wajib diisi',
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'file.image' => 'File harus berupa gambar',
            'file.max' => 'Ukuran file maksimal 2MB',
        ];

        // Validasi password jika diisi
        if (!empty($this->current_password) || !empty($this->password) || !empty($this->password_confirmation)) {
            $rules['current_password'] = 'required';
            $rules['password'] = 'required|min:8|confirmed';
            $messages['current_password.required'] = 'Password saat ini wajib diisi';
            $messages['password.required'] = 'Password baru wajib diisi';
            $messages['password.min'] = 'Password baru minimal 8 karakter';
            $messages['password.confirmed'] = 'Konfirmasi password tidak cocok';
        }

        $this->validate($rules, $messages);

        try {
            $user = User::findOrFail(Auth::id());

            // Upload avatar baru jika ada
            if ($this->avatar) {
                // Observer akan otomatis hapus avatar lama
                $avatarPath = $this->avatar->store('avatars', 'public');
            } else {
                $avatarPath = $user->avatar_url;
            }

            // Data yang akan diupdate
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'avatar_url' => $avatarPath,
            ];

            // Jika password diisi, cek dan update password
            if (!empty($this->current_password)) {
                // Cek password saat ini
                if (!Hash::check($this->current_password, $user->password)) {
                    $this->error('Password saat ini tidak sesuai!', position: 'toast-bottom');
                    return;
                }

                // Update password
                $data['password'] = Hash::make($this->password);
            }

            // Update user
            $user->update($data);

            // Update current avatar URL untuk preview
            $this->currentAvatarUrl = $avatarPath ?? '';

            // Update original data
            $this->originalName = $this->name;
            $this->originalEmail = $this->email;

            // Reset form password
            $this->reset(['current_password', 'password', 'password_confirmation', 'avatar']);

            $this->success('Profil berhasil diperbarui!', position: 'toast-bottom');
        } catch (Exception $e) {
            $this->error('Gagal memperbarui profil: ' . $e->getMessage(), position: 'toast-bottom');
        }
    }

    public function render()
    {
        return view('livewire.admin.profile', [
            'hasChanges' => $this->hasChanges(),
        ]);
    }
}
