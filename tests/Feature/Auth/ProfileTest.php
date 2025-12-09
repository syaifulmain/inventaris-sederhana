<?php

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

test('authenticated user can view profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/profile');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Data profil berhasil diambil',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
});

test('unauthenticated user cannot view profile', function () {
    $response = $this->getJson('/api/profile');

    $response->assertStatus(401);
});

test('user can update their profile name', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'name' => 'New Name',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Profil berhasil diupdate',
            'data' => [
                'name' => 'New Name',
            ],
        ]);

    $user->refresh();
    expect($user->name)->toBe('New Name');
});

test('user can update their email', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'email' => 'new@example.com',
        ]);

    $response->assertStatus(200);

    $user->refresh();
    expect($user->email)->toBe('new@example.com');
});

test('user can update their password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('oldpassword'),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

    $response->assertStatus(200);

    $user->refresh();
    expect(Hash::check('newpassword', $user->password))->toBeTrue();
});

test('user cannot update email to existing email', function () {
    User::factory()->create(['email' => 'existing@example.com']);
    $user = User::factory()->create(['email' => 'user@example.com']);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'email' => 'existing@example.com',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('password must be confirmed when updating', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'password' => 'newpassword',
            'password_confirmation' => 'differentpassword',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('password must be at least 6 characters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

//test('profile returns error when auth fails', function () {
//    $user = User::factory()->create();
//
//    Auth::shouldReceive('user')
//        ->once()
//        ->andThrow(new Exception('Auth error'));
//
//    $response = $this->actingAs($user, 'sanctum')
//        ->getJson('/api/profile');
//
//    $response->assertStatus(404)
//        ->assertJson([
//            'status' => 404,
//            'message' => 'Profil tidak ditemukan',
//        ]);
//});

test('update profile returns error when auth service throws exception', function () {
    $user = User::factory()->create();

    $mock = Mockery::mock(AuthService::class);
    $mock->shouldReceive('updateProfile')
        ->once()
        ->andThrow(new Exception('Service error'));
    $this->app->instance(AuthService::class, $mock);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal mengupdate profil',
            'errors' => ['message' => 'Service error'],
        ]);
});
