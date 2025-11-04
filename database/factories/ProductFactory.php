<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'manufacturer_id' => Manufacturer::factory(),
            'name' => fake()->unique()->name(),
            'slug' => fake()->unique()->slug(),
            'ean' => fake()->unique()->ean13(),
            'description' => fake()->text(),
            'active' => fake()->boolean(),
        ];
    }
}
