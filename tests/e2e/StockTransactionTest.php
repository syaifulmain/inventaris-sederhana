<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockTransaction;
use Illuminate\Support\Carbon;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('can display stock transaction page', function () {
    $page = visit('/stocks');

    $page->assertSee('Tambah Transaksi')
        ->assertSee('Semua Transaksi')
        ->assertSee('Stok Masuk')
        ->assertSee('Stok Keluar')
        ->assertSee('Kode Transaksi')
        ->assertSee('Tanggal')
        ->assertSee('Produk')
        ->assertSee('Supplier')
        ->assertSee('Tipe')
        ->assertSee('Kuantitas')
        ->assertSee('Aksi');
});

it('shows empty state when no stock transactions exist', function () {
    $page = visit('/stocks');

    $page->assertSee('Tidak ada transaksi stok')
        ->assertSee('Silakan tambah transaksi stok baru');
});

it('can display list of stock transactions', function () {
    $product = Product::factory()->create(['name' => 'Laptop', 'code' => 'PRD001']);
    $supplier = Supplier::factory()->create(['name' => 'Tech Supplier']);

    StockTransaction::factory()->create([
        'transaction_code' => 'TRX001',
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'type' => 'in',
        'quantity' => 10,
        'transaction_date' => now(),
        'user_id' => $this->user->id,
    ]);

    $page = visit('/stocks');

    $page->assertSee('TRX001')
        ->assertSee('Laptop')
        ->assertSee('PRD001')
        ->assertSee('Tech Supplier')
        ->assertSee('Stok Masuk')
        ->assertSee('+10');
});

// it('can filter stock transactions by type in', function () {
//     $product = Product::factory()->create();
//     $supplier = Supplier::factory()->create();

//     StockTransaction::factory()->create([
//         'type' => 'in',
//         'quantity' => 5,
//         'product_id' => $product->id,
//         'supplier_id' => $supplier->id,
//     ]);

//     StockTransaction::factory()->create([
//         'type' => 'out',
//         'quantity' => 3,
//         'product_id' => $product->id,
//         'supplier_id' => $supplier->id,
//     ]);

//     $page = visit('/stocks');

//     $page->press('Stok Masuk')
//     //  ->waitForText('Stok Masuk')
//      ->assertSeeIn('@table', 'Stok Masuk')
//      ->assertDontSeeIn('@table', 'Stok Keluar');
// });

it('can search stock transaction by product name', function () {
    $product = Product::factory()->create(['name' => 'Laptop']);
    $supplier = Supplier::factory()->create();

    StockTransaction::factory()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
    ]);

    $page = visit('/stocks');

    $page->type('search', 'Laptop')
        ->assertSee('Laptop');
});

it('can open create stock transaction modal', function () {
    Product::factory()->create();
    Supplier::factory()->create();

    $page = visit('/stocks');

    $page->press('Tambah Transaksi')
        ->assertSee('Tambah Stok Baru')
        ->assertSee('Produk')
        ->assertSee('Supplier')
        ->assertSee('Tipe Transaksi')
        ->assertSee('Kuantitas')
        ->assertSee('Tanggal Transaksi')
        ->assertSee('Simpan')
        ->assertSee('Batal');
});

it('can create stock in transaction', function () {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $page = visit('/stocks');


    $page->press('Tambah Transaksi')
        ->select('product_id', $product->id)
        ->select('supplier_id', $supplier->id)
        ->select(
            'type',
            'in'
        )

        ->type('quantity', "10")
        ->type('transaction_date', now()->format('Y-m-d'))
        ->press('Simpan')
        ->assertSee('berhasil');

    $this->assertDatabaseHas('stock_transactions', [
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'quantity' => 10,
        'type' => 'in',
    ]);
});

it('shows validation error when required fields are empty', function () {
    $page = visit('/stocks');

    $page->press('Tambah Transaksi')
        ->press('Simpan')
        ->assertSee('wajib');
});

it('can open edit stock transaction modal', function () {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    $trx = StockTransaction::factory()->create([
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
    ]);

    $page = visit('/stocks');

    $page->press('Edit')
        ->assertSee('Edit Stok')
        ->assertSee($product->name);
});


it('can close modal by clicking cancel button', function () {
    Product::factory()->create();
    Supplier::factory()->create();

    $page = visit('/stocks');

    $page->press('Tambah Transaksi')
        ->assertSee('Tambah Stok Baru')
        ->press('Batal')
        ->assertDontSee('Tambah Stok Baru');
});

it('redirects to login when not authenticated', function () {
    auth()->logout();

    $page = visit('/stocks');

    $page->assertPathIs('/login');
});

it('shows pagination when stock transactions exceed per page limit', function () {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();

    StockTransaction::factory()
        ->count(15)
        ->create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
        ]);

    $page = visit('/stocks');

    $page->assertSee('1')
        ->assertSee('2');
});
