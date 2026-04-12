<?php

namespace Database\Factories;

use App\Enums\PhaseStatus;
use App\Models\Phase;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Phase>
 */
class PhaseFactory extends Factory
{
    protected $model = Phase::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'sort_order' => 0,
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'status' => PhaseStatus::Pending->value,
            'budget' => fake()->randomFloat(2, 500, 50000),
            'progress' => 0,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PhaseStatus::Pending->value,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PhaseStatus::InProgress->value,
            'progress' => fake()->numberBetween(10, 90),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PhaseStatus::Completed->value,
            'progress' => 100,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PhaseStatus::Approved->value,
            'progress' => 100,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PhaseStatus::Rejected->value,
        ]);
    }
}
