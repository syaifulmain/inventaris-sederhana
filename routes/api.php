<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SupplierController; 
use App\Http\Controllers\Api\StockTransactionController;

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

        // Product management
        Route::apiResource('products', ProductController::class);

        // Supplier management
        Route::apiResource('suppliers', SupplierController::class);

        // StockTransaction management
        Route::apiResource('stock-transactions', StockTransactionController::class);
    });
});

// Route::prefix('admin')->group(function () {
//     Route::apiResource('users', UserController::class);
//     Route::apiResource('products', ProductController::class);
// });
