<?php

namespace Database\Factories;

use App\Models\Phase;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'phase_id' => Phase::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'status' => 'pending',
            'budget' => fake()->randomFloat(2, 100, 10000),
            'assigned_to' => null,
            'start_date' => null,
            'end_date' => null,
        ];
    }
}
