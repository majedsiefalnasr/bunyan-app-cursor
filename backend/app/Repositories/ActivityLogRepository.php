<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogRepository extends BaseRepository
{
    protected function model(): string
    {
        return ActivityLog::class;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateAdmin(array $filters): LengthAwarePaginator
    {
        $perPage = min(100, max(1, (int) ($filters['per_page'] ?? 15)));

        $query = $this->newQuery()
            ->with('actor')
            ->orderByDesc('created_at');

        $query->when($filters['user_id'] ?? null, fn (Builder $q, int $id): Builder => $q->where('user_id', $id));

        $query->when($filters['action'] ?? null, fn (Builder $q, string $action): Builder => $q->where('action', $action));

        $query->when($filters['subject_type'] ?? null, fn (Builder $q, string $type): Builder => $q->where('subject_type', $type));

        $query->when($filters['subject_id'] ?? null, fn (Builder $q, int $sid): Builder => $q->where('subject_id', $sid));

        if (! empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (! empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateForSubject(Model $subject, array $filters): LengthAwarePaginator
    {
        $perPage = min(100, max(1, (int) ($filters['per_page'] ?? 15)));

        return $this->newQuery()
            ->with('actor')
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function countOlderThan(DateTimeInterface $cutoff): int
    {
        return (int) $this->newQuery()->where('created_at', '<', $cutoff)->count();
    }

    public function deleteOlderThan(DateTimeInterface $cutoff): int
    {
        return (int) $this->newQuery()->where('created_at', '<', $cutoff)->delete();
    }
}
