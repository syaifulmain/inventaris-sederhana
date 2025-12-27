<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Services\ProductService;
use App\Services\CategoryService;
use App\Models\Product;

#[Title('Manajemen Produk')]
class ProductManagement extends Component
{
    use WithPagination;

    public $title = 'Manajemen Produk';
    public $breadcrumb = 'Manajemen Produk';

    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $categoryFilter = '';

    public $perPage = 10;

    // Modal states
    public $showModal = false;
    public $modalMode = 'create';
    public $productId;

    // Form fields
    public $code = '';
    public $name = '';
    public $category_id = '';

    public function rules()
    {
        $rules = [
            'code' => 'required|string|max:20|unique:products,code',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ];

        if ($this->modalMode === 'edit') {
            $rules['code'] = 'required|string|max:20|unique:products,code,' . $this->productId;
        }

        return $rules;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
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
        $product = Product::findOrFail($id);

        $this->productId = $product->id;
        $this->code = $product->code;
        $this->name = $product->name;
        $this->category_id = $product->category_id;

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
        $this->productId = null;
        $this->code = '';
        $this->name = '';
        $this->category_id = '';
        $this->resetValidation();
    }

    public function save(ProductService $productService)
    {
        $this->validate();

        try {
            $data = [
                'code' => $this->code,
                'name' => $this->name,
                'category_id' => $this->category_id,
            ];

            if ($this->modalMode === 'create') {
                $productService->createProduct($data);
                session()->flash('message', 'Produk berhasil dibuat!');
            } else {
                $productService->updateProduct($this->productId, $data);
                session()->flash('message', 'Produk berhasil diupdate!');
            }

            $this->closeModal();

        } catch (\Exception $e) {
            $this->addError('save', 'Gagal menyimpan produk: ' . $e->getMessage());
        }
    }

    public function delete($id, ProductService $productService)
    {
        try {
            $productService->delete($id);
            session()->flash('message', 'Produk berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function render(ProductService $productService, CategoryService $categoryService)
    {
        $filters = array_filter([
            'search' => $this->search,
            'category_id' => $this->categoryFilter,
        ], fn($value) => $value !== '' && $value !== null);

        $products = $productService->getPaginated($filters, $this->perPage);
        $categories = $categoryService->getAll();

        return view('livewire.user.product-management', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
