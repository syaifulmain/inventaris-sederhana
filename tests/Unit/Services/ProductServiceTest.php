<?php
// filepath: tests/Unit/Services/ProductServiceTest.php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Category;
use App\Services\ProductService;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->productService = app(ProductService::class);
});

test('create product stores data correctly', function () {
    $category = Category::factory()->create();
    
    $product = $this->productService->createProduct([
        'name' => 'Laptop ASUS ROG',
        'code' => 'PROD-001',
        'category_id' => $category->id,
    ]);

    expect($product->name)->toBe('Laptop ASUS ROG');
    expect($product->code)->toBe('PROD-001');
    expect($product->category_id)->toBe($category->id);
    $this->assertDatabaseHas('products', [
        'name' => 'Laptop ASUS ROG',
        'code' => 'PROD-001',
    ]);
});

test('update product modifies data correctly', function () {
    $product = Product::factory()->create(['name' => 'Old Name']);
    
    $updated = $this->productService->updateProduct($product->id, [
        'name' => 'New Name',
    ]);

    expect($updated->name)->toBe('New Name');
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'New Name',
    ]);
});

test('get paginated applies search filter by name', function () {
    Product::factory()->create(['name' => 'Laptop ASUS']);
    Product::factory()->create(['name' => 'Monitor Samsung']);

    $result = $this->productService->getPaginated(['search' => 'Laptop'], 10);

    expect($result->total())->toBeGreaterThan(0);
    expect($result->first()->name)->toContain('Laptop');
});

test('get paginated applies search filter by code', function () {
    Product::factory()->create(['code' => 'PROD-001']);
    Product::factory()->create(['code' => 'PROD-002']);

    $result = $this->productService->getPaginated(['search' => 'PROD-001'], 10);

    expect($result->total())->toBe(1);
    expect($result->first()->code)->toBe('PROD-001');
});

test('get paginated applies category_id filter', function () {
    $category = Category::factory()->create();
    Product::factory()->count(3)->create(['category_id' => $category->id]);
    Product::factory()->count(2)->create(); // different categories

    $result = $this->productService->getPaginated(['category_id' => $category->id], 10);

    expect($result->total())->toBe(3);
    $result->each(function ($product) use ($category) {
        expect($product->category_id)->toBe($category->id);
    });
});

test('delete removes product from database', function () {
    $product = Product::factory()->create();

    $this->productService->delete($product->id);

    expect(Product::find($product->id))->toBeNull();
});

test('find by id returns product with category', function () {
    $category = Category::factory()->create(['name' => 'Laptop']);
    $product = Product::factory()->create(['category_id' => $category->id]);

    $found = $this->productService->findById($product->id);

    expect($found->id)->toBe($product->id);
    expect($found->category)->not->toBeNull();
    expect($found->category->name)->toBe('Laptop');
});