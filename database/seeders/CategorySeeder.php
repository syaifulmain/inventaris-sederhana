<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['code' => 'CAT-001', 'name' => 'Laptop'],
            ['code' => 'CAT-002', 'name' => 'Desktop Computer'],
            ['code' => 'CAT-003', 'name' => 'Monitor'],
            ['code' => 'CAT-004', 'name' => 'Printer'],
            ['code' => 'CAT-005', 'name' => 'Network Equipment'],
            ['code' => 'CAT-006', 'name' => 'Mobile Device'],
            ['code' => 'CAT-007', 'name' => 'Audio Equipment'],
            ['code' => 'CAT-008', 'name' => 'Office Furniture'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create additional random categories
        // Category::factory(5)->create();
    }
}
