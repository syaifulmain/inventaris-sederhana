<?php

use App\Models\StockTransaction;
use App\Services\StockTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\StockTransactionType;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(StockTransactionService::class);
});

it('can create stock transaction', function () {
    $data = [
        'product_id'  => 1,
        'supplier_id' => 1,
        'code'        => 'TRX-001',
        'name'        => 'Stock In',
        'type'        => StockTransactionType::IN,
        'quantity'    => 10,
    ];

    $transaction = $this->service->create($data);

    expect($transaction)
        ->toBeInstanceOf(StockTransaction::class)
        ->and($transaction->code)->toBe('TRX-001');

    $this->assertDatabaseHas('stock_transactions', [
        'code' => 'TRX-001',
    ]);
});

it('can find stock transaction by id', function () {
    $transaction = StockTransaction::factory()->create();

    $found = $this->service->findById($transaction->id);

    expect($found->id)->toBe($transaction->id);
});

it('throws exception when stock transaction not found', function () {
    $this->service->findById(999);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('can update stock transaction', function () {
    $transaction = StockTransaction::factory()->create([
        'name' => 'Old Name',
    ]);

    $updated = $this->service->update($transaction->id, [
        'name' => 'New Name',
    ]);

    expect($updated->name)->toBe('New Name');

    $this->assertDatabaseHas('stock_transactions', [
        'id'   => $transaction->id,
        'name' => 'New Name',
    ]);
});

it('can delete stock transaction', function () {
    $transaction = StockTransaction::factory()->create();

    $result = $this->service->delete($transaction->id);

    expect($result)->toBeTrue();

    $this->assertDatabaseMissing('stock_transactions', [
        'id' => $transaction->id,
    ]);
});

it('can get all stock transactions', function () {
    StockTransaction::factory()->count(3)->create();

    $results = $this->service->getAll();

    expect($results)->toHaveCount(3);
});

it('can filter by product_id', function () {
    StockTransaction::factory()->create(['product_id' => 1]);
    StockTransaction::factory()->create(['product_id' => 2]);

    $results = $this->service->getAll([
        'product_id' => 1,
    ]);

    expect($results)->toHaveCount(1)
        ->and($results->first()->product_id)->toBe(1);
});

it('can filter by supplier_id', function () {
    StockTransaction::factory()->create(['supplier_id' => 10]);
    StockTransaction::factory()->create(['supplier_id' => 20]);

    $results = $this->service->getAll([
        'supplier_id' => 10,
    ]);

    expect($results)->toHaveCount(1)
        ->and($results->first()->supplier_id)->toBe(10);
});

it('can filter by type', function () {
    StockTransaction::factory()->create([
        'type' => StockTransactionType::IN,
    ]);

    StockTransaction::factory()->create([
        'type' => StockTransactionType::OUT,
    ]);

    $results = $this->service->getAll([
        'type' => StockTransactionType::IN,
    ]);

    expect($results)->toHaveCount(1)
        ->and($results->first()->type)->toBe(StockTransactionType::IN);
});

it('can search by code or name', function () {
    StockTransaction::factory()->create([
        'code' => 'ABC-123',
        'name' => 'Transaction Alpha',
    ]);

    StockTransaction::factory()->create([
        'code' => 'XYZ-999',
        'name' => 'Transaction Beta',
    ]);

    $results = $this->service->getAll([
        'search' => 'ABC',
    ]);

    expect($results)->toHaveCount(1)
        ->and($results->first()->code)->toBe('ABC-123');
});

it('can paginate stock transactions', function () {
    StockTransaction::factory()->count(15)->create();

    $paginated = $this->service->getPaginated([], 10);

    expect($paginated->count())->toBe(10)
        ->and($paginated->total())->toBe(15);
});
