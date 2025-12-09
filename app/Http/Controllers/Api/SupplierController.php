<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class SupplierController extends Controller
{
    use ApiResponseTrait;

    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * List suppliers (with pagination and search)
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $search = $request->query('search');

        $suppliers = $this->supplierService->list($search, $perPage, $page);

        $data = [
            'data' => $suppliers->items(),
            'current_page' => $suppliers->currentPage(),
            'per_page' => $suppliers->perPage(),
            'total' => $suppliers->total(),
            'last_page' => $suppliers->lastPage(),
        ];

        return $this->successResponse($data, 'Data supplier berhasil diambil', 200);
    }

    /**
     * Create supplier
     */
    public function store(SupplierRequest $request)
    {
        $supplier = $this->supplierService->create($request->validated());

        return $this->successResponse($supplier, 'Supplier berhasil ditambahkan', 201);
    }

    /**
     * Get supplier by ID
     */
    public function show($id)
    {
        $supplier = $this->supplierService->find($id);

        if (!$supplier) {
            return $this->notFoundResponse('Supplier tidak ditemukan');
        }

        return $this->successResponse($supplier, 'Data supplier berhasil diambil');
    }

    /**
     * Update supplier
     */
    public function update(SupplierRequest $request, $id)
    {
        $supplier = $this->supplierService->find($id);

        if (!$supplier) {
            return $this->errorResponse(
                ['message' => 'Supplier tidak ditemukan'],
                'Error',
                400
            );
        }

        $updated = $this->supplierService->update($supplier, $request->validated());

        return $this->successResponse($updated, 'Supplier berhasil diupdate', 200);
    }

    /**
     * Delete supplier
     */
    public function destroy($id)
    {
        $supplier = $this->supplierService->find($id);

        if (!$supplier) {
            return $this->notFoundResponse('Supplier tidak ditemukan');
        }

        $this->supplierService->delete($supplier);

        return $this->successResponse(null, 'Supplier berhasil dihapus', 200);
    }
}
