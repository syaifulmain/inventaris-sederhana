<?php
use App\Models\User;
use App\Models\Supplier;

test('admin can view list of suppliers', function () {
    $admin = User::factory()->admin()->create();
    Supplier::factory()->count(15)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/suppliers');

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

test('admin can search suppliers by code', function () {
    $admin = User::factory()->admin()->create();
    Supplier::factory()->create(['code' => 'SUP-001']);
    Supplier::factory()->create(['code' => 'SUP-002']);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/suppliers?search=SUP-001');

    $response->assertStatus(200);

    $suppliers = $response->json('data.data');
    expect(collect($suppliers)->where('code', 'SUP-001')->count())->toBeGreaterThan(0);
});

test('admin can search suppliers by name', function () {
    $admin = User::factory()->admin()->create();
    Supplier::factory()->create(['name' => 'PT. Maju Jaya']);
    Supplier::factory()->create(['name' => 'PT. Sejahtera']);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/suppliers?search=Maju');

    $response->assertStatus(200);

    $suppliers = $response->json('data.data');
    expect(collect($suppliers)->where('name', 'PT. Maju Jaya')->count())->toBeGreaterThan(0);
});

test('admin can change pagination per page', function () {
    $admin = User::factory()->admin()->create();
    Supplier::factory()->count(25)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/suppliers?per_page=5');

    $response->assertStatus(200);
    expect($response->json('data.per_page'))->toBe(5);
});

test('admin can create new supplier', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/suppliers', [
            'code' => 'SUP-001',
            'name' => 'PT. Maju Jaya',
            'address' => 'Jl. Sudirman No. 123',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'status' => 201,
            'message' => 'Supplier berhasil ditambahkan',
            'data' => [
                'code' => 'SUP-001',
                'name' => 'PT. Maju Jaya',
            ],
        ]);

    $this->assertDatabaseHas('suppliers', [
        'code' => 'SUP-001',
    ]);
});

test('admin can view specific supplier', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/suppliers/{$supplier->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'data' => [
                'id' => $supplier->id,
                'code' => $supplier->code,
                'name' => $supplier->name,
            ],
        ]);
});

test('admin can update supplier', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/suppliers/{$supplier->id}", [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Supplier berhasil diupdate',
            'data' => [
                'name' => 'Updated Name',
            ],
        ]);

    $supplier->refresh();
    expect($supplier->name)->toBe('Updated Name');
});

test('admin can delete supplier', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/suppliers/{$supplier->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 200,
            'message' => 'Supplier berhasil dihapus',
        ]);

    $this->assertDatabaseMissing('suppliers', [
        'id' => $supplier->id,
    ]);
});

test('admin cannot create supplier with duplicate code', function () {
    $admin = User::factory()->admin()->create();
    Supplier::factory()->create(['code' => 'SUP-001']);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/suppliers', [
            'code' => 'SUP-001',
            'name' => 'PT. Test',
            'address' => 'Test Address',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('admin cannot view non-existent supplier', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/suppliers/99999');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Supplier tidak ditemukan',
        ]);
});

test('admin cannot update non-existent supplier', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson('/api/suppliers/99999', [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(404);
});

test('admin cannot delete non-existent supplier', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson('/api/suppliers/99999');

    $response->assertStatus(404);
});

test('create supplier validation fails with missing required fields', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/suppliers', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code', 'name']);
});

test('update supplier code must be unique', function () {
    $admin = User::factory()->admin()->create();
    $existingSupplier = Supplier::factory()->create(['code' => 'SUP-001']);
    $supplier = Supplier::factory()->create(['code' => 'SUP-002']);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/suppliers/{$supplier->id}", [
            'code' => 'SUP-001',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('index returns error when service throws exception', function () {
    $admin = User::factory()->admin()->create();

    $mock = Mockery::mock(\App\Services\SupplierService::class);
    $mock->shouldReceive('getPaginated')
        ->once()
        ->andThrow(new Exception('Service error'));
    $this->app->instance(\App\Services\SupplierService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/suppliers');

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal mengambil data supplier',
            'errors' => ['message' => 'Service error'],
        ]);
});

test('store returns error when service throws exception', function () {
    $admin = User::factory()->admin()->create();

    $mock = Mockery::mock(\App\Services\SupplierService::class);
    $mock->shouldReceive('create')
        ->once()
        ->andThrow(new Exception('Create error'));
    $this->app->instance(\App\Services\SupplierService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/suppliers', [
            'code' => 'SUP-TEST',
            'name' => 'Test Supplier',
        ]);

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal menambahkan supplier',
            'errors' => ['message' => 'Create error'],
        ]);
});

test('update returns error when service throws generic exception', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    $mock = Mockery::mock(\App\Services\SupplierService::class);
    $mock->shouldReceive('update')
        ->once()
        ->andThrow(new Exception('Update error'));
    $this->app->instance(\App\Services\SupplierService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/suppliers/{$supplier->id}", [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal mengupdate supplier',
            'errors' => ['message' => 'Update error'],
        ]);
});

test('delete returns error when service throws generic exception', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    $mock = Mockery::mock(\App\Services\SupplierService::class);
    $mock->shouldReceive('delete')
        ->once()
        ->andThrow(new Exception('Delete error'));
    $this->app->instance(\App\Services\SupplierService::class, $mock);

    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/suppliers/{$supplier->id}");

    $response->assertStatus(500)
        ->assertJson([
            'status' => 500,
            'message' => 'Gagal menghapus supplier',
            'errors' => ['message' => 'Delete error'],
        ]);
});
