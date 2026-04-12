<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
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
        $name = fake()->words(2, true);

        return [
            'phase_id' => Phase::factory(),
            'project_id' => null,
            'name' => $name,
            'title_ar' => $name,
            'title_en' => null,
            'description' => fake()->optional()->sentence(),
            'status' => TaskStatus::Todo->value,
            'priority' => TaskPriority::Medium->value,
            'budget' => fake()->randomFloat(2, 100, 10000),
            'assigned_to' => null,
            'start_date' => null,
            'end_date' => null,
            'due_date' => null,
            'estimated_hours' => null,
            'actual_hours' => null,
            'sort_order' => 0,
            'created_by' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Task $task): void {
            $dirty = false;
            if ($task->project_id === null && $task->phase_id) {
                $task->loadMissing('phase');
                if ($task->phase) {
                    $task->project_id = $task->phase->project_id;
                    $dirty = true;
                }
            }
            if ($task->title_ar === null && $task->name !== null) {
                $task->title_ar = $task->name;
                $dirty = true;
            }
            if ($dirty) {
                $task->saveQuietly();
            }
        });
    }

    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Todo->value,
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
            'status' => TaskStatus::Done->value,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Done->value,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Blocked->value,
        ]);
    }

    /** @deprecated use todo() */
    public function pending(): static
    {
        return $this->todo();
    }
}
