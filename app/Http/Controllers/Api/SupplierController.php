<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Services\SupplierService;

class SupplierController extends Controller
{
    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * List all suppliers
     */
    public function index()
    {
        $suppliers = $this->supplierService->all();

        return response()->json([
            'status'  => 200,
            'message' => 'Daftar supplier berhasil diambil',
            'data'    => $suppliers,
        ]);
    }

    /**
     * Create new supplier
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'    => 'required|string|max:50|unique:suppliers,code',
            'name'    => 'required|string|max:255',
            'address' => 'required|string',
        ]);

        $supplier = $this->supplierService->create($data);

        return response()->json([
            'status'  => 201,
            'message' => 'Supplier berhasil ditambahkan',
            'data'    => $supplier,
        ], 201);
    }

    /**
     * Show supplier by ID
     */
    public function show($id)
    {
        $supplier = $this->supplierService->find($id);

        if (!$supplier) {
            return response()->json([
                'status'  => 404,
                'message' => 'Supplier tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Detail supplier berhasil diambil',
            'data'    => $supplier,
        ]);
    }

    /**
     * Update supplier
     */
    public function update(Request $request, $id)
    {
        $supplier = $this->supplierService->find($id);

        if (!$supplier) {
            return response()->json([
                'status'  => 404,
                'message' => 'Supplier tidak ditemukan',
            ], 404);
        }

        $data = $request->validate([
            'code'    => 'sometimes|required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'name'    => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
        ]);

        $supplier = $this->supplierService->update($supplier, $data);

        return response()->json([
            'status'  => 200,
            'message' => 'Supplier berhasil diupdate',
            'data'    => $supplier,
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
                'status'  => 404,
                'message' => 'Supplier tidak ditemukan',
            ], 404);
        }

        $this->supplierService->delete($supplier);

        return response()->json([
            'status'  => 200,
            'message' => 'Supplier berhasil dihapus',
        ]);
    }
}
