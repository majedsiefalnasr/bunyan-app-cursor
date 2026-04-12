<?php

namespace Database\Factories;

use App\Enums\EstimateItemCategory;
use App\Models\Estimate;
use App\Models\EstimateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstimateItem>
 */
class EstimateItemFactory extends Factory
{
    protected $model = EstimateItem::class;

    public function definition(): array
    {
        $qty = fake()->randomFloat(2, 1, 50);
        $price = fake()->randomFloat(2, 10, 500);
        $total = round($qty * $price, 2);

        return [
            'estimate_id' => Estimate::factory(),
            'product_id' => null,
            'description_ar' => fake()->optional()->words(3, true),
            'description_en' => fake()->optional()->words(3, true),
            'category' => EstimateItemCategory::Material,
            'quantity' => $qty,
            'unit' => 'm2',
            'unit_price' => $price,
            'total_price' => $total,
            'sort_order' => 0,
        ];
    }
}
