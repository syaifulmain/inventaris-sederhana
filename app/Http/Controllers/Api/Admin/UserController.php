<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Exception;

class UserController extends Controller
{
    use ApiResponseTrait;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'role', 'is_active']);
            $perPage = $request->input('per_page', 10);

            $users = $this->userService->getPaginated($filters, $perPage);

            return $this->successResponse($users, 'Data user berhasil diambil');
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengambil data user',
                500
            );
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return $this->successResponse(
                $user,
                'User berhasil dibuat',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal membuat user',
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $user = $this->userService->findById($id);
            return $this->successResponse($user, 'Data user berhasil diambil');
        } catch (Exception $e) {
            return $this->notFoundResponse('User tidak ditemukan');
        }
    }

    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $user = $this->userService->updateUser($id, $request->validated());
            return $this->successResponse($user, 'User berhasil diupdate');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('User tidak ditemukan');
            }

            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengupdate user',
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $this->userService->delete($id);
            return $this->successResponse(null, 'User berhasil dihapus');
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('User tidak ditemukan');
            }

            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal menghapus user',
                500
            );
        }
    }
}
