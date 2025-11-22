<?php

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

/**
 * GET /suppliers
 */
test('admin can list suppliers with pagination and search', function () {
    Supplier::factory()->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);
    Supplier::factory()->create([
        'code' => 'SUP-002',
        'name' => 'PT. Sejahtera',
        'address' => 'Jl. Thamrin No. 456, Jakarta Pusat',
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/suppliers');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['data', 'current_page', 'per_page', 'total', 'last_page'],
            'errors'
        ]);

    $search = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/suppliers?search=Maju');

    $search->assertStatus(200);
    $suppliers = $search->json('data.data');
    expect(collect($suppliers)->where('name', 'PT. Maju Jaya')->count())->toBeGreaterThan(0);

    $searchCode = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/suppliers?search=SUP-002');
    $searchCode->assertStatus(200);
    expect(collect($searchCode->json('data.data'))->where('code', 'SUP-002')->count())->toBeGreaterThan(0);

    $searchAddress = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/suppliers?search=Thamrin');
    $searchAddress->assertStatus(200);
    expect(collect($searchAddress->json('data.data'))->where('address', 'Jl. Thamrin No. 456, Jakarta Pusat')->count())->toBeGreaterThan(0);
});

test('unauthenticated cannot list suppliers', function () {
    $response = $this->getJson('/api/suppliers');
    $response->assertStatus(401);
});

/**
 * POST /suppliers
 */
test('admin can create supplier', function () {
    $payload = [
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/suppliers', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 201,
            'message' => 'Supplier berhasil ditambahkan',
            'errors' => null
        ]);

    $this->assertDatabaseHas('suppliers', ['code' => 'SUP-001']);
});

test('admin cannot create supplier with missing fields', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/suppliers', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code', 'name', 'address']);
});

test('unauthenticated cannot create supplier', function () {
    $response = $this->postJson('/api/suppliers', [
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);
    $response->assertStatus(401);
});

/**
 * GET /suppliers/{id}
 */
test('admin can get supplier by id', function () {
    $supplier = Supplier::factory()->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson("/api/suppliers/{$supplier->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Data supplier berhasil diambil',
            'errors' => null,
            'data' => [
                'id' => $supplier->id,
                'code' => 'SUP-001',
                'name' => 'PT. Maju Jaya',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
            ]
        ]);
});

test('admin cannot get non-existent supplier', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/suppliers/99999');
    $response->assertStatus(404)
        ->assertJson([
            'status' => 404,
            'message' => 'Supplier tidak ditemukan',
            'data' => null,
            'errors' => null,
        ]);
});

test('unauthenticated cannot get supplier by id', function () {
    $response = $this->getJson('/api/suppliers/1');
    $response->assertStatus(401);
});

/**
 * PUT /suppliers/{id} 
 */
test('admin can update supplier', function () {
    $supplier = Supplier::factory()->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    $payload = [
        'code' => 'SUP-001-UPD',
        'name' => 'PT. Maju Jaya Sejahtera',
        'address' => 'Jl. Thamrin No. 456, Jakarta Pusat'
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/suppliers/{$supplier->id}", $payload);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Supplier berhasil diupdate',
            'errors' => null,
        ])
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id',
                'code',
                'name',
                'address',
                'created_at',
                'updated_at',
            ],
            'errors'
        ]);

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'code' => 'SUP-001-UPD',
        'name' => 'PT. Maju Jaya Sejahtera',
        'address' => 'Jl. Thamrin No. 456, Jakarta Pusat',
    ]);
});

test('admin cannot update non-existent supplier', function () {
    $payload = [
        'code' => 'SUP-999',
        'name' => 'Does Not Exist',
        'address' => 'Jl. Unknown'
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson('/api/suppliers/99999', $payload);

    $response->assertStatus(400)
        ->assertJson([
            'status' => 400,
            'message' => 'Error',
            'data' => null,
            'errors' => ['message' => 'Supplier tidak ditemukan'],
        ]);
});

test('unauthenticated cannot update supplier', function () {
    $payload = [
        'code' => 'SUP-001-UPD',
        'name' => 'PT. Maju Jaya Sejahtera',
        'address' => 'Jl. Thamrin No. 456, Jakarta Pusat'
    ];

    $response = $this->putJson('/api/suppliers/1', $payload);

    $response->assertStatus(401);
});

/**
 * DELETE /suppliers/{id}
 */
test('admin can delete supplier', function () {
    $supplier = Supplier::factory()->create([
        'code' => 'SUP-001',
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson("/api/suppliers/{$supplier->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Supplier berhasil dihapus',
            'data' => null,
            'errors' => null
        ]);

    $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
});

test('admin cannot delete non-existent supplier', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson('/api/suppliers/99999');
    $response->assertStatus(404)
        ->assertJson([
            'status' => 404,
            'message' => 'Supplier tidak ditemukan',
            'data' => null,
            'errors' => null,
        ]);
});

test('unauthenticated cannot delete supplier', function () {
    $response = $this->deleteJson('/api/suppliers/1');
    $response->assertStatus(401);
});
