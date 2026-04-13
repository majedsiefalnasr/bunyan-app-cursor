<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Rfq;
use App\Models\RfqItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RfqItem>
 */
class RfqItemFactory extends Factory
{
    protected $model = RfqItem::class;

    public function definition(): array
    {
        return [
            'rfq_id' => Rfq::factory(),
            'product_id' => Product::factory(),
            'description' => fake()->sentence(8),
            'quantity' => fake()->randomFloat(2, 1, 1000),
            'unit' => fake()->randomElement(['قطعة', 'متر', 'متر مربع', 'كيس', 'طن']),
            'specifications' => fake()->optional(0.7)->randomElements(['grade' => 'A', 'color' => 'gray'], 1),
            'sort_order' => 0,
        ];
    }
}
