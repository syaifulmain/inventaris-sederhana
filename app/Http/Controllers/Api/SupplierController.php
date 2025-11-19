<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * List Supplier (Pagination)
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $suppliers = Supplier::paginate($perPage);

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
     * Create Supplier
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|unique:suppliers,code',
                'name' => 'required|string',
                'address' => 'nullable|string'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'data' => 'Unknown Type: null',
                'errors' => $e->errors()
            ], 422);
        }

        $supplier = $this->supplierService->create($validated);

        return response()->json([
            'status' => 201,
            'message' => 'Supplier berhasil ditambahkan',
            'data' => $supplier,
            'errors' => 'Unknown Type: null'
        ], 201);
    }

    /**
     * Show Supplier by ID
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
            ], 400);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Data supplier berhasil diambil',
            'data' => $supplier,
            'errors' => 'Unknown Type: null'
        ]);
    }

    /**
     * Update Supplier
     */
    public function update(Request $request, $id)
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

        try {
            $validated = $request->validate([
                'code' => 'nullable|string|unique:suppliers,code,' . $id,
                'name' => 'nullable|string',
                'address' => 'nullable|string'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'data' => 'Unknown Type: null',
                'errors' => $e->errors()
            ], 422);
        }

        $updated = $this->supplierService->update($supplier, $validated);

        return response()->json([
            'status' => 200,
            'message' => 'Supplier berhasil diupdate',
            'data' => $updated,
            'errors' => 'Unknown Type: null'
        ]);
    }

    /**
     * Delete Supplier
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
            ], 400);
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
