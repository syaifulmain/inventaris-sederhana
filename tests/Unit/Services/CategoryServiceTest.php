<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use App\Services\CategoryService;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->categoryService = app(CategoryService::class);
});

test('create category stores data correctly', function () {
    $category = $this->categoryService->create([
        'code' => 'ELEC',
        'name' => 'Electronics',
    ]);

    expect($category->code)->toBe('ELEC');
    expect($category->name)->toBe('Electronics');
    $this->assertDatabaseHas('categories', [
        'code' => 'ELEC',
        'name' => 'Electronics',
    ]);
});

test('update category modifies data correctly', function () {
    $category = Category::factory()->create(['name' => 'Old Name']);

    $updated = $this->categoryService->update($category->id, [
        'name' => 'New Name',
    ]);

    expect($updated->name)->toBe('New Name');
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'New Name',
    ]);
});

test('get all returns all categories', function () {
    Category::factory()->count(5)->create();

    $categories = $this->categoryService->getAll();

    expect($categories)->toHaveCount(5);
});

test('get paginated applies search filter by code', function () {
    Category::factory()->create(['code' => 'ELEC', 'name' => 'Electronics']);
    Category::factory()->create(['code' => 'FASH', 'name' => 'Fashion']);

    $result = $this->categoryService->getPaginated(['search' => 'ELEC'], 10);

    expect($result->total())->toBe(1);
    expect($result->first()->code)->toBe('ELEC');
});

test('get paginated applies search filter by name', function () {
    Category::factory()->create(['code' => 'ELEC', 'name' => 'Electronics']);
    Category::factory()->create(['code' => 'FASH', 'name' => 'Fashion']);

    $result = $this->categoryService->getPaginated(['search' => 'Fashion'], 10);

    expect($result->total())->toBe(1);
    expect($result->first()->name)->toBe('Fashion');
});

test('get paginated returns ordered by created_at desc', function () {
    $oldCategory = Category::factory()->create(['created_at' => now()->subDays(2)]);
    $newCategory = Category::factory()->create(['created_at' => now()]);

    $result = $this->categoryService->getPaginated([], 10);

    expect($result->first()->id)->toBe($newCategory->id);
});

test('delete removes category from database', function () {
    $category = Category::factory()->create();

    $this->categoryService->delete($category->id);

    expect(Category::find($category->id))->toBeNull();
});

test('find by id returns category', function () {
    $category = Category::factory()->create(['name' => 'Electronics']);

    $found = $this->categoryService->findById($category->id);

    expect($found->id)->toBe($category->id);
    expect($found->name)->toBe('Electronics');
});

test('find by id throws exception for non-existent category', function () {
    $this->categoryService->findById(99999);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
