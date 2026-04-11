<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
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
            'status' => TaskStatus::Pending->value,
            'budget' => fake()->randomFloat(2, 100, 10000),
            'assigned_to' => null,
            'start_date' => null,
            'end_date' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Pending->value,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::InProgress->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Completed->value,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Approved->value,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Rejected->value,
        ]);
    }
}
