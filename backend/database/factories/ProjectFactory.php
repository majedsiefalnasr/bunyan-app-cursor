<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'customer_id' => User::factory(),
            'contractor_id' => null,
            'supervising_architect_id' => null,
            'status' => 'pending',
            'budget' => fake()->randomFloat(2, 1000, 500000),
            'location' => fake()->city(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
        ];
    }
}
