<?php

use App\Enums\StockTransactionType;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);

// ------------------------------------------------------------
// 1. Test guarded
// ------------------------------------------------------------
test('stock transaction permits mass assignment (guarded empty)', function () {
    $transaction = new StockTransaction([
        'product_id' => 1,
        'supplier_id' => 2,
        'user_id' => 3,
        'type' => StockTransactionType::IN,
        'quantity' => 10,
    ]);

    expect($transaction->product_id)->toBe(1);
    expect($transaction->supplier_id)->toBe(2);
    expect($transaction->user_id)->toBe(3);
    expect($transaction->type)->toBe(StockTransactionType::IN);
    expect($transaction->quantity)->toBe(10);
});

// ------------------------------------------------------------
// 2. Test casts (enum casting) — No DB
// ------------------------------------------------------------
test('type attribute is cast to enum instance', function () {
    $transaction = new StockTransaction([
        'type' => 'OUT',
    ]);

    expect($transaction->type)->toBeInstanceOf(StockTransactionType::class);
    expect($transaction->type)->toBe(StockTransactionType::OUT);
});

// ------------------------------------------------------------
// 3. Test relation: product
// ------------------------------------------------------------
test('product relation returns BelongsTo instance', function () {
    $transaction = new StockTransaction();

    expect($transaction->product())
        ->toBeInstanceOf(BelongsTo::class);
});

// ------------------------------------------------------------
// 4. Test relation: supplier
// ------------------------------------------------------------
test('supplier relation returns BelongsTo instance', function () {
    $transaction = new StockTransaction();

    expect($transaction->supplier())
        ->toBeInstanceOf(BelongsTo::class);
});

// ------------------------------------------------------------
// 5. Test relation: user
// ------------------------------------------------------------
test('user relation returns BelongsTo instance', function () {
    $transaction = new StockTransaction();

    expect($transaction->user())
        ->toBeInstanceOf(BelongsTo::class);
});
