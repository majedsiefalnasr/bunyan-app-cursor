<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'task_id' => null,
            'phase_id' => null,
            'project_id' => Project::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'description' => fake()->paragraph(),
            'attachments' => null,
            'status' => 'submitted',
        ];
    }
}
