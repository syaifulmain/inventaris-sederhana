<?php

namespace App\Services;

use App\Models\Category;

class CategoryService extends BaseService
{
    public function __construct(Category $category)
    {
        $this->model = $category;
    }

    protected function applyFilters($query, array $filters)
    {
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('code', 'like', "%{$filters['search']}%")
                    ->orWhere('name', 'like', "%{$filters['search']}%");
            });
        }

        // Default ordering
        $query->orderBy('created_at', 'desc');

        return $query;
    }
}
