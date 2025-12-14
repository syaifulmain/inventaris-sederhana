<?php

namespace Database\Factories;

use App\Enums\StockTransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockTransaction>
 */
class StockTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => 1,
            'supplier_id' => 1,
            'type' => StockTransactionType::IN,
            'quantity' => 10,
            'transaction_date' => now(),
            'user_id' => 1,
        ];
    }
}
