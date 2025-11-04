<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Distributor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
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
            'distributor_id' => Distributor::factory(),
            'sku' => fake()->unique()->ean8(),
            'price' => fake()->randomFloat(2, 0, 1000),
            'available' => fake()->boolean(),
        ];
    }
}
