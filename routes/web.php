<?php

use App\Http\Controllers\OpenApiController;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/api-docs', [OpenApiController::class, 'show'])->name('api.docs');

// routes/web.php
Route::get('/api-documentation', function () {
    return view('swagger');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', Login::class)->name('login');
});

// Authenticated routes
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', \App\Livewire\User\Dashboard::class)->name('dashboard');

    // Profile
    Route::get('/profile', Profile::class)->name('profile');

    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/users', UserManagement::class)->name('admin.users');
    });
});
