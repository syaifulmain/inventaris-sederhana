<?php

use function Pest\Laravel\{actingAs};
use App\Models\User;

it('dapat menampilkan halaman login', function () {
    $page = visit('/login');

    $page->assertSee('Masuk ke aplikasi')
        ->assertSee('Email')
        ->assertSee('Kata sandi')
        ->assertSee('Ingat saya')
        ->assertSee('Masuk');
});

it('dapat login dengan kredensial yang valid', function () {
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

it('dapat login sebagai admin dan redirect ke halaman admin', function () {
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

it('menampilkan error ketika email kosong', function () {
    $page = visit('/login');

    $page->type('password', 'password123')
        ->press('Masuk')
        ->assertSee('email wajib diisi');
});

it('menampilkan error ketika password kosong', function () {
    $page = visit('/login');

    $page->type('email', 'user@test.com')
        ->press('Masuk')
        ->assertSee('password wajib diisi');
});

it('menampilkan error ketika format email tidak valid', function () {
    $page = visit('/login');

    $page->type('email', 'emailtidakvalid')
        ->type('password', 'password123')
        ->press('Masuk')
        ->assertSee('email harus berupa alamat surel yang valid');
});

it('menampilkan error ketika password kurang dari 6 karakter', function () {
    $page = visit('/login');

    $page->type('email', 'user@test.com')
        ->type('password', '12345')
        ->press('Masuk')
        ->assertSee('password minimal berisi 6 karakter');
});

it('menampilkan error ketika user tidak ditemukan', function () {
    $page = visit('/login');

    $page->type('email', 'notexist@test.com')
        ->type('password', 'password123')
        ->press('Masuk')
        ->assertSee(' Email atau password salah ');
});

it('dapat menggunakan fitur remember me', function () {
    $user = User::factory()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('password123')
    ]);

    $page = visit('/login');

    $page->type('email', 'user@test.com')
        ->type('password', 'password123')
        ->check('remember')
        ->press('Masuk')
        ->assertPathIs('/dashboard');

    $user->refresh();
    expect($user->remember_token)->not->toBeNull();
});

it('tetap di halaman login ketika terjadi error validasi', function () {
    $page = visit('/login');

    $page->type('email', 'emailtidakvalid')
        ->press('Masuk')
        ->assertPathIs('/login');
});

it('redirect ke dashboard jika sudah login', function () {
    $user = User::factory()->create();

    actingAs($user);

    $page = visit('/login');
    $page->assertPathIs('/dashboard');
});
