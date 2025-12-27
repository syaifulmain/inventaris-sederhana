<?php

use App\Models\User;
use App\Models\Supplier;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'user']);
    actingAs($this->user);
});

it('can display supplier management page', function () {
    $page = visit('/suppliers');

    $page->assertSee('Manajemen Supplier')
        ->assertSee('Tambah')
        ->assertSee('Kode')
        ->assertSee('Nama Supplier')
        ->assertSee('Alamat')
        ->assertSee('Tanggal Dibuat')
        ->assertSee('Aksi');
});

it('can display empty state when no suppliers exist', function () {
    $page = visit('/suppliers');

    $page->assertSee('Tidak ada data supplier');
});

it('can display list of suppliers', function () {
    Supplier::factory()->create([
        'code' => 'SUP001',
        'name' => 'PT Supplier Utama',
        'address' => 'Jl. Supplier No. 1'
    ]);

    Supplier::factory()->create([
        'code' => 'SUP002',
        'name' => 'CV Mitra Supplier',
        'address' => 'Jl. Supplier No. 2'
    ]);

    $page = visit('/suppliers');

    $page->assertSee('SUP001')
        ->assertSee('PT Supplier Utama')
        ->assertSee('Jl. Supplier No. 1')
        ->assertSee('SUP002')
        ->assertSee('CV Mitra Supplier')
        ->assertSee('Jl. Supplier No. 2');
});

it('can search suppliers by code', function () {
    Supplier::factory()->create(['code' => 'SUP001', 'name' => 'PT Supplier Utama']);
    Supplier::factory()->create(['code' => 'SUP002', 'name' => 'CV Mitra Supplier']);

    $page = visit('/suppliers');

    $page->type('search', 'SUP001')
        ->assertSee('SUP001')
        ->assertSee('PT Supplier Utama')
        ->assertDontSee('SUP002');
});

it('can search suppliers by name', function () {
    Supplier::factory()->create(['code' => 'SUP001', 'name' => 'PT Supplier Utama']);
    Supplier::factory()->create(['code' => 'SUP002', 'name' => 'CV Mitra Supplier']);

    $page = visit('/suppliers');

    $page->type('search', 'Mitra')
        ->assertSee('SUP002')
        ->assertSee('CV Mitra Supplier')
        ->assertDontSee('PT Supplier Utama');
});

it('can search suppliers by address', function () {
    Supplier::factory()->create(['code' => 'SUP001', 'name' => 'PT Supplier Utama', 'address' => 'Jakarta']);
    Supplier::factory()->create(['code' => 'SUP002', 'name' => 'CV Mitra Supplier', 'address' => 'Bandung']);

    $page = visit('/suppliers');

    $page->type('search', 'Jakarta')
        ->assertSee('SUP001')
        ->assertSee('PT Supplier Utama')
        ->assertDontSee('Bandung');
});

it('can open create modal', function () {
    $page = visit('/suppliers');

    $page->press('Tambah')
        ->assertSee('Tambah Supplier Baru')
        ->assertSee('Kode Supplier')
        ->assertSee('Nama Supplier')
        ->assertSee('Alamat')
        ->assertSee('Simpan')
        ->assertSee('Batal');
});

it('can create new supplier', function () {
    $page = visit('/suppliers');

    $page->press('Tambah')
        ->type('code', 'SUP001')
        ->type('name', 'PT Supplier Utama')
        ->type('address', 'Jl. Supplier No. 1')
        ->press('Simpan')
        ->assertSee('Supplier berhasil dibuat!')
        ->assertSee('SUP001')
        ->assertSee('PT Supplier Utama')
        ->assertSee('Jl. Supplier No. 1');

    $this->assertDatabaseHas('suppliers', [
        'code' => 'SUP001',
        'name' => 'PT Supplier Utama',
        'address' => 'Jl. Supplier No. 1'
    ]);
});

it('can create supplier without address', function () {
    $page = visit('/suppliers');

    $page->press('Tambah')
        ->type('code', 'SUP001')
        ->type('name', 'PT Supplier Utama')
        ->press('Simpan')
        ->assertSee('Supplier berhasil dibuat!')
        ->assertSee('SUP001')
        ->assertSee('PT Supplier Utama');

    $this->assertDatabaseHas('suppliers', [
        'code' => 'SUP001',
        'name' => 'PT Supplier Utama',
        'address' => ""
    ]);
});

it('shows validation error when code is empty', function () {
    $page = visit('/suppliers');

    $page->press('Tambah')
        ->type('name', 'PT Supplier Utama')
        ->press('Simpan')
        ->assertSee('code wajib diisi');
});

it('shows validation error when name is empty', function () {
    $page = visit('/suppliers');

    $page->press('Tambah')
        ->type('code', 'SUP001')
        ->press('Simpan')
        ->assertSee('name wajib diisi');
});

it('shows validation error when code is duplicate', function () {
    Supplier::factory()->create(['code' => 'SUP001']);

    $page = visit('/suppliers');

    $page->press('Tambah')
        ->type('code', 'SUP001')
        ->type('name', 'PT Supplier Utama')
        ->press('Simpan')
        ->assertSee('code sudah ada');
});

it('can open edit modal', function () {
    $supplier = Supplier::factory()->create([
        'code' => 'SUP001',
        'name' => 'PT Supplier Utama',
        'address' => 'Jl. Supplier No. 1'
    ]);

    $page = visit('/suppliers');

    $page->press('Edit')
        ->assertSee('Edit Supplier')
        ->assertSee('SUP001')
        ->assertSee('PT Supplier Utama')
        ->assertSee('Jl. Supplier No. 1');
});

it('can update existing supplier', function () {
    $supplier = Supplier::factory()->create([
        'code' => 'SUP001',
        'name' => 'PT Supplier Utama',
        'address' => 'Jl. Supplier No. 1'
    ]);

    $page = visit('/suppliers');

    $page->press('Edit')
        ->type('code', 'SUP002')
        ->type('name', 'PT Supplier Updated')
        ->type('address', 'Jl. Updated No. 2')
        ->press('Simpan')
        ->assertSee('Supplier berhasil diupdate!')
        ->assertSee('SUP002')
        ->assertSee('PT Supplier Updated')
        ->assertSee('Jl. Updated No. 2');

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'code' => 'SUP002',
        'name' => 'PT Supplier Updated',
        'address' => 'Jl. Updated No. 2'
    ]);
});

//it('can delete supplier', function () {
//    $supplier = Supplier::factory()->create([
//        'code' => 'SUP001',
//        'name' => 'PT Supplier Utama'
//    ]);
//
//    $page = visit('/suppliers');
//
//    $page->press('Hapus')
//        ->assertSee('Supplier berhasil dihapus!');
//
//    $this->assertDatabaseMissing('suppliers', [
//        'id' => $supplier->id
//    ]);
//});

it('can close modal by clicking cancel button', function () {
    $page = visit('/suppliers');

    $page->press('Tambah')
        ->assertSee('Tambah Supplier Baru')
        ->press('Batal')
        ->assertDontSee('Tambah Supplier Baru');
});

it('redirects to login when not authenticated', function () {
    auth()->logout();

    $page = visit('/suppliers');

    $page->assertPathIs('/login');
});

it('shows pagination when suppliers exceed per page limit', function () {
    Supplier::factory()->count(15)->create();

    $page = visit('/suppliers');

    $page->assertSee('2')
        ->assertSee('1');
});
