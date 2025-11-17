<?php

use App\Livewire\Admin\UserManagement;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {

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
