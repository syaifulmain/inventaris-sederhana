<?php

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);
});

test('user can login with valid credentials', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ],
                'token',
                'token_type',
            ],
            'errors',
        ]);

    expect($response->json('data.token'))->not->toBeNull();
    expect($response->json('data.token_type'))->toBe('Bearer');
});

test('user cannot login with invalid email', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'wrong@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'status' => 401,
            'message' => 'Login gagal',
        ]);
});

test('user cannot login with invalid password', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
});

test('inactive user cannot login', function () {
    $inactiveUser = User::factory()->inactive()->create([
        'email' => 'inactive@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'inactive@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(401)
        ->assertJsonFragment([
            'message' => 'Login gagal',
        ]);
});

test('login validation fails with missing email', function () {
    $response = $this->postJson('/api/login', [
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login validation fails with invalid email format', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'invalid-email',
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login validation fails with short password', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => '12345',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('login returns error when auth service throws exception', function () {
    $mock = Mockery::mock(AuthService::class);
    $mock->shouldReceive('login')
        ->once()
        ->andThrow(new Exception('Service error'));
    $this->app->instance(AuthService::class, $mock);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'status' => 401,
            'message' => 'Login gagal',
            'errors' => ['message' => 'Service error'],
        ]);
});
