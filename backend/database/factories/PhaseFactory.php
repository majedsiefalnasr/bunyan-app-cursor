<?php

namespace Database\Factories;

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
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'status' => 'pending',
            'budget' => fake()->randomFloat(2, 500, 50000),
            'progress' => 0,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ];
    }
}
