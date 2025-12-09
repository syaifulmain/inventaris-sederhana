<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use Exception;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());
            return $this->successResponse($result, 'Login berhasil');
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Login gagal',
                401
            );
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout();
            return $this->successResponse(null, 'Logout berhasil');
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Logout gagal',
                500
            );
        }
    }

    public function profile()
    {
        try {
            $user = auth()->user();
            return $this->successResponse($user, 'Data profil berhasil diambil');
        } catch (Exception $e) {
            return $this->notFoundResponse('Profil tidak ditemukan');
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = $this->authService->updateProfile(
                auth()->id(),
                $request->validated()
            );
            return $this->successResponse($user, 'Profil berhasil diupdate');
        } catch (Exception $e) {
            return $this->errorResponse(
                ['message' => $e->getMessage()],
                'Gagal mengupdate profil',
                500
            );
        }
    }
}
