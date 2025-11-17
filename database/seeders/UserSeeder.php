<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        for ($i = 0; $i < 15; $i++) {
            // Create regular user
            User::create([
                'name' => 'User ' . ($i + 1),
                'email' => 'user' . ($i + 1) . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ]);
        }
    }
}
