<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'price' => fake()->randomFloat(2, 1, 9999),
            'quantity_in_stock' => fake()->numberBetween(0, 500),
            'category' => 'building_materials',
            'specifications' => null,
            'image_url' => null,
            'active' => true,
        ];
    }
}
