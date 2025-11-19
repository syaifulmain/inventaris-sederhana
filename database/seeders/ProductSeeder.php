<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing categories
        $categories = Category::all();

        // Create some specific products
        $specificProducts = [
            ['code' => 'PROD-001', 'name' => 'Laptop ASUS ROG Strix', 'category_id' => $categories->where('name', 'Laptop')->first()?->id ?? 1],
            ['code' => 'PROD-002', 'name' => 'Dell OptiPlex Desktop', 'category_id' => $categories->where('name', 'Desktop Computer')->first()?->id ?? 2],
            ['code' => 'PROD-003', 'name' => 'Samsung 24" Monitor', 'category_id' => $categories->where('name', 'Monitor')->first()?->id ?? 3],
            ['code' => 'PROD-004', 'name' => 'HP LaserJet Printer', 'category_id' => $categories->where('name', 'Printer')->first()?->id ?? 4],
        ];

        foreach ($specificProducts as $product) {
            Product::create($product);
        }

        // Create random products for each category
        // foreach ($categories as $category) {
        //     Product::factory(rand(3, 8))->create([
        //         'category_id' => $category->id,
        //     ]);
        // }
    }
}
