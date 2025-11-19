<?php

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin'
    ]);
});

/**
 * Create Supplier - POST
 */
it('can create a supplier', function () {
    $payload = [
        'code' => 'SUP-' . fake()->unique()->numerify('###'),
        'name' => 'PT. Maju Jaya',
        'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/suppliers', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 201,
            'message' => 'Supplier berhasil ditambahkan',
            'errors' => 'Unknown Type: null'
        ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'code',
                'name',
                'address',
                'created_at',
                'updated_at'
            ]
        ]);

    $this->assertDatabaseHas('suppliers', [
        'code' => $payload['code']
    ]);
});

/**
 * List Supplier - GET
 */
it('can list all suppliers', function () {
    Supplier::factory()->count(3)->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/suppliers');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Data supplier berhasil diambil',
            'errors' => 'Unknown Type: null'
        ])
        ->assertJsonStructure([
            'data' => [
                'data' => [
                    [
                        'id',
                        'code',
                        'name',
                        'address',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'current_page',
                'per_page',
                'total',
                'last_page'
            ]
        ]);

    expect(count($response->json('data.data')))->toBe(3);
});

/**
 * Get Supplier by ID
 */
it('can get supplier by id', function () {
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson("/api/suppliers/{$supplier->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Data supplier berhasil diambil',
            'errors' => 'Unknown Type: null',
            'data' => [
                'id' => $supplier->id,
                'code' => $supplier->code,
                'name' => $supplier->name,
                'address' => $supplier->address,
            ]
        ]);
});

/**
 * Update Supplier - PUT
 */
it('can update a supplier', function () {
    $supplier = Supplier::factory()->create();

    $payload = [
        'code' => 'SUP-999',
        'name' => 'PT. Grind Boys',
        'address' => 'Jl. Setia Budi No. 99, Jakarta',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/suppliers/{$supplier->id}", $payload);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Supplier berhasil diupdate',
            'errors' => 'Unknown Type: null',
            'data' => [
                'code' => 'SUP-999',
                'name' => 'PT. Grind Boys',
                'address' => 'Jl. Setia Budi No. 99, Jakarta',
            ]
        ]);

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'PT. Grind Boys'
    ]);
});

/**
 * Delete Supplier
 */
it('can delete a supplier', function () {
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson("/api/suppliers/{$supplier->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Supplier berhasil dihapus',
            'data' => 'Unknown Type: null',
            'errors' => 'Unknown Type: null'
        ]);

    $this->assertDatabaseMissing('suppliers', [
        'id' => $supplier->id
    ]);
});
