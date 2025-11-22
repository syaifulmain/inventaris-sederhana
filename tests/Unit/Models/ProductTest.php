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
