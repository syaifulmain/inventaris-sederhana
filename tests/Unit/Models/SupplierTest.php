<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Supplier;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Supplier fillable attributes
 */
test('supplier has fillable attributes', function () {
    $supplier = new Supplier();

    expect($supplier->getFillable())
        ->toContain('code', 'name', 'address');
});

/**
 * Supplier factory creates correct data
 */
test('supplier factory creates a valid supplier', function () {
    $supplier = Supplier::factory()->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    expect($supplier->code)->toBe('SUP-001');
    expect($supplier->name)->toBe('PT. Maju Jaya');
    expect($supplier->address)->toBe('Jl. Sudirman No. 123, Jakarta Pusat');

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);
});

/**
 * Update supplier
 */
test('supplier can be updated', function () {
    $supplier = Supplier::factory()->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    $supplier->update([
        'name' => 'PT. Maju Jaya Sejahtera',
    ]);

    expect($supplier->fresh()->name)->toBe('PT. Maju Jaya Sejahtera');

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'PT. Maju Jaya Sejahtera',
    ]);
});

/**
 * Delete supplier
 */
test('supplier can be deleted', function () {
    $supplier = Supplier::factory()->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    $supplier->delete();

    $this->assertDatabaseMissing('suppliers', [
        'id' => $supplier->id,
    ]);
});
