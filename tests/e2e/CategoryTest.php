<?php

use App\Models\User;
use App\Models\Category;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'user']);
    actingAs($this->user);
});

it('can display category management page', function () {
    $page = visit('/categories');

    $page->assertSee('Manajemen Kategori')
        ->assertSee('Tambah')
        ->assertSee('Kode')
        ->assertSee('Nama Kategori')
        ->assertSee('Tanggal Dibuat')
        ->assertSee('Aksi');
});

it('can display empty state when no categories exist', function () {
    $page = visit('/categories');

    $page->assertSee('Tidak ada data kategori');
});

it('can display list of categories', function () {
    Category::factory()->create([
        'code' => 'ELEC',
        'name' => 'Electronics'
    ]);

    Category::factory()->create([
        'code' => 'FURN',
        'name' => 'Furniture'
    ]);

    $page = visit('/categories');

    $page->assertSee('ELEC')
        ->assertSee('Electronics')
        ->assertSee('FURN')
        ->assertSee('Furniture');
});

it('can search categories by code', function () {
    Category::factory()->create(['code' => 'ELEC', 'name' => 'Electronics']);
    Category::factory()->create(['code' => 'FURN', 'name' => 'Furniture']);

    $page = visit('/categories');

    $page->type('search', 'ELEC')
        ->assertSee('ELEC')
        ->assertSee('Electronics')
        ->assertDontSee('FURN');
});

it('can search categories by name', function () {
    Category::factory()->create(['code' => 'ELEC', 'name' => 'Electronics']);
    Category::factory()->create(['code' => 'FURN', 'name' => 'Furniture']);

    $page = visit('/categories');

    $page->type('search', 'Furniture')
        ->assertSee('FURN')
        ->assertSee('Furniture')
        ->assertDontSee('ELEC');
});

it('can open create modal', function () {
    $page = visit('/categories');

    $page->press('Tambah')
        ->assertSee('Tambah Kategori Baru')
        ->assertSee('Kode Kategori')
        ->assertSee('Nama Kategori')
        ->assertSee('Simpan')
        ->assertSee('Batal');
});


it('can create new category', function () {
    $page = visit('/categories');

    $page->press('Tambah')
        ->type('code', 'ELEC')
        ->type('name', 'Electronics')
        ->press('Simpan')
        ->assertSee('Kategori berhasil dibuat!')
        ->assertSee('ELEC')
        ->assertSee('Electronics');

    $this->assertDatabaseHas('categories', [
        'code' => 'ELEC',
        'name' => 'Electronics'
    ]);
});


it('shows validation error when code is empty', function () {
    $page = visit('/categories');

    $page->press('Tambah')
        ->type('name', 'Electronics')
        ->press('Simpan')
        ->assertSee('code wajib diisi');
});

it('shows validation error when name is empty', function () {
    $page = visit('/categories');

    $page->press('Tambah')
        ->type('code', 'ELEC')
        ->press('Simpan')
        ->assertSee('name wajib diisi');
});

it('shows validation error when code is duplicate', function () {
    Category::factory()->create(['code' => 'ELEC']);

    $page = visit('/categories');

    $page->press('Tambah')
        ->type('code', 'ELEC')
        ->type('name', 'Electronics')
        ->press('Simpan')
        ->assertSee('code sudah ada');
});

it('can open edit modal', function () {
    $category = Category::factory()->create([
        'code' => 'ELEC',
        'name' => 'Electronics'
    ]);

    $page = visit('/categories');

    $page->press('Edit')
        ->assertSee('Edit Kategori')
        ->assertSee('ELEC')
        ->assertSee('Electronics');
});

it('can update existing category', function () {
    $category = Category::factory()->create([
        'code' => 'ELEC',
        'name' => 'Electronics'
    ]);

    $page = visit('/categories');

    $page->press('Edit')
        ->type('code', 'ELEC2')
        ->type('name', 'Electronics Updated')
        ->press('Simpan')
        ->assertSee('Kategori berhasil diupdate!')
        ->assertSee('ELEC2')
        ->assertSee('Electronics Updated');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'code' => 'ELEC2',
        'name' => 'Electronics Updated'
    ]);
});

it('can close modal by clicking cancel button', function () {
    $page = visit('/categories');

    $page->press('Tambah')
        ->assertSee('Tambah Kategori Baru')
        ->press('Batal')
        ->assertDontSee('Tambah Kategori Baru');
});

it('redirects to login when not authenticated', function () {
    auth()->logout();

    $page = visit('/categories');

    $page->assertPathIs('/login');
});

it('shows pagination when categories exceed per page limit', function () {
    Category::factory()->count(15)->create();

    $page = visit('/categories');

    $page->assertSee('2')
        ->assertSee('1');
});
