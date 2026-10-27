<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'type' => fake()->randomElement(ProductType::cases()),
            'unit_price' => fake()->randomFloat(2, 250, 25000),
            'tax_rate' => fake()->randomElement([0, 5, 12, 18]),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
