<?php

namespace Database\Seeders;

use App\Enums\StockTransactionType;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::whereIn('code', ['PROD-001', 'PROD-002', 'PROD-003', 'PROD-004', 'PROD-005'])->get();
        $supplierMajuJaya = Supplier::where('code', 'SUP-033')->first();
        $supplierSuksesSelalu = Supplier::where('code', 'SUP-034')->first();

        if ($products->count() > 0 && $supplierMajuJaya && $supplierSuksesSelalu) {
            $today = now()->format('Ymd');
            $sequence = 1;
            
            // Stock in transactions
            foreach ($products as $index => $product) {
                StockTransaction::create([
                    'transaction_code' => 'TR' . $today . str_pad($sequence++, 4, '0', STR_PAD_LEFT),
                    'product_id' => $product->id,
                    'supplier_id' => $index % 2 == 0 ? $supplierMajuJaya->id : $supplierSuksesSelalu->id,
                    'type' => StockTransactionType::in,
                    'quantity' => rand(50, 100),
                    'description' => 'Initial stock in for ' . $product->name,
                    'transaction_date' => now()->subDays(rand(10, 30)),
                    'user_id' => 1,
                ]);
            }

            // Stock out transactions
            StockTransaction::create([
                'transaction_code' => 'TR' . $today . str_pad($sequence++, 4, '0', STR_PAD_LEFT),
                'product_id' => $products->first()->id,
                'supplier_id' => $supplierMajuJaya->id,
                'type' => StockTransactionType::out,
                'quantity' => 20,
                'description' => 'Stock out for sale',
                'transaction_date' => now()->subDays(5),
                'user_id' => 1,
            ]);
        }
    }
}
