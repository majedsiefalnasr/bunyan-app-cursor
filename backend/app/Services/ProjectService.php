<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use App\Repositories\ProjectRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    public function __construct(private ProjectRepository $projects)
    {
    }

    public function paginateForUser(User $user, int $perPage, ?string $status): LengthAwarePaginator
    {
        return $this->projects->paginateForIndex($user, $perPage, $status);
    }

    public function findForShow(int $id): Project
    {
        /** @var Project */
        return $this->projects->findByIdOrFail($id);
    }

    public function create(User $user, array $data): Project
    {
        $payload = [
            'name' => $data['name'],
            'name_ar' => $data['name_ar'] ?? $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'description' => $data['description'] ?? null,
            'customer_id' => $user->id,
            'status' => ProjectStatus::Draft->value,
            'budget' => $data['budget'],
            'budget_estimated' => $data['budget_estimated'] ?? $data['budget'],
            'budget_actual' => $data['budget_actual'] ?? null,
            'location' => $data['location'],
            'city' => $data['city'] ?? null,
            'district' => $data['district'] ?? null,
            'location_lat' => $data['location_lat'] ?? null,
            'location_lng' => $data['location_lng'] ?? null,
            'project_type' => $data['project_type'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ];

        /** @var Project */
        return $this->projects->create($payload);
    }

    public function updateProject(Project $project, array $data): Project
    {
        $allowed = [
            'name',
            'name_ar',
            'name_en',
            'description',
            'budget',
            'budget_estimated',
            'budget_actual',
            'location',
            'city',
            'district',
            'location_lat',
            'location_lng',
            'project_type',
            'start_date',
            'end_date',
        ];

        $updated = $this->projects->update($project, Arr::only($data, $allowed));
        assert($updated instanceof Project);

        return $updated;
    }

    public function transitionStatus(Project $project, ProjectStatus $next): Project
    {
        $current = $project->status;
        if (! $this->canTransition($current, $next)) {
            throw ValidationException::withMessages([
                'status' => ['انتقال الحالة غير مسموح'],
            ]);
        }

        $updated = $this->projects->update($project, ['status' => $next->value]);
        assert($updated instanceof Project);

        return $updated;
    }

    public function timeline(Project $project): array
    {
        $project->load([
            'phases' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
        ]);

        return [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'start_date' => $project->start_date?->toIso8601String(),
                'end_date' => $project->end_date?->toIso8601String(),
            ],
            'phases' => $project->phases->map(static function ($phase) {
                return [
                    'id' => $phase->id,
                    'name' => $phase->name,
                    'start_date' => $phase->start_date?->toIso8601String(),
                    'end_date' => $phase->end_date?->toIso8601String(),
                    'completion_percentage' => $phase->progress,
                    'sort_order' => $phase->sort_order,
                ];
            })->values()->all(),
        ];
    }

    private function canTransition(ProjectStatus $from, ProjectStatus $to): bool
    {
        $allowed = match ($from) {
            ProjectStatus::Draft => [ProjectStatus::Planning],
            ProjectStatus::Planning => [ProjectStatus::InProgress],
            ProjectStatus::InProgress => [ProjectStatus::OnHold, ProjectStatus::Completed],
            ProjectStatus::OnHold => [ProjectStatus::InProgress, ProjectStatus::Completed],
            ProjectStatus::Completed => [ProjectStatus::Closed],
            ProjectStatus::Closed => [],
        };

        return in_array($to, $allowed, true);
    }
}
