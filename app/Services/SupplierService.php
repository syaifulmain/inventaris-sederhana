<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService
{
    /**
     * List suppliers with optional search and pagination
     */
    public function list(?string $search = null, int $perPage = 10, int $page = 1)
    {
        $query = Supplier::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%$search%")
                  ->orWhere('name', 'like', "%$search%")
                  ->orWhere('address', 'like', "%$search%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create a new supplier
     */
    public function create(array $data)
    {
        return Supplier::create($data);
    }

    /**
     * Find supplier by ID
     */
    public function find($id)
    {
        return Supplier::find($id);
    }

    /**
     * Update supplier
     */
    public function update(Supplier $supplier, array $data)
    {
        $supplier->update($data);
        return $supplier;
    }

    /**
     * Delete supplier
     */
    public function delete(Supplier $supplier)
    {
        $supplier->delete();
    }
}
