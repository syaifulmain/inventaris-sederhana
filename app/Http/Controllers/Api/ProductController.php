<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Exception;

class ProductController extends Controller
{
    use ApiResponseTrait;

    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'category']);
            $perPage = $request->input('per_page', 10);

            $products = $this->productService->getPaginated($filters, $perPage);

            return $this->successResponse($products, 'Data produk berhasil diambil');
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengambil data produk',
                500
            );
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            return $this->successResponse(
                $product,
                'Produk berhasil dibuat',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal membuat produk',
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $product = $this->productService->findById($id);
            return $this->successResponse($product, 'Data produk berhasil diambil');
        } catch (Exception $e) {
            return $this->notFoundResponse('Produk tidak ditemukan');
        }
    }

    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $product = $this->productService->updateProduct($id, $request->validated());
            return $this->successResponse($product, 'Produk berhasil diupdate');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Produk tidak ditemukan');
            }

            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengupdate produk',
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $this->productService->delete($id);
            return $this->successResponse(null, 'Produk berhasil dihapus');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Produk tidak ditemukan');
            }

            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal menghapus produk',
                500
            );
        }
    }
}
