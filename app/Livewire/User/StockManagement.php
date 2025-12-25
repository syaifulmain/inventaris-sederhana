<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Services\StockTransactionService;
use App\Enums\StockTransactionType;
use App\Services\ProductService;
use App\Services\SupplierService;
use App\Models\StockTransaction;

#[Title('Manajemen Stok')]
class StockManagement extends Component
{
    use WithPagination;

    public $title = 'Manajemen Stok';
    public $breadcrumb = 'Manajemen Stok';

    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $typeFilter = '';

    #[Url]
    public $dateFrom = '';

    #[Url]
    public $dateTo = '';

    public $perPage = 10;

    // Modal states
    public $showModal = false;
    public $modalMode = 'create';
    public $stockId;

    // Form fields
    public $product_id = '';
    public $supplier_id = '';
    public $type = '';
    public $quantity = '';
    public $description = '';
    public $transaction_date = '';

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date',
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
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
        $stock = StockTransaction::findOrFail($id);

        $this->stockId = $stock->id;
        $this->product_id = $stock->product_id;
        $this->supplier_id = $stock->supplier_id;
        $this->type = $stock->type->value;
        $this->quantity = $stock->quantity;
        $this->description = $stock->description;
        $this->transaction_date = $stock->transaction_date->format('Y-m-d');

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
        $this->stockId = null;
        $this->product_id = '';
        $this->supplier_id = '';
        $this->type = '';
        $this->quantity = '';
        $this->description = '';
        $this->transaction_date = '';
        $this->resetValidation();
    }

    public function save(StockTransactionService $stockTransactionService)
    {
        $this->validate();

        try {
            $data = [
                'product_id' => $this->product_id,
                'supplier_id' => $this->supplier_id,
                'type' => $this->type,
                'quantity' => $this->quantity,
                'description' => $this->description,
                'transaction_date' => $this->transaction_date,
            ];

            if ($this->modalMode === 'create') {
                $stockTransactionService->createStockTransaction($data);
                session()->flash('message', 'Transaksi stok berhasil dibuat!');
            } else {
                $stockTransactionService->updateStockTransaction($this->stockId, $data);
                session()->flash('message', 'Transaksi stok berhasil diupdate!');
            }

            $this->closeModal();

        } catch (\Throwable $e) {
            report($e); // 🔥 INI KUNCI
            $this->addError('save', 'Gagal menyimpan transaksi stok');
        }

    }

    public function delete($id, StockTransactionService $stockTransactionService)
    {
        try {
            $stockTransactionService->delete($id);
            session()->flash('message', 'Transaksi stok berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus transaksi stok: ' . $e->getMessage());
        }
    }

    public function render(StockTransactionService $stockTransactionService, ProductService $productService, SupplierService $supplierService)
    {
        $filters = array_filter([
            'search' => $this->search,
            'type' => $this->typeFilter,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ], fn($value) => $value !== '' && $value !== null);

        $stockTransactions = $stockTransactionService->getPaginated($filters, $this->perPage);
        $products = $productService->getAll();
        $suppliers = $supplierService->getAll();
        $types = StockTransactionType::cases();

        return view('livewire.user.stock-management', [
            'stockTransactions' => $stockTransactions,
            'products' => $products,
            'suppliers' => $suppliers,
            'types' => $types,
        ]);
    }
}
