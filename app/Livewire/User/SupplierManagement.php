<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Services\SupplierService;
use App\Models\Supplier;

#[Title('Manajemen Supplier')]
class SupplierManagement extends Component
{
    use WithPagination;

    public $title = 'Manajemen Supplier';
    public $breadcrumb = 'Manajemen Supplier';

    #[Url(as: 'q')]
    public $search = '';

    public $perPage = 10;

    // Modal states
    public $showModal = false;
    public $modalMode = 'create';
    public $supplierId;

    // Form fields
    public $code = '';
    public $name = '';
    public $address = '';

    public function rules()
    {
        $rules = [
            'code' => 'required|string|max:20|unique:suppliers,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ];

        if ($this->modalMode === 'edit') {
            $rules['code'] = 'required|string|max:20|unique:suppliers,code,' . $this->supplierId;
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
        $supplier = Supplier::findOrFail($id);

        $this->supplierId = $supplier->id;
        $this->code = $supplier->code;
        $this->name = $supplier->name;
        $this->address = $supplier->address;

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
        $this->supplierId = null;
        $this->code = '';
        $this->name = '';
        $this->address = '';
        $this->resetValidation();
    }

    public function save(SupplierService $supplierService)
    {
        $this->validate();

        try {
            $data = [
                'code' => $this->code,
                'name' => $this->name,
                'address' => $this->address,
            ];

            if ($this->modalMode === 'create') {
                $supplierService->create($data);
                session()->flash('message', 'Supplier berhasil dibuat!');
            } else {
                $supplierService->update($this->supplierId, $data);
                session()->flash('message', 'Supplier berhasil diupdate!');
            }

            $this->closeModal();

        } catch (\Exception $e) {
            $this->addError('save', 'Gagal menyimpan supplier: ' . $e->getMessage());
        }
    }

    public function delete($id, SupplierService $supplierService)
    {
        try {
            $supplierService->delete($id);
            session()->flash('message', 'Supplier berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus supplier: ' . $e->getMessage());
        }
    }

    public function render(SupplierService $supplierService)
    {
        $filters = array_filter([
            'search' => $this->search,
        ], fn($value) => $value !== '' && $value !== null);

        $suppliers = $supplierService->getPaginated($filters, $this->perPage);

        return view('livewire.user.supplier-management', [
            'suppliers' => $suppliers,
        ]);
    }
}
