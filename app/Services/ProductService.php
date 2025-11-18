<?php

namespace App\Services;

use App\Models\Product;


class ProductService extends BaseService
{
    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    public function createProduct(array $data)
    {
        return $this->create($data);
    }

    public function updateProduct($id, array $data)
    {
        return $this->update($id, $data);
    }

    protected function applyFilters($query, array $filters)
    {
        if (isset($filters['category']) && $filters['category'] !== '') {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('code', 'like', "%{$filters['search']}%")
                    ->orWhere('name', 'like', "%{$filters['search']}%");
            });
        }

        // Default ordering
        $query->orderBy('created_at', 'asc');

        return $query;
    }
}
