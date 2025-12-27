<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Supplier;

uses(TestCase::class, RefreshDatabase::class);

test('supplier has fillable attributes', function () {
    $supplier = new Supplier();

    expect($supplier->getFillable())->toContain('code', 'name', 'address');
});

test('search scope filters by code', function () {
    Supplier::factory()->create(['code' => 'SUP-001']);
    Supplier::factory()->create(['code' => 'SUP-002']);

    $results = Supplier::search('SUP-001')->get();

    expect($results->count())->toBeGreaterThan(0);
    expect($results->first()->code)->toBe('SUP-001');
});

test('search scope filters by name', function () {
    Supplier::factory()->create(['name' => 'PT. Maju Jaya']);
    Supplier::factory()->create(['name' => 'PT. Sejahtera']);

    $results = Supplier::search('Maju')->get();

    expect($results->count())->toBeGreaterThan(0);
    expect($results->first()->name)->toContain('Maju');
});

test('search scope filters by address', function () {
    Supplier::factory()->create(['address' => 'Jl. Sudirman']);
    Supplier::factory()->create(['address' => 'Jl. Thamrin']);

    $results = Supplier::search('Sudirman')->get();

    expect($results->count())->toBeGreaterThan(0);
    expect($results->first()->address)->toContain('Sudirman');
});

test('supplier code must be unique', function () {
    Supplier::factory()->create(['code' => 'SUP-001']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Supplier::factory()->create(['code' => 'SUP-001']);
});

test('search scope returns all suppliers when keyword is empty', function () {
    Supplier::factory()->count(3)->create();

    $results = Supplier::search('')->get();

    expect($results->count())->toBe(3);
});
