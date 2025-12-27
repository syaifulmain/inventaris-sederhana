<?php

use App\Enums\StockTransactionType;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('stock transaction permits mass assignment (guarded empty)', function () {
    $transaction = new StockTransaction([
        'product_id' => 1,
        'supplier_id' => 2,
        'user_id' => 3,
        'type' => StockTransactionType::in,
        'quantity' => 10,
        'transaction_date' => now(),
    ]);

    expect($transaction->product_id)->toBe(1);
    expect($transaction->supplier_id)->toBe(2);
    expect($transaction->user_id)->toBe(3);
    expect($transaction->type)->toBe(StockTransactionType::in);
    expect($transaction->quantity)->toBe(10);
});

test('type attribute is cast to enum instance', function () {
    $transaction = new StockTransaction([
        'type' => 'out',
    ]);

    expect($transaction->type)->toBeInstanceOf(StockTransactionType::class);
    expect($transaction->type)->toBe(StockTransactionType::out);
});

test('transaction_date is cast to date', function () {
    $transaction = new StockTransaction([
        'transaction_date' => '2025-12-24',
    ]);

    expect($transaction->transaction_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('product relation returns BelongsTo instance', function () {
    $transaction = new StockTransaction();

    expect($transaction->product())
        ->toBeInstanceOf(BelongsTo::class);
});

test('supplier relation returns BelongsTo instance', function () {
    $transaction = new StockTransaction();

    expect($transaction->supplier())
        ->toBeInstanceOf(BelongsTo::class);
});

test('user relation returns BelongsTo instance', function () {
    $transaction = new StockTransaction();

    expect($transaction->user())
        ->toBeInstanceOf(BelongsTo::class);
});

test('transaction code is auto generated on creation', function () {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $transaction = StockTransaction::create([
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
        'type' => StockTransactionType::in,
        'quantity' => 10,
        'transaction_date' => now(),
    ]);

    expect($transaction->transaction_code)->not->toBeNull();
    expect($transaction->transaction_code)->toStartWith('TR');
});

test('transaction code format is correct', function () {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $transaction = StockTransaction::create([
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
        'type' => StockTransactionType::out,
        'quantity' => 5,
        'transaction_date' => now(),
    ]);

    // Format: TR + YYYYMMDD + 0001
    expect($transaction->transaction_code)->toMatch('/^TR\d{8}\d{4}$/');
});
