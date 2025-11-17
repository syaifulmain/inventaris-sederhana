<?php

use App\Models\User;

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Logout berhasil',
            'data' => null,
            'errors' => null,
        ]);

    // Check that token is deleted
    expect($user->tokens()->count())->toBe(0);
});

test('unauthenticated user cannot logout', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401);
});

test('logout deletes all user tokens', function () {
    $user = User::factory()->create();

    // Create multiple tokens
    $user->createToken('token-1');
    $user->createToken('token-2');
    $token3 = $user->createToken('token-3')->plainTextToken;

    expect($user->tokens()->count())->toBe(3);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token3)
        ->postJson('/api/logout');

    $response->assertStatus(200);
    expect($user->tokens()->count())->toBe(0);
});
