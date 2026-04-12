<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectRepository extends BaseRepository
{
    protected function model(): string
    {
        return Project::class;
    }

    public function findById(int $id): ?Model
    {
        return $this->newQuery()->with([
            'customer',
            'contractor',
            'supervisingArchitect',
            'phases.tasks',
        ])->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()->with([
            'customer',
            'contractor',
            'supervisingArchitect',
            'phases.tasks',
        ])->findOrFail($id);
    }

    public function paginateForIndex(User $user, int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->with(['customer', 'contractor', 'supervisingArchitect'])
            ->withCount(['phases', 'tasks']);

        if ($user->role !== UserRole::Admin) {
            $query->forUser($user);
        }

        if ($status !== null && $status !== '') {
            $query->byStatus($status);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function listForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->paginateForIndex(
            $user,
            (int) ($filters['per_page'] ?? 15),
            $filters['status'] ?? null
        );
    }

    public function allActive(array $filters = []): Collection
    {
        /** @var Collection<int, Project> */
        return $this->newQuery()
            ->active()
            ->with(['customer', 'contractor', 'supervisingArchitect', 'phases'])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->get();
    }

    public function allByCustomer(int $customerId, array $filters = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->where('customer_id', $customerId)
            ->with(['contractor', 'supervisingArchitect', 'phases'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function allByContractor(int $contractorId, array $filters = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->where('contractor_id', $contractorId)
            ->with(['customer', 'supervisingArchitect', 'phases'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->orderByDesc('created_at')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
