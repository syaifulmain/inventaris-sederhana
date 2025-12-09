<?php

// /*
use App\Models\User;
use App\Models\Category;
use App\Models\Product;

test('admin can view list of products', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->count(15)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/products');

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

test('admin can search products by name', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['name' => 'Lenovo ThinkPad X1 Carbon']);
    Product::factory()->create(['name' => 'Asus ROG Strix G15']);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/products?search=Lenovo ThinkPad X1 Carbon');
    $response->assertStatus(200);

    $products = $response->json('data.data');
    expect(collect($products)->where('name', 'Lenovo ThinkPad X1 Carbon')->count())->toBeGreaterThan(0);
});

test('admin can filter products by category', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Laptop']);
    $otherCategory = Category::factory()->create(['name' => 'Monitor']);

    $laptopProducts = Product::factory()->count(3)->create(['category_id' => $category->id]);
    Product::factory()->count(5)->create(['category_id' => $otherCategory->id]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/products?category_id={$category->id}");

    $response->assertStatus(200);

    $products = $response->json('data.data');
    
    // Pastikan jumlah produk sesuai yang dibuat untuk kategori Laptop
    expect(count($products))->toBe(3);
    
    // Pastikan semua produk adalah produk Laptop yang dibuat
    $productIds = collect($products)->pluck('id')->toArray();
    foreach ($laptopProducts as $laptopProduct) {
        expect($productIds)->toContain($laptopProduct->id);
    }
});


test('admin can change pagination per page', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->count(25)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/products?per_page=5');

    $response->assertStatus(200);
    expect($response->json('data.per_page'))->toBe(5);
});

test('admin can create new product', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/products', [
            'code' => 'PROD-999',
            'category_id' => $category->id, 
            'name' => 'New Product',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 201,
            'message' => 'Produk berhasil dibuat',
            'data' => [
                'code' => 'PROD-999',
                'category_id' => 1,
                'name' => 'New Product',
            ],
        ]);

    $this->assertDatabaseHas('products', [
        'code' => 'PROD-999',
    ]);
});

test('admin can view specific product', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/products/{$product->id}");
    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
            ],
        ]);
});

test('admin can update product', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Produk berhasil diupdate',
            'data' => [
                'name' => 'Updated Name',
            ],
        ]);

    $product->refresh();
    expect($product->name)->toBe('Updated Name');
});

test('admin can delete product', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Produk berhasil dihapus',
        ]);

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

test('admin cannot create product with duplicate code', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['code' => 'EXISTING-CODE']);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/products', [
            'code' => 'EXISTING-CODE',
            'category_id' => 1,
            'name' => 'New Product',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('admin cannot view non-existent product', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/products/99999');

    $response->assertStatus(404)
        ->assertJson([
            'status' => 404,
            'message' => 'Produk tidak ditemukan',
        ]);
});

test('admin cannot update non-existent product', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson('/api/products/99999', [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(404);
});

test('admin cannot delete non-existent product', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson('/api/products/99999');

    $response->assertStatus(404);
});

test('create product validation fails with missing required fields', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/products', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code', 'category_id', 'name']);
});

test('update product code must be unique', function () {
    $admin = User::factory()->admin()->create();
    $existingProduct = Product::factory()->create(['code' => 'existing-code']);
    $product = Product::factory()->create(['code' => 'user-code']);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/products/{$product->id}", [
            'code' => 'existing-code',
        ]); 

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

