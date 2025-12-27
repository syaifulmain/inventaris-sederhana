<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockTransaction;
use App\Http\Requests\StoreStockTransactionRequest;
use App\Http\Requests\UpdateStockTransactionRequest;
use App\Services\StockTransactionService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class StockTransactionController extends Controller
{
    use ApiResponseTrait;

    protected $service;

    /**
     * Summary of __construct
     * @param StockTransactionService $stockTransactionService
     */
    public function __construct(StockTransactionService $stockTransactionService)
    {
        $this->service = $stockTransactionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'product_id', 'supplier_id', 'type']);
            $perPage = $request->input('per_page', 10);

            $products = $this->service->getPaginated($filters, $perPage);

            return $this->successResponse($products, 'Data transaksi stok berhasil diambil');
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengambil data transaksi stok',
                500
            );
        }
    }

    public function store(StoreStockTransactionRequest $request)
    {
        try {
            $stockTransaction = $this->service->createStockTransaction($request->validated());

            return $this->successResponse(
                $stockTransaction,
                'Transaksi stok berhasil dibuat',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal membuat transaksi stok',
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockTransaction $stockTransaction)
    {

        try {
            $stockTransaction = $this->service->findById($stockTransaction->id);
            // $product = $this->productService->findById($id);
            return $this->successResponse($stockTransaction, 'Data transaksi stok berhasil diambil');
        } catch (Exception $e) {
            return $this->notFoundResponse('Transaksi Stok tidak ditemukan');
        }
    }

    public function update(UpdateStockTransactionRequest $request, StockTransaction $stockTransaction)
    {
        try {
            $stockTransaction = $this->service->updateStockTransaction($stockTransaction->id, $request->validated());
            return $this->successResponse($stockTransaction, 'Transaksi stok berhasil diperbarui');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Transaksi Stok tidak ditemukan');
            }

            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengupdate transaksi stok',
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockTransaction $stockTransaction)
    {
        try {
            $this->service->delete($stockTransaction->id);
            return $this->successResponse(null, 'Transaksi stok berhasil dihapus');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Transaksi Stok tidak ditemukan');
            }

            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal menghapus transaksi stok',
                500
            );
        }
    }
}
