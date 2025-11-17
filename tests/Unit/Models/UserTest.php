<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

uses(TestCase::class, RefreshDatabase::class);

test('user has fillable attributes', function () {
    $user = new User();

    expect($user->getFillable())->toContain('name', 'email', 'password', 'role', 'is_active');
});

test('user has hidden attributes', function () {
    $user = new User();

    expect($user->getHidden())->toContain('password', 'remember_token');
});

test('is admin returns true for admin users', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->isAdmin())->toBeTrue();
});

test('is admin returns false for regular users', function () {
    $user = User::factory()->create(['role' => 'user']);

    expect($user->isAdmin())->toBeFalse();
});

test('active scope filters active users', function () {
    User::factory()->create(['is_active' => true]);
    User::factory()->create(['is_active' => true]);
    User::factory()->inactive()->create();

    $activeUsers = User::active()->get();

    $activeUsers->each(function ($user) {
        expect($user->is_active)->toBeTrue();
    });
});

test('search scope filters by name', function () {
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $results = User::search('John')->get();

    expect($results->count())->toBeGreaterThan(0);
    expect($results->first()->name)->toContain('John');
});

test('search scope filters by email', function () {
    User::factory()->create(['email' => 'john@example.com']);
    User::factory()->create(['email' => 'jane@example.com']);

    $results = User::search('john@example')->get();

    expect($results->count())->toBeGreaterThan(0);
    expect($results->first()->email)->toContain('john@example');
});
