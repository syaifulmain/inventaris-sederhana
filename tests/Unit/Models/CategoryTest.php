<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use App\Models\Product;

uses(TestCase::class, RefreshDatabase::class);

test('category has fillable attributes', function () {
    $category = new Category();

    expect($category->getFillable())->toBe([
        'code',
        'name',
    ]);
});

test('category can be created', function () {
    $category = Category::factory()->create([
        'code' => 'TEST',
        'name' => 'Test Category',
    ]);

    expect($category->code)->toBe('TEST');
    expect($category->name)->toBe('Test Category');
    expect($category->exists)->toBeTrue();
});

test('category has products relationship', function () {
    $category = Category::factory()->create();
    Product::factory()->count(3)->create(['category_id' => $category->id]);

    expect($category->products)->toHaveCount(3);
    expect($category->products->first())->toBeInstanceOf(Product::class);
});

test('category code must be unique', function () {
    Category::factory()->create(['code' => 'UNIQUE']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Category::factory()->create(['code' => 'UNIQUE']);
});

test('category has timestamps', function () {
    $category = Category::factory()->create();

    expect($category->created_at)->not->toBeNull();
    expect($category->updated_at)->not->toBeNull();
});
