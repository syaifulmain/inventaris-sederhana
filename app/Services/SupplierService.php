<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService extends BaseService
{
    public function __construct(Supplier $supplier)
    {
        $this->model = $supplier;
    }

    public function findById($id)
    {
        $supplier = $this->model->find($id);

        if (!$supplier) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Supplier tidak ditemukan');
        }

        return $supplier;
    }

    protected function applyFilters($query, array $filters)
    {
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('code', 'like', "%{$filters['search']}%")
                    ->orWhere('name', 'like', "%{$filters['search']}%")
                    ->orWhere('address', 'like', "%{$filters['search']}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        return $query;
    }
}
