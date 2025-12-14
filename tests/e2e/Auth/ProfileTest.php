<?php

use function Pest\Laravel\{actingAs};
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@test.com',
        'password' => bcrypt('password123')
    ]);

    actingAs($this->user);
});

it('can display profile page', function () {
    $page = visit('/profile');

    $page->assertSee('Update Profile')
        ->assertSee('Nama')
        ->assertSee('Email')
        ->assertSee('Password Baru')
        ->assertSee('Konfirmasi Password')
        ->assertSee('John Doe')
        ->assertSee('john@test.com');
});
