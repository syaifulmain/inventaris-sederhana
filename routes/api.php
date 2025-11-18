<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Admin\SupplierController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (authenticated users)
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {

        // User management
        Route::apiResource('users', UserController::class);

        // Supplier management
        Route::apiResource('suppliers', SupplierController::class);
    });
});
