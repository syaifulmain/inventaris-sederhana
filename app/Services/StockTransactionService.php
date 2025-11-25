<?php

namespace App\Services;

use App\Models\StockTransaction;

class StockTransactionService extends BaseService
{
    public function __construct(StockTransaction $stockTransaction)
    {
        $this->model = $stockTransaction;
    }

    protected function applyFilters($query, array $filters)
    {
        if (isset($filters['product_id']) && $filters['product_id'] !== '') {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['supplier_id']) && $filters['supplier_id'] !== '') {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('code', 'like', "%{$filters['search']}%")
                    ->orWhere('name', 'like', "%{$filters['search']}%");
            });
        }

        // Default ordering
        $query->orderBy('created_at', 'asc');

        return $query;
    }
}
