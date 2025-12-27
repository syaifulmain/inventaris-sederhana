<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::where('code', 'ELEC')->first();
        $fashion = Category::where('code', 'FASH')->first();

        if ($electronics) {
            Product::create([
                'code' => 'PROD-001',
                'category_id' => $electronics->id,
                'name' => 'Laptop ASUS ROG Strix',
            ]);
            
            Product::create([
                'code' => 'PROD-002',
                'category_id' => $electronics->id,
                'name' => 'Mouse Gaming Logitech',
            ]);
            
            Product::create([
                'code' => 'PROD-003',
                'category_id' => $electronics->id,
                'name' => 'Keyboard Mechanical RGB',
            ]);
        }

        if ($fashion) {
            Product::create([
                'code' => 'PROD-004',
                'category_id' => $fashion->id,
                'name' => 'Kaos Polos Premium',
            ]);
            
            Product::create([
                'code' => 'PROD-005',
                'category_id' => $fashion->id,
                'name' => 'Celana Jeans Slim Fit',
            ]);
        }
    }
}