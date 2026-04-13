<?php

namespace Database\Factories;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\SupplierProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition(): array
    {
        return [
            'rfq_id' => Rfq::factory(),
            'supplier_id' => SupplierProfile::factory(),
            'status' => QuotationStatus::Submitted->value,
            'total_price' => fake()->randomFloat(2, 100, 50000),
            'delivery_days' => fake()->optional(0.6)->numberBetween(1, 30),
            'notes' => fake()->optional(0.5)->sentence(10),
            'valid_until' => fake()->optional(0.6)->dateTimeBetween('+3 days', '+1 month'),
            'submitted_at' => now(),
        ];
    }
}
