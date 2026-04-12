<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
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
            'status' => ProjectStatus::Draft->value,
            'budget' => fake()->randomFloat(2, 1000, 500000),
            'location' => fake()->city(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Draft->value,
        ]);
    }

    public function planning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Planning->value,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::InProgress->value,
        ]);
    }

    /** @deprecated Use inProgress() — alias for older tests */
    public function active(): static
    {
        return $this->inProgress();
    }

    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::OnHold->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Completed->value,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Closed->value,
        ]);
    }
}
