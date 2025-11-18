<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService
{
    /**
     * Get all suppliers
     */
    public function all()
    {
        return Supplier::all();
    }

    /**
     * Create a new supplier
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Find supplier by ID
     */
    public function find(int $id): ?Supplier
    {
        return Supplier::find($id);
    }

    /**
     * Update supplier
     */
    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);
        return $supplier;
    }

    /**
     * Delete supplier
     */
    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }
}
