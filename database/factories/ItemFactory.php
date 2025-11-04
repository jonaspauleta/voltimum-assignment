<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Distributor;
use App\Models\Item;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
final class ItemFactory extends Factory
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
        ];
    }
}
