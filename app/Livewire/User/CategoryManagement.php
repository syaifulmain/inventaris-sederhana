<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Services\CategoryService;
use App\Models\Category;

#[Title('Manajemen Kategori')]
class CategoryManagement extends Component
{
    use WithPagination;

    public $title = 'Manajemen Kategori';
    public $breadcrumb = 'Manajemen Kategori';

    #[Url(as: 'q')]
    public $search = '';

    public $perPage = 10;

    // Modal states
    public $showModal = false;
    public $modalMode = 'create';
    public $categoryId;

    // Form fields
    public $code = '';
    public $name = '';

    public function rules()
    {
        $rules = [
            'code' => 'required|string|max:20|unique:categories,code',
            'name' => 'required|string|max:255',
        ];

        if ($this->modalMode === 'edit') {
            $rules['code'] = 'required|string|max:20|unique:categories,code,' . $this->categoryId;
        }

        return $rules;
    }

    public function updatedSearch()
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
        $category = Category::findOrFail($id);

        $this->categoryId = $category->id;
        $this->code = $category->code;
        $this->name = $category->name;

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
        $this->categoryId = null;
        $this->code = '';
        $this->name = '';
        $this->resetValidation();
    }

    public function save(CategoryService $categoryService)
    {
        $this->validate();

        try {
            $data = [
                'code' => $this->code,
                'name' => $this->name,
            ];

            if ($this->modalMode === 'create') {
                $categoryService->create($data);
                session()->flash('message', 'Kategori berhasil dibuat!');
            } else {
                $categoryService->update($this->categoryId, $data);
                session()->flash('message', 'Kategori berhasil diupdate!');
            }

            $this->closeModal();

        } catch (\Exception $e) {
            $this->addError('save', 'Gagal menyimpan kategori: ' . $e->getMessage());
        }
    }

    public function delete($id, CategoryService $categoryService)
    {
        try {
            $categoryService->delete($id);
            session()->flash('message', 'Kategori berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    public function render(CategoryService $categoryService)
    {
        $filters = array_filter([
            'search' => $this->search,
        ], fn($value) => $value !== '' && $value !== null);

        $categories = $categoryService->getPaginated($filters, $this->perPage);

        return view('livewire.user.category-management', [
            'categories' => $categories,
        ]);
    }
}
