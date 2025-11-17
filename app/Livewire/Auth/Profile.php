<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Services\AuthService;
use Livewire\Attributes\Title;

#[Title('Profile')]
class Profile extends Component
{
    public $title = 'Dashboard';
    public $breadcrumb = 'Dashboard';
    public $name;
    public $email;
    public $password;
    public $password_confirmation;

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'password' => 'nullable|min:6|confirmed',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama wajib diisi',
        'email.required' => 'Email wajib diisi',
        'email.email' => 'Format email tidak valid',
        'email.unique' => 'Email sudah digunakan',
        'password.min' => 'Password minimal 6 karakter',
        'password.confirmed' => 'Konfirmasi password tidak cocok',
    ];

    public function updateProfile(AuthService $authService)
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            if ($this->password) {
                $data['password'] = $this->password;
            }

            // Gunakan service yang sama dengan API
            $authService->updateProfile(auth()->id(), $data);

            // Reset password fields
            $this->password = '';
            $this->password_confirmation = '';

            // Flash success message
            session()->flash('message', 'Profil berhasil diupdate!');

            // Refresh data
            $this->mount();

        } catch (\Exception $e) {
            $this->addError('update', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.profile');
    }
}
