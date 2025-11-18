<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * List all suppliers
     */
    public function index()
    {
        $suppliers = Supplier::all();

        return response()->json([
            'status'  => 200,
            'message' => 'Daftar supplier berhasil diambil',
            'data'    => $suppliers
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

        $supplier = Supplier::create($data);

        return response()->json([
            'status'  => 201,
            'message' => 'Supplier berhasil ditambahkan',
            'data'    => $supplier
        ], 201);
    }

    /**
     * Show supplier by ID
     */
    public function show(Supplier $supplier)
    {
        return response()->json([
            'status'  => 200,
            'message' => 'Detail supplier berhasil diambil',
            'data'    => $supplier
        ]);
    }

    /**
     * Update supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'code'    => 'sometimes|required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'name'    => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
        ]);

        $supplier->update($data);

        return response()->json([
            'status'  => 200,
            'message' => 'Supplier berhasil diperbarui',
            'data'    => $supplier
        ]);
    }

    /**
     * Delete supplier
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Supplier berhasil dihapus'
        ]);
    }
}
