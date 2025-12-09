<?php

use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockTransactionController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Menu routers
    Route::apiResource('products', ProductController::class);

    // Supplier management
    Route::apiResource('suppliers', SupplierController::class);

    Route::apiResource('stock-transactions', StockTransactionController::class);

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});
// Route::prefix('admin')->group(function () {
//     Route::apiResource('users', UserController::class);
//     Route::apiResource('products', ProductController::class);
// });
