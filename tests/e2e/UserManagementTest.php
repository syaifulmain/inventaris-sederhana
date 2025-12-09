<?php

use App\Models\User;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    actingAs($this->admin);
});

it('can display user management page', function () {
    $page = visit('/admin/users');

    $page->assertSee('Manajemen Pengguna')
        ->assertSee('Tambah')
        ->assertSee('Nama')
        ->assertSee('Email')
        ->assertSee('Role')
        ->assertSee('Status')
        ->assertSee('Aksi');
});

it('can display empty state when no users exist except admin', function () {
    User::where('id', '!=', $this->admin->id)->delete();

    $page = visit('/admin/users');

    $page->assertSee($this->admin->name)
        ->assertSee($this->admin->email);
});

it('can display list of users', function () {
    User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => 'user'
    ]);

    User::factory()->create([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'role' => 'admin'
    ]);

    $page = visit('/admin/users');

    $page->assertSee('John Doe')
        ->assertSee('john@example.com')
        ->assertSee('Jane Smith')
        ->assertSee('jane@example.com');
});

it('can search users by name', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $page = visit('/admin/users');

    $page->type('input-group-1', 'John')
        ->assertSee('John Doe')
        ->assertSee('john@example.com')
        ->assertDontSee('Jane Smith');
});

it('can search users by email', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $page = visit('/admin/users');

    $page->type('input-group-1', 'jane@example.com')
        ->assertSee('Jane Smith')
        ->assertSee('jane@example.com')
        ->assertDontSee('John Doe');
});

it('can filter users by role admin', function () {
    User::factory()->create(['name' => 'Regular User', 'role' => 'user']);
    User::factory()->create(['name' => 'Admin User', 'role' => 'admin']);

    $page = visit('/admin/users');

    $page->select('roleFilter', 'admin')
        ->assertSee('Admin User')
        ->assertDontSee('Regular User');
});

it('can filter users by role user', function () {
    User::factory()->create(['name' => 'Regular User', 'role' => 'user']);
    User::factory()->create(['name' => 'Admin User', 'role' => 'admin']);

    $page = visit('/admin/users');

    $page->select('roleFilter', 'user')
        ->assertSee('Regular User')
        ->assertDontSee('Admin');
});

it('can open create modal', function () {
    $page = visit('/admin/users');

    $page->press('Tambah')
        ->assertSee('Tambah User Baru')
        ->assertSee('Nama')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertSee('Konfirmasi Password')
        ->assertSee('Role')
        ->assertSee('Pengguna Aktif')
        ->assertSee('Simpan')
        ->assertSee('Batal');
});

it('can create new user', function () {
    $page = visit('/admin/users');

    $page->press('Tambah')
        ->type('name', 'New User')
        ->type('email', 'newuser@example.com')
        ->type('password', 'password123')
        ->type('password_confirmation', 'password123')
        ->press('Simpan')
        ->assertSee('User berhasil dibuat!')
        ->assertSee('New User')
        ->assertSee('newuser@example.com');

    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'user',
        'is_active' => true
    ]);
});

it('can create admin user', function () {
    $page = visit('/admin/users');

    $page->press('Tambah')
        ->type('name', 'Admin User')
        ->type('email', 'admin@example.com')
        ->type('password', 'password123')
        ->type('password_confirmation', 'password123')
        ->select('role', 'admin')
        ->press('Simpan')
        ->assertSee('User berhasil dibuat!')
        ->assertSee('Admin User');

    $this->assertDatabaseHas('users', [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'role' => 'admin'
    ]);
});

it('shows validation error when name is empty', function () {
    $page = visit('/admin/users');

    $page->press('Tambah')
        ->type('email', 'test@example.com')
        ->type('password', 'password123')
        ->type('password_confirmation', 'password123')
        ->press('Simpan')
        ->assertSee('name wajib diisi');
});

it('shows validation error when email is empty', function () {
    $page = visit('/admin/users');

    $page->press('Tambah')
        ->type('name', 'Test User')
        ->type('password', 'password123')
        ->type('password_confirmation', 'password123')
        ->press('Simpan')
        ->assertSee('email wajib diisi');
});

it('shows validation error when password is empty', function () {
    $page = visit('/admin/users');

    $page->press('Tambah')
        ->type('name', 'Test User')
        ->type('email', 'test@example.com')
        ->press('Simpan')
        ->assertSee('password wajib diisi');
});

