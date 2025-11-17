<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function successResponse($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'errors' => null
        ], $status);
    }

    protected function errorResponse($errors = null, string $message = 'Error', int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => null,
            'errors' => $errors
        ], $status);
    }

    protected function validationErrorResponse(array $errors, string $message = 'Validation Error'): JsonResponse
    {
        return $this->errorResponse($errors, $message, 422);
    }

    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse(null, $message, 404);
    }

    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse(null, $message, 401);
    }
}
