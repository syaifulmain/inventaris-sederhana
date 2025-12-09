<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Exception;

class SupplierController extends Controller
{
    use ApiResponseTrait;

    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search']);
            $perPage = $request->input('per_page', 10);

            $suppliers = $this->supplierService->getPaginated($filters, $perPage);

            return $this->successResponse($suppliers, 'Data supplier berhasil diambil');
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengambil data supplier',
                500
            );
        }
    }

    public function store(StoreSupplierRequest $request)
    {
        try {
            $supplier = $this->supplierService->create($request->validated());
            return $this->successResponse($supplier, 'Supplier berhasil ditambahkan', 201);
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal menambahkan supplier',
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $supplier = $this->supplierService->findById($id);
            return $this->successResponse($supplier, 'Data supplier berhasil diambil');
        } catch (Exception $e) {
            return $this->notFoundResponse('Supplier tidak ditemukan');
        }
    }

    public function update(UpdateSupplierRequest $request, $id)
    {
        try {
            $supplier = $this->supplierService->update($id, $request->validated());
            return $this->successResponse($supplier, 'Supplier berhasil diupdate');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Supplier tidak ditemukan');
            }
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengupdate supplier',
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $this->supplierService->delete($id);
            return $this->successResponse(null, 'Supplier berhasil dihapus');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Supplier tidak ditemukan');
            }
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal menghapus supplier',
                500
            );
        }
    }
}
