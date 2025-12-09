<?php

use App\Models\User;
use App\Models\Category;

test('admin can view list of categories', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->count(15)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/categories');

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

test('admin can search categories', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->create(['name' => 'Electronics', 'code' => 'ELEC']);
    Category::factory()->create(['name' => 'Fashion', 'code' => 'FASH']);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/categories?search=Electronics');

    $response->assertStatus(200);

    $categories = $response->json('data.data');
    expect(collect($categories)->where('name', 'Electronics')->count())->toBeGreaterThan(0);
});

test('admin can change pagination per page', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->count(25)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/categories?per_page=5');

    $response->assertStatus(200);
    expect($response->json('data.per_page'))->toBe(5);
});

test('admin can create new category', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/categories', [
            'code' => 'BOOK',
            'name' => 'Books',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 201,
            'message' => 'Kategori berhasil ditambahkan',
            'data' => [
                'code' => 'BOOK',
                'name' => 'Books',
            ],
        ]);

    $this->assertDatabaseHas('categories', [
        'code' => 'BOOK',
        'name' => 'Books',
    ]);
});

test('admin can view specific category', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'data' => [
                'id' => $category->id,
                'code' => $category->code,
                'name' => $category->name,
            ],
        ]);
});

test('admin can update category', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Kategori berhasil diupdate',
            'data' => [
                'name' => 'Updated Name',
            ],
        ]);

    $category->refresh();
    expect($category->name)->toBe('Updated Name');
});

test('admin can delete category', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Kategori berhasil dihapus',
        ]);

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

test('admin cannot create category with duplicate code', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->create(['code' => 'EXISTING']);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/categories', [
            'code' => 'EXISTING',
            'name' => 'New Category',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('admin cannot view non-existent category', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/categories/99999');

    $response->assertStatus(404)
        ->assertJson([
            'status' => 404,
            'message' => 'Kategori tidak ditemukan',
        ]);
});

test('admin cannot update non-existent category', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson('/api/categories/99999', [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(404);
});

test('admin cannot delete non-existent category', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson('/api/categories/99999');

    $response->assertStatus(404);
});

test('create category validation fails with missing required fields', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/categories', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code', 'name']);
});

test('update category code must be unique', function () {
    $admin = User::factory()->admin()->create();
    $existingCategory = Category::factory()->create(['code' => 'EXISTING']);
    $category = Category::factory()->create(['code' => 'CURRENT']);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/categories/{$category->id}", [
            'code' => 'EXISTING',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('index returns error when service throws exception', function () {
    $admin = User::factory()->admin()->create();

    $mock = Mockery::mock(\App\Services\CategoryService::class);
    $mock->shouldReceive('getPaginated')
        ->once()
        ->andThrow(new Exception('Service error'));
    $this->app->instance(\App\Services\CategoryService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/categories');

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal mengambil data kategori',
            'errors' => ['message' => 'Service error'],
        ]);
});

test('store returns error when service throws exception', function () {
    $admin = User::factory()->admin()->create();

    $mock = Mockery::mock(\App\Services\CategoryService::class);
    $mock->shouldReceive('create')
        ->once()
        ->andThrow(new Exception('Create error'));
    $this->app->instance(\App\Services\CategoryService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/categories', [
            'code' => 'TEST',
            'name' => 'Test Category',
        ]);

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal menambahkan kategori',
            'errors' => ['message' => 'Create error'],
        ]);
});

test('update returns error when service throws generic exception', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $mock = Mockery::mock(\App\Services\CategoryService::class);
    $mock->shouldReceive('update')
        ->once()
        ->andThrow(new Exception('Update error'));
    $this->app->instance(\App\Services\CategoryService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal mengupdate kategori',
            'errors' => ['message' => 'Update error'],
        ]);
});

test('delete returns error when service throws generic exception', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $mock = Mockery::mock(\App\Services\CategoryService::class);
    $mock->shouldReceive('delete')
        ->once()
        ->andThrow(new Exception('Delete error'));
    $this->app->instance(\App\Services\CategoryService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/categories/{$category->id}");

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal menghapus kategori',
            'errors' => ['message' => 'Delete error'],
        ]);
});
