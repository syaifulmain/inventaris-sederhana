<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Supplier;
use App\Services\SupplierService;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->supplierService = app(SupplierService::class);
});

test('create supplier stores data correctly', function () {
    $supplier = $this->supplierService->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123',
    ]);

    expect($supplier->code)->toBe('SUP-001');
    expect($supplier->name)->toBe('PT. Maju Jaya');
    expect($supplier->address)->toBe('Jl. Sudirman No. 123');
    $this->assertDatabaseHas('suppliers', [
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
    ]);
});

test('update supplier modifies data correctly', function () {
    $supplier = Supplier::factory()->create(['name' => 'Old Name']);

    $updated = $this->supplierService->update($supplier->id, [
        'name' => 'New Name',
    ]);

    expect($updated->name)->toBe('New Name');
    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'New Name',
    ]);
});

test('get paginated applies search filter by code', function () {
    Supplier::factory()->create(['code' => 'SUP-001']);
    Supplier::factory()->create(['code' => 'SUP-002']);

    $result = $this->supplierService->getPaginated(['search' => 'SUP-001'], 10);

    expect($result->total())->toBe(1);
    expect($result->first()->code)->toBe('SUP-001');
});

test('get paginated applies search filter by name', function () {
    Supplier::factory()->create(['name' => 'PT. Maju Jaya']);
    Supplier::factory()->create(['name' => 'PT. Sejahtera']);

    $result = $this->supplierService->getPaginated(['search' => 'Maju'], 10);

    expect($result->total())->toBeGreaterThan(0);
    expect($result->first()->name)->toContain('Maju');
});

test('get paginated applies search filter by address', function () {
    Supplier::factory()->create(['address' => 'Jl. Sudirman']);
    Supplier::factory()->create(['address' => 'Jl. Thamrin']);

    $result = $this->supplierService->getPaginated(['search' => 'Sudirman'], 10);

    expect($result->total())->toBeGreaterThan(0);
    expect($result->first()->address)->toContain('Sudirman');
});

test('delete removes supplier from database', function () {
    $supplier = Supplier::factory()->create();

    $this->supplierService->delete($supplier->id);

    expect(Supplier::find($supplier->id))->toBeNull();
});

test('find by id returns supplier', function () {
    $supplier = Supplier::factory()->create(['code' => 'SUP-001']);

    $found = $this->supplierService->findById($supplier->id);

    expect($found->id)->toBe($supplier->id);
    expect($found->code)->toBe('SUP-001');
});
