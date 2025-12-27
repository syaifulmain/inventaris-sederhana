<?php

namespace App\Services;

use App\Models\StockTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class StockTransactionService extends BaseService
{
    public function __construct(StockTransaction $stockTransaction)
    {
        $this->model = $stockTransaction;
    }

    public function createStockTransaction(array $data)
    {
        DB::beginTransaction();
        try {
            // Create stock transaction
            $data['user_id'] = auth()->id();
            $stockTransaction = $this->model->create($data);

            DB::commit();
            return $stockTransaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStockTransaction($id, array $data)
    {
        DB::beginTransaction();
        try {
            $stockTransaction = $this->findById($id);

            // Update transaction
            $stockTransaction->update($data);
            $stockTransaction = $stockTransaction->fresh();

            DB::commit();
            return $stockTransaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function applyFilters($query, array $filters)
    {
        // Load relationships for search
        $query->with(['product', 'supplier', 'user']);

        // Filter by type
        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        // Filter by date range
        if (isset($filters['date_from']) && $filters['date_from'] !== '') {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to'] !== '') {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        // Search by product name, supplier name, or transaction code
        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('transaction_code', 'like', "%{$filters['search']}%")
                    ->orWhereHas('product', function ($productQuery) use ($filters) {
                        $productQuery->where('name', 'like', "%{$filters['search']}%");
                    })
                    ->orWhereHas('supplier', function ($supplierQuery) use ($filters) {
                        $supplierQuery->where('name', 'like', "%{$filters['search']}%");
                    });
            });
        }

        // Default ordering
        $query->orderBy('transaction_date', 'desc')
              ->orderBy('created_at', 'desc');

        return $query;
    }
}
