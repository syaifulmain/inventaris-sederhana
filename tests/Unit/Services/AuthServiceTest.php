<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->authService = app(AuthService::class);
});

test('login returns user and token on valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    $result = $this->authService->login([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    expect($result)->toHaveKeys(['user', 'token', 'token_type']);
    expect($result['user']->id)->toBe($user->id);
    expect($result['token'])->not->toBeNull();
    expect($result['token_type'])->toBe('Bearer');
});

test('login throws exception for invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->authService->login([
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);
})->throws(Exception::class, 'Email atau password salah');

test('login throws exception for inactive user', function () {
    User::factory()->inactive()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->authService->login([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
})->throws(Exception::class, 'Akun Anda tidak aktif');

test('logout deletes all user tokens', function () {
    $user = User::factory()->create();
    $user->createToken('token-1');
    $user->createToken('token-2');

    Auth::login($user);

    expect($user->tokens()->count())->toBe(2);

    $this->authService->logout();

    expect($user->tokens()->count())->toBe(0);
});

test('update profile updates user data', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $updated = $this->authService->updateProfile($user->id, [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

    expect($updated->name)->toBe('New Name');
    expect($updated->email)->toBe('new@example.com');
});

test('update profile hashes password', function () {
    $user = User::factory()->create();

    $updated = $this->authService->updateProfile($user->id, [
        'password' => 'newpassword',
    ]);

    expect(Hash::check('newpassword', $updated->password))->toBeTrue();
});
