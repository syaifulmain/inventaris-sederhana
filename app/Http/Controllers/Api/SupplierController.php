<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
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

        return response()->json([
            'status' => 200,
            'message' => 'Data supplier berhasil diambil',
            'data' => [
                'data' => $suppliers->items(),
                'current_page' => $suppliers->currentPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
                'last_page' => $suppliers->lastPage(),
            ],
            'errors' => 'Unknown Type: null'
        ]);
    }

    /**
     * Create supplier
     */
    public function store(SupplierRequest $request)
    {
        $supplier = $this->supplierService->create($request->validated());

        return response()->json([
            'status' => 201,
            'message' => 'Supplier berhasil ditambahkan',
            'data' => $supplier,
            'errors' => 'Unknown Type: null'
        ], 201);
    }

    /**
     * Get supplier by ID
     */
    public function show($id)
    {
        $supplier = $this->supplierService->find($id);

        if (!$supplier) {
            return response()->json([
                'status' => 400,
                'message' => 'Error',
                'data' => 'Unknown Type: null',
                'errors' => [
                    'message' => 'Supplier tidak ditemukan'
                ]
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Data supplier berhasil diambil',
            'data' => $supplier,
            'errors' => 'Unknown Type: null'
        ]);
    }

    /**
     * Update supplier
     */
    public function update(SupplierRequest $request, $id)
    {
        $supplier = $this->supplierService->find($id);

        if (!$supplier) {
            return response()->json([
                'status' => 400,
                'message' => 'Error',
                'data' => 'Unknown Type: null',
                'errors' => [
                    'message' => 'Supplier tidak ditemukan'
                ]
            ], 400);
        }

        $updated = $this->supplierService->update($supplier, $request->validated());

        return response()->json([
            'status' => 200,
            'message' => 'Supplier berhasil diupdate',
            'data' => $updated,
            'errors' => 'Unknown Type: null'
        ]);
    }

    /**
     * Delete supplier
     */
    public function destroy($id)
    {
        $supplier = $this->supplierService->find($id);

        if (!$supplier) {
            return response()->json([
                'status' => 400,
                'message' => 'Error',
                'data' => 'Unknown Type: null',
                'errors' => [
                    'message' => 'Supplier tidak ditemukan'
                ]
            ], 404);
        }

        $this->supplierService->delete($supplier);

        return response()->json([
            'status' => 200,
            'message' => 'Supplier berhasil dihapus',
            'data' => 'Unknown Type: null',
            'errors' => 'Unknown Type: null'
        ]);
    }
}
