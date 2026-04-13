<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\Rfq;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class RfqRepository extends BaseRepository
{
    protected function model(): string
    {
        return Rfq::class;
    }

    public function findByIdOrFail(int $id): Rfq
    {
        /** @var Rfq */
        return $this->newQuery()
            ->with(['items', 'creator', 'project'])
            ->findOrFail($id);
    }

    public function findVisibleByIdOrFail(User $user, int $id): Rfq
    {
        /** @var Rfq */
        return $this->queryVisibleTo($user)
            ->with(['items', 'creator', 'project'])
            ->findOrFail($id);
    }

    public function listVisibleTo(User $user, array $filters = []): LengthAwarePaginator
    {
        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return $this->queryVisibleTo($user)
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when(($filters['sort'] ?? null) === 'created_at', fn (Builder $q) => $q->orderBy('created_at', 'asc'))
            ->when(($filters['sort'] ?? null) === '-created_at', fn (Builder $q) => $q->orderBy('created_at', 'desc'))
            ->paginate($perPage);
    }

    public function createRfq(array $data): Rfq
    {
        /** @var Rfq */
        return $this->create($data);
    }

    public function updateRfq(Rfq $rfq, array $data): Rfq
    {
        /** @var Rfq */
        return $this->update($rfq, $data);
    }

    private function queryVisibleTo(User $user): Builder
    {
        $query = $this->newQuery()->select(['rfqs.*']);

        return match ($user->role) {
            UserRole::Customer => $query->where('created_by', $user->id),
            UserRole::Contractor => $query
                ->join('rfq_targets', 'rfq_targets.rfq_id', '=', 'rfqs.id')
                ->where('rfq_targets.supplier_id', $user->supplierProfile?->id ?? 0),
            UserRole::Admin => $query,
            default => $query->whereRaw('1=0'),
        };
    }
}
