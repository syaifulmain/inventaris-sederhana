<?php

namespace Database\Factories;

use App\Enums\StockTransactionType;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
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
            'product_id' => Product::factory(),
            'supplier_id' => Supplier::factory(),
            'type' => fake()->randomElement([StockTransactionType::in, StockTransactionType::out]),
            'quantity' => fake()->numberBetween(10, 100),
            'description' => fake()->sentence(),
            'transaction_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'user_id' => User::factory(),
        ];
    }
}
