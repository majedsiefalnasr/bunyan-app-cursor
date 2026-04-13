<?php

namespace Database\Factories;

use App\Enums\EstimateStatus;
use App\Models\Estimate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estimate>
 */
class EstimateFactory extends Factory
{
    protected $model = Estimate::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'status' => EstimateStatus::Draft,
            'total_materials' => 0,
            'total_labor' => 0,
            'total_overhead' => 0,
            'grand_total' => 0,
            'markup_percentage' => 0,
            'approved_by' => null,
            'approved_at' => null,
            'created_by' => User::factory(),
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => ['status' => EstimateStatus::Submitted]);
    }
}
