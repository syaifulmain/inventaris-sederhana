<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin can view list of users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(15)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'data',
                'current_page',
                'per_page',
                'total',
            ],
            'errors',
        ]);

    expect($response->json('data.per_page'))->toBe(10);
});

test('admin can search users by name', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/users?search=John');

    $response->assertStatus(200);

    $users = $response->json('data.data');
    expect(collect($users)->where('name', 'John Doe')->count())->toBeGreaterThan(0);
});

test('admin can search users by email', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'john@example.com']);
    User::factory()->create(['email' => 'jane@example.com']);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/users?search=john@example.com');

    $response->assertStatus(200);

    $users = $response->json('data.data');
    expect(collect($users)->where('email', 'john@example.com')->count())->toBeGreaterThan(0);
});

test('admin can filter users by role', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->admin()->create();
    User::factory()->count(5)->create(['role' => 'user']);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/users?role=admin');

    $response->assertStatus(200);

    $users = $response->json('data.data');
    foreach ($users as $user) {
        expect($user['role'])->toBe('admin');
    }
});

test('admin can change pagination per page', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(25)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/users?per_page=5');

    $response->assertStatus(200);
    expect($response->json('data.per_page'))->toBe(5);
});

test('admin can create new user', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 201,
            'message' => 'User berhasil dibuat',
            'data' => [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'role' => 'user',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com',
    ]);
});

test('admin can view specific user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/admin/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
});

test('admin can update user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'role' => 'admin',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'User berhasil diupdate',
            'data' => [
                'name' => 'Updated Name',
                'role' => 'admin',
            ],
        ]);

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->role)->toBe('admin');
});

test('admin can delete user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'User berhasil dihapus',
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('admin cannot create user with duplicate email', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('regular user cannot access admin endpoints', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/admin/users');

    $response->assertStatus(403)
        ->assertJson([
            'status' => 403,
            'message' => 'Forbidden. Admin access required',
        ]);
});

test('regular user cannot create users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
        ]);

    $response->assertStatus(403);
});

test('regular user cannot update other users', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/admin/users/{$otherUser->id}", [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(403);
});

test('regular user cannot delete users', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/admin/users/{$otherUser->id}");

    $response->assertStatus(403);
});

test('admin cannot view non-existent user', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/users/99999');

    $response->assertStatus(404)
        ->assertJson([
            'status' => 404,
            'message' => 'User tidak ditemukan',
        ]);
});

test('admin cannot update non-existent user', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson('/api/admin/users/99999', [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(404);
});

test('admin cannot delete non-existent user', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson('/api/admin/users/99999');

    $response->assertStatus(404);
});

test('create user validation fails with missing required fields', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/users', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
});

test('create user validation fails with invalid role', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'invalid_role',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

test('update user email must be unique', function () {
    $admin = User::factory()->admin()->create();
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);
    $user = User::factory()->create(['email' => 'user@example.com']);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/admin/users/{$user->id}", [
            'email' => 'existing@example.com',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
