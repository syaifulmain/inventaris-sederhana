<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockTransaction;
use App\Enums\StockTransactionType;

test('admin can view list of stock transactions', function () {
    $admin = User::factory()->admin()->create();
    StockTransaction::factory()->count(15)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/stock-transactions');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'data',
                'current_page',
                'per_page',
                'total',
            ],
            'errors',
        ]);

    expect($response->json('data.per_page'))->toBe(10);
});

test('admin can search stock transactions by transaction code', function () {
    $admin = User::factory()->admin()->create();
    $transaction1 = StockTransaction::factory()->create();
    $transaction2 = StockTransaction::factory()->create();

    $searchTerm = substr($transaction1->transaction_code, 0, 8);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/stock-transactions?search={$searchTerm}");

    $response->assertStatus(200);

    $transactions = $response->json('data.data');
    expect(count($transactions))->toBeGreaterThan(0);
});

test('admin can search stock transactions by product name', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => 'Laptop ASUS ROG']);
    StockTransaction::factory()->create(['product_id' => $product->id]);
    StockTransaction::factory()->count(2)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/stock-transactions?search=ASUS');

    $response->assertStatus(200);

    $transactions = $response->json('data.data');
    expect(count($transactions))->toBe(1);
});

test('admin can search stock transactions by supplier name', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create(['name' => 'PT Maju Jaya']);
    StockTransaction::factory()->create(['supplier_id' => $supplier->id]);
    StockTransaction::factory()->count(2)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/stock-transactions?search=Maju');

    $response->assertStatus(200);

    $transactions = $response->json('data.data');
    expect(count($transactions))->toBe(1);
});

test('admin can filter stock transactions by type', function () {
    $admin = User::factory()->admin()->create();
    StockTransaction::factory()->count(3)->create(['type' => StockTransactionType::in]);
    StockTransaction::factory()->count(2)->create(['type' => StockTransactionType::out]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/stock-transactions?type=in');

    $response->assertStatus(200);
    expect($response->json('data.total'))->toBe(3);
});

test('admin can change pagination per page', function () {
    $admin = User::factory()->admin()->create();
    StockTransaction::factory()->count(25)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/stock-transactions?per_page=5');

    $response->assertStatus(200);
    expect($response->json('data.per_page'))->toBe(5);
});

test('admin can create new stock transaction', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/stock-transactions', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'type' => 'in',
            'quantity' => 100,
            'description' => 'Initial stock',
            'transaction_date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 201,
            'message' => 'Transaksi stok berhasil dibuat',
        ]);

    $this->assertDatabaseHas('stock_transactions', [
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'quantity' => 100,
    ]);
});

test('admin can view specific stock transaction', function () {
    $admin = User::factory()->admin()->create();
    $transaction = StockTransaction::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/stock-transactions/{$transaction->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'data' => [
                'id' => $transaction->id,
                'quantity' => $transaction->quantity,
            ],
        ]);
});

test('admin can update stock transaction', function () {
    $admin = User::factory()->admin()->create();
    $transaction = StockTransaction::factory()->create(['quantity' => 10]);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/stock-transactions/{$transaction->id}", [
            'product_id' => $transaction->product_id,
            'supplier_id' => $transaction->supplier_id,
            'type' => $transaction->type->value,
            'quantity' => 20,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Transaksi stok berhasil diperbarui',
        ]);

    $transaction->refresh();
    expect($transaction->quantity)->toBe(20);
});

test('admin can delete stock transaction', function () {
    $admin = User::factory()->admin()->create();
    $transaction = StockTransaction::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/stock-transactions/{$transaction->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Transaksi stok berhasil dihapus',
        ]);

    $this->assertDatabaseMissing('stock_transactions', [
        'id' => $transaction->id,
    ]);
});

test('admin cannot view non-existent stock transaction', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/stock-transactions/99999');

    // Laravel route model binding throws 404 before reaching controller
    $response->assertStatus(404);
});

test('admin cannot update non-existent stock transaction', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson('/api/stock-transactions/99999', [
            'quantity' => 20,
        ]);

    $response->assertStatus(404);
});

test('admin cannot delete non-existent stock transaction', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson('/api/stock-transactions/99999');

    $response->assertStatus(404);
});

test('create stock transaction validation fails with missing required fields', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/stock-transactions', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'product_id',
            'supplier_id',
            'type',
            'quantity',
            'transaction_date',
        ]);
});

test('create stock transaction fails with invalid product_id', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/stock-transactions', [
            'product_id' => 99999,
            'supplier_id' => $supplier->id,
            'type' => 'in',
            'quantity' => 10,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);
});

test('create stock transaction fails with invalid supplier_id', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/stock-transactions', [
            'product_id' => $product->id,
            'supplier_id' => 99999,
            'type' => 'in',
            'quantity' => 10,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['supplier_id']);
});

test('create stock transaction fails with invalid type', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/stock-transactions', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'type' => 'invalid',
            'quantity' => 10,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

test('create stock transaction fails with negative quantity', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/stock-transactions', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'type' => 'in',
            'quantity' => -10,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['quantity']);
});

test('create stock transaction fails with zero quantity', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/stock-transactions', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'type' => 'in',
            'quantity' => 0,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['quantity']);
});

test('index returns error when service throws exception', function () {
    $admin = User::factory()->admin()->create();

    $mock = Mockery::mock(\App\Services\StockTransactionService::class);
    $mock->shouldReceive('getPaginated')
        ->once()
        ->andThrow(new Exception('Service error'));
    $this->app->instance(\App\Services\StockTransactionService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/stock-transactions');

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal mengambil data transaksi stok',
            'errors' => ['message' => 'Service error'],
        ]);
});

test('store returns error when service throws exception', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $mock = Mockery::mock(\App\Services\StockTransactionService::class);
    $mock->shouldReceive('createStockTransaction')
        ->once()
        ->andThrow(new Exception('Create error'));
    $this->app->instance(\App\Services\StockTransactionService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/stock-transactions', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'type' => 'in',
            'quantity' => 10,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal membuat transaksi stok',
            'errors' => ['message' => 'Create error'],
        ]);
});

test('update returns error when service throws generic exception', function () {
    $admin = User::factory()->admin()->create();
    $transaction = StockTransaction::factory()->create();

    $mock = Mockery::mock(\App\Services\StockTransactionService::class);
    $mock->shouldReceive('updateStockTransaction')
        ->once()
        ->andThrow(new Exception('Update error'));
    $this->app->instance(\App\Services\StockTransactionService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/stock-transactions/{$transaction->id}", [
            'product_id' => $transaction->product_id,
            'supplier_id' => $transaction->supplier_id,
            'type' => $transaction->type->value,
            'quantity' => 20,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
        ]);

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal mengupdate transaksi stok',
            'errors' => ['message' => 'Update error'],
        ]);
});

test('delete returns error when service throws generic exception', function () {
    $admin = User::factory()->admin()->create();
    $transaction = StockTransaction::factory()->create();

    $mock = Mockery::mock(\App\Services\StockTransactionService::class);
    $mock->shouldReceive('delete')
        ->once()
        ->andThrow(new Exception('Delete error'));
    $this->app->instance(\App\Services\StockTransactionService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/stock-transactions/{$transaction->id}");

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal menghapus transaksi stok',
            'errors' => ['message' => 'Delete error'],
        ]);
});
