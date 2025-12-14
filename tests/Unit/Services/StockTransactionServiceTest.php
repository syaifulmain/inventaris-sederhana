<?php

use App\Models\StockTransaction;
use App\Services\StockTransactionService;
use App\Enums\StockTransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Mockery;

afterEach(function () {
    Mockery::close();
});

test('returns all stock transactions', function () {
    $model = Mockery::mock(StockTransaction::class);
    $query = Mockery::mock(Builder::class);

    $model->shouldReceive('query')
        ->once()
        ->andReturn($query);

    $query->shouldReceive('orderBy')
        ->with('created_at', 'asc')
        ->andReturnSelf();

    $query->shouldReceive('get')
        ->once()
        ->andReturn(collect(['trx1', 'trx2']));

    $service = new StockTransactionService($model);

    $result = $service->getAll();

    expect($result)->toHaveCount(2);
});

test('applies filters correctly', function () {
    $model = Mockery::mock(StockTransaction::class);
    $query = Mockery::mock(Builder::class);

    $model->shouldReceive('query')->once()->andReturn($query);

    $query->shouldReceive('where')
        ->with('product_id', 1)
        ->once()
        ->andReturnSelf();

    $query->shouldReceive('where')
        ->with('type', StockTransactionType::IN)
        ->once()
        ->andReturnSelf();

    $query->shouldReceive('orderBy')
        ->with('created_at', 'asc')
        ->once()
        ->andReturnSelf();

    $query->shouldReceive('get')
        ->once()
        ->andReturn(collect());

    $service = new StockTransactionService($model);

    $service->getAll([
        'product_id' => 1,
        'type'       => StockTransactionType::IN,
    ]);
});


test('finds stock transaction by id', function () {
    $model = Mockery::mock(StockTransaction::class);

    $model->shouldReceive('findOrFail')
        ->with(1)
        ->once()
        ->andReturn('transaction');

    $service = new StockTransactionService($model);

    expect($service->findById(1))->toBe('transaction');
});


test('throws exception when stock transaction not found', function () {
    $model = Mockery::mock(StockTransaction::class);

    $model->shouldReceive('findOrFail')
        ->with(999)
        ->andThrow(ModelNotFoundException::class);

    $service = new StockTransactionService($model);

    $service->findById(999);
})->throws(ModelNotFoundException::class);


test('creates stock transaction with transaction handling', function () {
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('commit')->once();
    DB::shouldReceive('rollBack')->never();

    $model = Mockery::mock(StockTransaction::class);

    $model->shouldReceive('create')
        ->with([
            'code' => 'TRX-001',
        ])
        ->once()
        ->andReturn('created-transaction');

    $service = new StockTransactionService($model);

    $result = $service->create([
        'code' => 'TRX-001',
    ]);

    expect($result)->toBe('created-transaction');
});


test('updates stock transaction', function () {
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('commit')->once();
    DB::shouldReceive('rollBack')->never();

    $record = Mockery::mock();
    $record->shouldReceive('update')
        ->with(['name' => 'Updated'])
        ->once();

    $record->shouldReceive('fresh')
        ->once()
        ->andReturn('updated-transaction');

    $model = Mockery::mock(StockTransaction::class);
    $model->shouldReceive('findOrFail')
        ->with(1)
        ->once()
        ->andReturn($record);

    $service = new StockTransactionService($model);

    $result = $service->update(1, ['name' => 'Updated']);

    expect($result)->toBe('updated-transaction');
});


test('deletes stock transaction', function () {
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('commit')->once();
    DB::shouldReceive('rollBack')->never();

    $record = Mockery::mock();
    $record->shouldReceive('delete')->once();

    $model = Mockery::mock(StockTransaction::class);
    $model->shouldReceive('findOrFail')
        ->with(1)
        ->once()
        ->andReturn($record);

    $service = new StockTransactionService($model);

    expect($service->delete(1))->toBeTrue();
});
