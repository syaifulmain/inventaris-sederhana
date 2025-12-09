<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\UserService;

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

test('admin can combine multiple filters', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Admin John', 'role' => 'admin', 'is_active' => true]);
    User::factory()->create(['name' => 'User John', 'role' => 'user', 'is_active' => true]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/users?search=John&role=admin&is_active=1');

    $response->assertStatus(200);

    $users = $response->json('data.data');
    expect(count($users))->toBeGreaterThan(0);
    foreach ($users as $user) {
        expect($user['role'])->toBe('admin');
    }
});

test('admin can update user password', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'User berhasil diupdate',
        ]);

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('admin can update user without changing password', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
    $oldPassword = $user->password;

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

    $response->assertStatus(200);

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->password)->toBe($oldPassword);
});

test('admin can update user with same email', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['email' => 'user@example.com']);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'user@example.com',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'User berhasil diupdate',
        ]);
});

test('create user validation fails with password mismatch', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
            'role' => 'user',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('create user validation fails with invalid email', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('update user validation fails with password mismatch', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'different',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('update user validation fails with short password', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '123',
            'password_confirmation' => '123',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('create user validation fails with short password', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => '123',
            'password_confirmation' => '123',
            'role' => 'user',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('admin can create user with is_active status', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/users', [
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
            'is_active' => false,
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'inactive@example.com',
        'is_active' => false,
    ]);
});

test('admin can update user is_active status', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['is_active' => true]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => false,
        ]);

    $response->assertStatus(200);

    $user->refresh();
    expect($user->is_active)->toBe(false);
});


test('admin receives error when user listing fails', function () {
    $admin = User::factory()->admin()->create();

    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('getPaginated')
        ->once()
        ->andThrow(new Exception('Service error'));
    $this->app->instance(UserService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/users');

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal mengambil data user',
            'errors' => ['message' => 'Service error'],
        ]);
});

test('admin receives error when user creation fails', function () {
    $admin = User::factory()->admin()->create();

    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('createUser')
        ->once()
        ->andThrow(new Exception('Service error'));
    $this->app->instance(UserService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/admin/users', [
            'name' => 'Broken User',
            'email' => 'broken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
        ]);

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal membuat user',
            'errors' => ['message' => 'Service error'],
        ]);
});

test('admin receives error when user update fails', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('updateUser')
        ->once()
        ->andThrow(new Exception('Service error'));
    $this->app->instance(UserService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal mengupdate user',
            'errors' => ['message' => 'Service error'],
        ]);
});

test('admin receives error when user deletion fails', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $mock = Mockery::mock(UserService::class);
    $mock->shouldReceive('delete')
        ->once()
        ->andThrow(new Exception('Service error'));
    $this->app->instance(UserService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/users/{$user->id}");

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal menghapus user',
            'errors' => ['message' => 'Service error'],
        ]);
});
