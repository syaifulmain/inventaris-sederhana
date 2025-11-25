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
        $electronicsProduct = Product::whereIn('code', ['PROD-001', 'PROD-002', 'PROD-003'])->get()->first();

        $fashionProduct = Product::whereIn('code', ['PROD-004', 'PROD-005'])->get()->first();

        $supplierMajuJaya = Supplier::where('code', 'SUP-033')->first();
        $supplierSuksesSelalu = Supplier::where('code', 'SUP-034')->first();


        if ($electronicsProduct && $fashionProduct && $supplierMajuJaya && $supplierSuksesSelalu) {


            StockTransaction::create([
                'product_id' => $electronicsProduct->id,
                'supplier_id' => $supplierMajuJaya->id,
                'type' => StockTransactionType::IN->value,
                'quantity' => 50,
                'description' => 'Initial stock from Supplier Maju Jaya',
                'transaction_date' => now()->subDays(10),
                'user_id' => 1, // assuming user with ID 1 exists
            ]);

            StockTransaction::create([
                'product_id' => $fashionProduct->id,
                'supplier_id' => $supplierSuksesSelalu->id,
                'type' => StockTransactionType::OUT->value,
                'quantity' => 30,
                'description' => 'Initial stock from Supplier Sukses Selalu',
                'transaction_date' => now()->subDays(8),
                'user_id' => 1, // assuming user with ID 1 exists
            ]);
        }
    }
}
