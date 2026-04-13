<?php

namespace Database\Factories;

use App\Enums\RfqStatus;
use App\Models\Project;
use App\Models\Rfq;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rfq>
 */
class RfqFactory extends Factory
{
    protected $model = Rfq::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'created_by' => User::factory()->customer(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.7)->paragraph(),
            'status' => RfqStatus::Draft->value,
            'delivery_deadline' => fake()->optional(0.6)->dateTimeBetween('+1 week', '+2 months'),
            'response_deadline' => fake()->optional(0.8)->dateTimeBetween('+1 day', '+2 weeks'),
            'sent_at' => null,
            'awarded_quotation_id' => null,
            'awarded_by' => null,
            'awarded_at' => null,
            'closed_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => RfqStatus::Draft->value,
            'sent_at' => null,
        ]);
    }

    public function evaluation(): static
    {
        return $this->state(fn () => [
            'status' => RfqStatus::Evaluation->value,
        ]);
    }

    public function awarded(): static
    {
        return $this->state(fn () => [
            'status' => RfqStatus::Awarded->value,
            'awarded_at' => now(),
        ]);
    }
}
