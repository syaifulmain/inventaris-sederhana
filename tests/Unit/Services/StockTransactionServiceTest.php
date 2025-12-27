<?php

use App\Models\StockTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\StockTransactionType;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(StockTransactionService::class);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('create stock transaction stores data correctly', function () {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $data = [
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'type' => 'in',
        'quantity' => 10,
        'transaction_date' => now()->format('Y-m-d'),
        'description' => 'Test transaction',
    ];

    $transaction = $this->service->createStockTransaction($data);

    expect($transaction)->toBeInstanceOf(StockTransaction::class);
    expect($transaction->product_id)->toBe($product->id);
    expect($transaction->quantity)->toBe(10);
    expect($transaction->user_id)->toBe($this->user->id);

    $this->assertDatabaseHas('stock_transactions', [
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'quantity' => 10,
    ]);
});

test('find by id returns stock transaction', function () {
    $transaction = StockTransaction::factory()->create();

    $found = $this->service->findById($transaction->id);

    expect($found->id)->toBe($transaction->id);
});

test('throws exception when stock transaction not found', function () {
    $this->service->findById(999);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

test('update stock transaction modifies data correctly', function () {
    $transaction = StockTransaction::factory()->create([
        'quantity' => 10,
        'description' => 'Old description',
    ]);

    $updated = $this->service->updateStockTransaction($transaction->id, [
        'quantity' => 20,
        'description' => 'New description',
    ]);

    expect($updated->quantity)->toBe(20);
    expect($updated->description)->toBe('New description');

    $this->assertDatabaseHas('stock_transactions', [
        'id' => $transaction->id,
        'quantity' => 20,
        'description' => 'New description',
    ]);
});

test('delete removes stock transaction from database', function () {
    $transaction = StockTransaction::factory()->create();

    $result = $this->service->delete($transaction->id);

    expect($result)->toBeTrue();

    $this->assertDatabaseMissing('stock_transactions', [
        'id' => $transaction->id,
    ]);
});

test('get all stock transactions', function () {
    StockTransaction::factory()->count(3)->create();

    $results = $this->service->getAll();

    expect($results)->toHaveCount(3);
});

test('get paginated applies type filter', function () {
    StockTransaction::factory()->create(['type' => StockTransactionType::in]);
    StockTransaction::factory()->create(['type' => StockTransactionType::out]);

    $result = $this->service->getPaginated(['type' => 'in'], 10);

    expect($result->total())->toBe(1);
    expect($result->first()->type->value)->toBe('in');
});

test('get paginated applies date_from filter', function () {
    StockTransaction::factory()->create([
        'transaction_date' => now()->subDays(10),
    ]);
    StockTransaction::factory()->create([
        'transaction_date' => now(),
    ]);

    $result = $this->service->getPaginated([
        'date_from' => now()->subDays(5)->format('Y-m-d'),
    ], 10);

    expect($result->total())->toBe(1);
});

test('get paginated applies date_to filter', function () {
    StockTransaction::factory()->create([
        'transaction_date' => now()->subDays(10),
    ]);
    StockTransaction::factory()->create([
        'transaction_date' => now(),
    ]);

    $result = $this->service->getPaginated([
        'date_to' => now()->subDays(5)->format('Y-m-d'),
    ], 10);

    expect($result->total())->toBe(1);
});

test('get paginated searches by transaction code', function () {
    $transaction = StockTransaction::factory()->create();

    $result = $this->service->getPaginated([
        'search' => substr($transaction->transaction_code, 0, 5),
    ], 10);

    expect($result->total())->toBeGreaterThan(0);
});

test('get paginated searches by product name', function () {
    $product = Product::factory()->create(['name' => 'Laptop ASUS']);
    StockTransaction::factory()->create(['product_id' => $product->id]);
    StockTransaction::factory()->create();

    $result = $this->service->getPaginated([
        'search' => 'ASUS',
    ], 10);

    expect($result->total())->toBe(1);
});

test('get paginated searches by supplier name', function () {
    $supplier = Supplier::factory()->create(['name' => 'PT Maju Jaya']);
    StockTransaction::factory()->create(['supplier_id' => $supplier->id]);
    StockTransaction::factory()->create();

    $result = $this->service->getPaginated([
        'search' => 'Maju Jaya',
    ], 10);

    expect($result->total())->toBe(1);
});

test('get paginated orders by transaction date desc', function () {
    $old = StockTransaction::factory()->create([
        'transaction_date' => now()->subDays(5),
    ]);
    $new = StockTransaction::factory()->create([
        'transaction_date' => now(),
    ]);

    $result = $this->service->getPaginated([], 10);

    expect($result->first()->id)->toBe($new->id);
});
