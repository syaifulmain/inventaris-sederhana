<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\SupplierService;
use App\Models\Supplier;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->supplierService = app(SupplierService::class);
});

/**
 * Create Supplier
 */
test('create supplier stores correct data', function () {
    $supplier = $this->supplierService->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    expect($supplier->code)->toBe('SUP-001');
    expect($supplier->name)->toBe('PT. Maju Jaya');
    expect($supplier->address)->toBe('Jl. Sudirman No. 123, Jakarta Pusat');
});

/**
 * Update Supplier
 */
test('update supplier modifies only provided fields', function () {
    $supplier = Supplier::factory()->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    $updated = $this->supplierService->update($supplier, [
        'name' => 'PT. Maju Jaya Sejahtera',
    ]);

    expect($updated->name)->toBe('PT. Maju Jaya Sejahtera');
    expect($updated->address)->toBe('Jl. Sudirman No. 123, Jakarta Pusat'); // tetap sama
});

/**
 * Update throws TypeError (karena SupplierService::update butuh model Supplier)
 */
test('update throws exception when supplier not found', function () {
    $this->expectException(\TypeError::class);

    $this->supplierService->update(99999, [
        'name' => 'PT. Tidak Ada'
    ]);
});

/**
 * list() search test
 */
test('list applies search filter on name, code, and address', function () {
    Supplier::factory()->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    Supplier::factory()->create([
        'code' => 'SUP-002',
        'name' => 'PT. Sejahtera',
        'address' => 'Jl. Thamrin No. 456, Jakarta Pusat',
    ]);

    // Search by name
    $result1 = $this->supplierService->list('Maju', 10, 1);
    expect($result1->total())->toBeGreaterThan(0);
    expect($result1->first()->name)->toBe('PT. Maju Jaya');

    // Search by code
    $result2 = $this->supplierService->list('SUP-002', 10, 1);
    expect($result2->first()->code)->toBe('SUP-002');

    // Search by address
    $result3 = $this->supplierService->list('Thamrin', 10, 1);
    expect($result3->first()->address)->toBe('Jl. Thamrin No. 456, Jakarta Pusat');
});

/**
 * Delete Supplier
 */
test('delete removes supplier from database', function () {
    $supplier = Supplier::factory()->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    $this->supplierService->delete($supplier);

    expect(Supplier::find($supplier->id))->toBeNull();
});

/**
 * delete throws TypeError (karena delete() butuh model Supplier)
 */
test('delete throws exception when supplier does not exist', function () {
    $this->expectException(\TypeError::class);

    $this->supplierService->delete(99999); 
});
