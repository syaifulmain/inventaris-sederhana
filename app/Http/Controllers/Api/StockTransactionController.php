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

            return $this->successResponse($products, 'Stock transaction data retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Failed to retrieve stock transaction data',
                500
            );
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockTransactionRequest $request)
    {
        try {
            // $product = $this->productService->createProduct($request->validated());
            $stockTransaction = $this->service->create($request->validated());

            return $this->successResponse(
                $stockTransaction,
                'Stock transaction created successfully',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Failed to create stock transaction',
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
            return $this->successResponse($stockTransaction, 'Stock transaction data retrieved successfully');
        } catch (Exception $e) {
            return $this->notFoundResponse('Stock transaction not found');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(StockTransaction $stockTransaction)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStockTransactionRequest $request, StockTransaction $stockTransaction)
    {
        try {
            // $stockTransaction = $this->service->update($id, $request->validated());
            $stockTransaction = $this->service->update($stockTransaction->id, $request->validated());
            return $this->successResponse($stockTransaction, 'Stock transaction updated successfully');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Stock transaction not found');
            }

            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengupdate produk',
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
            return $this->successResponse(null, 'Stock transaction deleted successfully');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Stock transaction not found');
            }

            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Failed to delete stock transaction',
                500
            );
        }
    }
}
