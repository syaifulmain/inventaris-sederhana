<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use App\Models\Product;

uses(TestCase::class, RefreshDatabase::class);

test('product has fillable attributes', function () {
    $product = new Product();

    expect($product->getFillable())->toContain('code', 'name', 'category_id');
});

test('search scope filters by name', function () {
    Product::factory()->create(['name' => 'Asus ZenBook']);
    Product::factory()->create(['name' => 'Dell XPS 13']);

    $results = Product::search('Asus')->get();

    expect($results->count())->toBeGreaterThan(0);
    expect($results->first()->name)->toContain('Asus');
});

test('search scope filters by category', function () {
    $category = Category::factory()->create(['name' => 'Laptop']);
    $otherCategory = Category::factory()->create(['name' => 'Monitor']);

    Product::factory()->create(['category_id' => $category->id]);
    Product::factory()->create(['category_id' => $otherCategory->id]);

    $results = Product::whereHas('category', function ($query) {
        $query->whereLike('name', 'Laptop');
    })
    ->get();

    expect($results->count())->toBeGreaterThan(0);
    expect($results->first()->category_id)->toBe($category->id);
});

test('search scope returns all products when keyword is empty', function () {
    Product::factory()->count(3)->create();

    $results = Product::search('')->get();

    expect($results->count())->toBe(3);
});

test('search scope filters by code', function () {
    Product::factory()->create(['code' => 'LAPTOP-001', 'name' => 'Product A']);
    Product::factory()->create(['code' => 'MONITOR-001', 'name' => 'Product B']);

    $results = Product::search('LAPTOP')->get();

    expect($results->count())->toBe(1);
    expect($results->first()->code)->toContain('LAPTOP');
});

test('byCategory scope returns all products when category_id is empty', function () {
    Product::factory()->count(3)->create();

    $results = Product::byCategory(null)->get();

    expect($results->count())->toBe(3);
});

test('byCategory scope filters by category_id', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Product::factory()->count(2)->create(['category_id' => $category1->id]);
    Product::factory()->create(['category_id' => $category2->id]);

    $results = Product::byCategory($category1->id)->get();

    expect($results->count())->toBe(2);
    expect($results->first()->category_id)->toBe($category1->id);
});
