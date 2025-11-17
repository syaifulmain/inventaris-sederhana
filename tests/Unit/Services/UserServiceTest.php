<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Services\UserService;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->userService = app(UserService::class);
});

test('create user hashes password', function () {
    $user = $this->userService->createUser([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'role' => 'user',
    ]);

    expect(Hash::check('password', $user->password))->toBeTrue();
    expect($user->password)->not->toBe('password');
});

test('update user hashes new password', function () {
    $user = User::factory()->create();

    $updated = $this->userService->updateUser($user->id, [
        'password' => 'newpassword',
    ]);

    expect(Hash::check('newpassword', $updated->password))->toBeTrue();
});

test('update user does not change password if not provided', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);

    $oldPassword = $user->password;

    $this->userService->updateUser($user->id, [
        'name' => 'Updated Name',
    ]);

    $user->refresh();
    expect($user->password)->toBe($oldPassword);
});

test('get paginated applies search filter', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $result = $this->userService->getPaginated(['search' => 'John'], 10);

    expect($result->total())->toBeGreaterThan(0);
    expect($result->first()->name)->toContain('John');
});

test('get paginated applies role filter', function () {
    User::factory()->count(3)->admin()->create();
    User::factory()->count(5)->create(['role' => 'user']);

    $result = $this->userService->getPaginated(['role' => 'admin'], 10);

    $result->each(function ($user) {
        expect($user->role)->toBe('admin');
    });
});

test('delete removes user from database', function () {
    $user = User::factory()->create();

    $this->userService->delete($user->id);

    expect(User::find($user->id))->toBeNull();
});
