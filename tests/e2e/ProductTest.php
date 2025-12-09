<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'user']);
    actingAs($this->user);
});

it('can display product management page', function () {
    $page = visit('/products');

    $page->assertSee('Manajemen Produk')
        ->assertSee('Tambah')
        ->assertSee('Kode')
        ->assertSee('Nama Produk')
        ->assertSee('Kategori')
        ->assertSee('Tanggal Dibuat')
        ->assertSee('Aksi');
});

it('can display empty state when no products exist', function () {
    $page = visit('/products');

    $page->assertSee('Tidak ada data produk');
});

it('can display list of products', function () {
    $category = Category::factory()->create(['name' => 'Electronics']);

    Product::factory()->create([
        'code' => 'PROD001',
        'name' => 'Laptop',
        'category_id' => $category->id
    ]);

    Product::factory()->create([
        'code' => 'PROD002',
        'name' => 'Mouse',
        'category_id' => $category->id
    ]);

    $page = visit('/products');

    $page->assertSee('PROD001')
        ->assertSee('Laptop')
        ->assertSee('PROD002')
        ->assertSee('Mouse')
        ->assertSee('Electronics');
});

it('can search products by code', function () {
    $category = Category::factory()->create();

    Product::factory()->create(['code' => 'PROD001', 'name' => 'Laptop', 'category_id' => $category->id]);
    Product::factory()->create(['code' => 'PROD002', 'name' => 'Mouse', 'category_id' => $category->id]);

    $page = visit('/products');

    $page->type('search', 'PROD001')
        ->assertSee('PROD001')
        ->assertSee('Laptop')
        ->assertDontSee('PROD002');
});

it('can search products by name', function () {
    $category = Category::factory()->create();

    Product::factory()->create(['code' => 'PROD001', 'name' => 'Laptop', 'category_id' => $category->id]);
    Product::factory()->create(['code' => 'PROD002', 'name' => 'Mouse', 'category_id' => $category->id]);

    $page = visit('/products');

    $page->type('search', 'Mouse')
        ->assertSee('PROD002')
        ->assertSee('Mouse')
        ->assertDontSee('Laptop');
});

it('can filter products by category', function () {
    $electronics = Category::factory()->create(['name' => 'Electronics']);
    $furniture = Category::factory()->create(['name' => 'Furniture']);

    Product::factory()->create(['code' => 'PROD001', 'name' => 'Laptop', 'category_id' => $electronics->id]);
    Product::factory()->create(['code' => 'PROD002', 'name' => 'Chair', 'category_id' => $furniture->id]);

    $page = visit('/products');

    $page->select('categoryFilter', $electronics->id)
        ->assertSee('Laptop')
        ->assertDontSee('Chair');
});

it('can open create modal', function () {
    Category::factory()->create();

    $page = visit('/products');

    $page->press('Tambah')
        ->assertSee('Tambah Produk Baru')
        ->assertSee('Kode Produk')
        ->assertSee('Nama Produk')
        ->assertSee('Kategori')
        ->assertSee('Simpan')
        ->assertSee('Batal');
});

it('can create new product', function () {
    $category = Category::factory()->create(['name' => 'Electronics']);

    $page = visit('/products');

    $page->press('Tambah')
        ->type('code', 'PROD001')
        ->type('name', 'Laptop')
        ->select('category_id', $category->id)
        ->press('Simpan')
        ->assertSee('Produk berhasil dibuat!')
        ->assertSee('PROD001')
        ->assertSee('Laptop');

    $this->assertDatabaseHas('products', [
        'code' => 'PROD001',
        'name' => 'Laptop',
        'category_id' => $category->id
    ]);
});

it('shows validation error when code is empty', function () {
    Category::factory()->create();

    $page = visit('/products');

    $page->press('Tambah')
        ->type('name', 'Laptop')
        ->press('Simpan')
        ->assertSee('code wajib diisi');
});

it('shows validation error when name is empty', function () {
    Category::factory()->create();

    $page = visit('/products');

    $page->press('Tambah')
        ->type('code', 'PROD001')
        ->press('Simpan')
        ->assertSee('name wajib diisi');
});

it('shows validation error when category is empty', function () {
    Category::factory()->create();

    $page = visit('/products');

    $page->press('Tambah')
        ->type('code', 'PROD001')
        ->type('name', 'Laptop')
        ->press('Simpan')
        ->assertSee('category id wajib diisi');
});

it('shows validation error when code is duplicate', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['code' => 'PROD001', 'category_id' => $category->id]);

    $page = visit('/products');

    $page->press('Tambah')
        ->type('code', 'PROD001')
        ->type('name', 'Laptop')
        ->select('category_id', $category->id)
        ->press('Simpan')
        ->assertSee('code sudah ada');
});

it('can open edit modal', function () {
    $category = Category::factory()->create(['name' => 'Electronics']);
    $product = Product::factory()->create([
        'code' => 'PROD001',
        'name' => 'Laptop',
        'category_id' => $category->id
    ]);

    $page = visit('/products');

    $page->press('Edit')
        ->assertSee('Edit Produk')
        ->assertSee('PROD001')
        ->assertSee('Laptop');
});

it('can update existing product', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'code' => 'PROD001',
        'name' => 'Laptop',
        'category_id' => $category->id
    ]);

    $page = visit('/products');

    $page->press('Edit')
        ->type('code', 'PROD002')
        ->type('name', 'Laptop Updated')
        ->press('Simpan')
        ->assertSee('Produk berhasil diupdate!')
        ->assertSee('PROD002')
        ->assertSee('Laptop Updated');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'code' => 'PROD002',
        'name' => 'Laptop Updated'
    ]);
});

//it('can delete product', function () {
//    $category = Category::factory()->create();
//    $product = Product::factory()->create([
//        'code' => 'PROD001',
//        'name' => 'Laptop',
//        'category_id' => $category->id
//    ]);
//
//    $page = visit('/products');
//
//    $page->press('Hapus')
//        ->assertSee('Produk berhasil dihapus!');
//
//    $this->assertDatabaseMissing('products', [
//        'id' => $product->id
//    ]);
//});

it('can close modal by clicking cancel button', function () {
    Category::factory()->create();

    $page = visit('/products');

    $page->press('Tambah')
        ->assertSee('Tambah Produk Baru')
        ->press('Batal')
        ->assertDontSee('Tambah Produk Baru');
});

it('redirects to login when not authenticated', function () {
    auth()->logout();

    $page = visit('/products');

    $page->assertPathIs('/login');
});

it('shows pagination when products exceed per page limit', function () {
    $category = Category::factory()->create();
    Product::factory()->count(15)->create(['category_id' => $category->id]);

    $page = visit('/products');

    $page->assertSee('2')
        ->assertSee('1');
});