it('shows validation error when password confirmation does not match', function () {
    $page = visit('/admin/users');

    $page->press('Tambah')
        ->type('name', 'Test User')
        ->type('email', 'test@example.com')
        ->type('password', 'password123')
        ->type('password_confirmation', 'different')
        ->press('Simpan')
        ->assertSee(' Konfirmasi password tidak cocok. ');
});

it('shows validation error when email is duplicate', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $page = visit('/admin/users');

    $page->press('Tambah')
        ->type('name', 'Test User')
        ->type('email', 'existing@example.com')
        ->type('password', 'password123')
        ->type('password_confirmation', 'password123')
        ->press('Simpan')
        ->assertSee('email sudah ada');
});

it('can open edit modal', function () {
    $user = User::factory()->create([
        'name' => 'Edit User',
        'email' => 'edit@example.com'
    ]);

    $page = visit('/admin/users');

    $page->press('Edit')
        ->assertSee('Edit User')
        ->assertSee('Edit User')
        ->assertSee('edit@example.com')
        ->assertSee('(kosongkan jika tidak ingin mengubah)');
});

// TODO: Tambahkan test untuk validasi saat update user (nama kosong, email kosong, dll)
it('can update existing user', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'role' => 'user'
    ]);

    $page = visit('/admin/users');

    $page->press('Edit')
        ->type('name', 'Updated Name')
        ->type('email', 'updated@example.com')
        ->press('Simpan')
        ->assertSee('User berhasil diupdate!')
        ->assertSee('Updated Name')
        ->assertSee('updated@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com'
    ]);
})->skip();

// TODO: Tambahkan test untuk validasi saat update user (email duplicate, dll)
it('can update user password', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com'
    ]);

    $page = visit('/admin/users');

    $page->press('Edit')
        ->type('password', 'newpassword123')
        ->type('password_confirmation', 'newpassword123')
        ->press('Simpan')
        ->assertSee('User berhasil diupdate!');

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
})->skip();

// TODO: Tambahkan test untuk validasi saat update password (password kosong, konfirmasi tidak cocok, dll)
it('can update user role', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'role' => 'user'
    ]);

    $page = visit('/admin/users');

    $page->press('Edit')
        ->select('role', 'admin')
        ->press('Simpan')
        ->assertSee('User berhasil diupdate!');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => 'admin'
    ]);
})->skip();

// TODO: Tambahkan test untuk validasi saat update role (role tidak valid, dll)
it('can deactivate user', function () {
    $user = User::factory()->create([
        'name' => 'Active User',
        'is_active' => true
    ]);

    $page = visit('/admin/users');

    $page->press('Edit')
        ->uncheck('checked-checkbox')
        ->press('Simpan')
        ->assertSee('User berhasil diupdate!');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'is_active' => false
    ]);
})->skip();

it('cannot delete own account', function () {
    $page = visit('/admin/users');

    // Admin mencoba menghapus akunnya sendiri - button hapus tidak akan muncul
    $page->assertDontSee('Hapus');
});

it('can close modal by clicking cancel button', function () {
    $page = visit('/admin/users');

    $page->press('Tambah')
        ->assertSee('Tambah User Baru')
        ->press('Batal')
        ->assertDontSee('Tambah User Baru');
});

it('redirects to login when not authenticated', function () {
    auth()->logout();

    $page = visit('/admin/users');

    $page->assertPathIs('/login');
});

// Access control tests
it('forbids access for non-admin users', function () {
    $user = User::factory()->create(['role' => 'user']);
    actingAs($user);

    $page = visit('/admin/users');

    $page->assertStatus(403);
})->skip();

it('shows pagination when users exceed per page limit', function () {
    User::factory()->count(15)->create();

    $page = visit('/admin/users');

    $page->assertSee('2')
        ->assertSee('1');
});

it('displays correct user status badges', function () {
    User::factory()->create(['name' => 'Active User', 'is_active' => true]);
    User::factory()->create(['name' => 'Inactive User', 'is_active' => false]);

    $page = visit('/admin/users');

    $page->assertSee('Aktif')
        ->assertSee('Nonaktif');
});

it('displays correct role badges', function () {
    User::factory()->create(['name' => 'Admin User', 'role' => 'admin']);
    User::factory()->create(['name' => 'Regular User', 'role' => 'user']);

    $page = visit('/admin/users');

    $page->assertSee('Admin')
        ->assertSee('User');
});
