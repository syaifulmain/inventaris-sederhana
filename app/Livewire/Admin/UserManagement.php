<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Services\UserService;
use App\Models\User;

#[Title('Managemen Pengguna')]
class UserManagement extends Component
{
    use WithPagination;
    public $title = 'Manajemen Pengguna';
    public $breadcrumb = 'Manajemen Pengguna';

    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $roleFilter = '';

    public $perPage = 10;

    // Modal states
    public $showModal = false;
    public $modalMode = 'create';
    public $userId;

    // Form fields
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = 'user';
    public $is_active = true;

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
        ];

        if ($this->modalMode === 'create') {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|min:6|confirmed';
        } else {
            $rules['email'] = 'required|email|unique:users,email,' . $this->userId;
            $rules['password'] = 'nullable|min:6|confirmed';
        }

        return $rules;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $user = User::findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->is_active = $user->is_active;
        $this->password = '';
        $this->password_confirmation = '';

        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = 'user';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function save(UserService $userService)
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'is_active' => $this->is_active,
            ];

            if ($this->password) {
                $data['password'] = $this->password;
                $data['password_confirmation'] = $this->password_confirmation;
            }

            if ($this->modalMode === 'create') {
                $userService->createUser($data);
                session()->flash('message', 'User berhasil dibuat!');
            } else {
                $userService->updateUser($this->userId, $data);
                session()->flash('message', 'User berhasil diupdate!');
            }

            $this->closeModal();

        } catch (\Exception $e) {
            $this->addError('save', 'Gagal menyimpan user: ' . $e->getMessage());
        }
    }

    public function delete($id, UserService $userService)
    {
        if (auth()->id() == $id) {
            session()->flash('error', 'Anda tidak bisa menghapus akun sendiri!');
            return;
        }

        try {
            $userService->delete($id);
            session()->flash('message', 'User berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    public function render(UserService $userService)
    {
        $filters = array_filter([
            'search' => $this->search,
            'role' => $this->roleFilter,
        ], fn($value) => $value !== '' && $value !== null);

        $users = $userService->getPaginated($filters, $this->perPage);

        return view('livewire.admin.user-management', [
            'users' => $users,
        ]);
    }
}
