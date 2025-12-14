<?php

use function Pest\Laravel\{actingAs};
use App\Models\User;

it('can display login page', function () {
    $page = visit('/login');

    $page->assertSee('Masuk ke aplikasi')
        ->assertSee('Email')
        ->assertSee('Kata sandi')
        ->assertSee('Ingat saya')
        ->assertSee('Masuk');
});

it('can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('password123'),
        'role' => 'user'
    ]);

    $page = visit('/login');

    $page->type('email', 'user@test.com')
        ->type('password', 'password123')
        ->press('Masuk')
        ->assertPathIs('/dashboard');
});

it('can login as admin and redirect to admin page', function () {
    $admin = User::factory()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password123'),
        'role' => 'admin'
    ]);

    $page = visit('/login');

    $page->type('email', 'admin@test.com')
        ->type('password', 'password123')
        ->press('Masuk')
        ->assertPathIs('/admin/users');
});

it('shows error when email is empty', function () {
    $page = visit('/login');

    $page->type('password', 'password123')
        ->press('Masuk')
        ->assertSee('email wajib diisi');
});

it('shows error when password is empty', function () {
    $page = visit('/login');

    $page->type('email', 'user@test.com')
        ->press('Masuk')
        ->assertSee('password wajib diisi');
});

it('shows error when email format is invalid', function () {
    $page = visit('/login');

    $page->type('email', 'emailtidakvalid')
        ->type('password', 'password123')
        ->press('Masuk')
        ->assertSee('email harus berupa alamat surel yang valid');
});

it('shows error when password is less than 6 characters', function () {
    $page = visit('/login');

    $page->type('email', 'user@test.com')
        ->type('password', '12345')
        ->press('Masuk')
        ->assertSee('password minimal berisi 6 karakter');
});

it('shows error when user is not found', function () {
    $page = visit('/login');

    $page->type('email', 'notexist@test.com')
        ->type('password', 'password123')
        ->press('Masuk')
        ->assertSee(' Email atau password salah ');
});

it('stays on login page when validation error occurs', function () {
    $page = visit('/login');

    $page->type('email', 'emailtidakvalid')
        ->press('Masuk')
        ->assertPathIs('/login');
});

it('redirects to dashboard if already logged in', function () {
    $user = User::factory()->create();

    actingAs($user);

    $page = visit('/login');
    $page->assertPathIs('/dashboard');
});
