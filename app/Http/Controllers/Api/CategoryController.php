<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\CategoryService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search']);
            $perPage = $request->input('per_page', 10);

            $categories = $this->categoryService->getPaginated($filters, $perPage);

            return $this->successResponse($categories, 'Data kategori berhasil diambil');
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengambil data kategori',
                500
            );
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $this->categoryService->create($request->validated());
            return $this->successResponse($category, 'Kategori berhasil ditambahkan', 201);
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal menambahkan kategori',
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $category = $this->categoryService->findById($id);
            return $this->successResponse($category, 'Data kategori berhasil diambil');
        } catch (Exception $e) {
            return $this->notFoundResponse('Kategori tidak ditemukan');
        }
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $category = $this->categoryService->update($id, $request->validated());
            return $this->successResponse($category, 'Kategori berhasil diupdate');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Kategori tidak ditemukan');
            }

            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengupdate kategori',
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $this->categoryService->delete($id);
            return $this->successResponse(null, 'Kategori berhasil dihapus');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Kategori tidak ditemukan');
            }

            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal menghapus kategori',
                500
            );
        }
    }
}
