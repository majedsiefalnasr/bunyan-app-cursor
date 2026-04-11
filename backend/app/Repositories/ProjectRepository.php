<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectRepository
{
    public function __construct(
        private readonly Project $model,
    ) {
    }

    public function findById(int $id): ?Project
    {
        return $this->model->with([
            'customer',
            'contractor',
            'supervisingArchitect',
            'phases.tasks',
        ])->find($id);
    }

    public function findByIdOrFail(int $id): Project
    {
        return $this->model->with([
            'customer',
            'contractor',
            'supervisingArchitect',
            'phases.tasks',
        ])->findOrFail($id);
    }

    public function listForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->forUser($user)
            ->with(['customer', 'contractor', 'supervisingArchitect'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function allActive(array $filters = []): Collection
    {
        return $this->model
            ->active()
            ->with(['customer', 'contractor', 'supervisingArchitect', 'phases'])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->get();
    }

    public function allByCustomer(int $customerId, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->with(['contractor', 'supervisingArchitect', 'phases'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function allByContractor(int $contractorId, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->where('contractor_id', $contractorId)
            ->with(['customer', 'supervisingArchitect', 'phases'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Project
    {
        return $this->model->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh(['customer', 'contractor', 'supervisingArchitect']);
    }

    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    public function restore(int $projectId): bool
    {
        return $this->model->withTrashed()->findOrFail($projectId)->restore();
    }
}
