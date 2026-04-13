<?php

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RfqItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationItem>
 */
class QuotationItemFactory extends Factory
{
    protected $model = QuotationItem::class;

    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 1, 5000);
        $qty = fake()->randomFloat(2, 1, 100);

        return [
            'quotation_id' => Quotation::factory(),
            'rfq_item_id' => RfqItem::factory(),
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $qty,
            'notes' => fake()->optional(0.4)->sentence(8),
        ];
    }
}
