<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\Media;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

class MediaRepository extends BaseRepository
{
    protected function model(): string
    {
        return Media::class;
    }

    /**
     * @param  array{collection?: string|null, mime_type?: string|null, temporary?: bool|null, user_id?: int|null, per_page?: int|null, page?: int|null}  $filters
     */
    public function paginateForActor(User $actor, array $filters): LengthAwarePaginator
    {
        $perPage = min(50, max(1, (int) ($filters['per_page'] ?? 15)));

        $query = $this->newQuery()->orderByDesc('created_at');

        if ($actor->role !== UserRole::Admin) {
            $query->where('uploaded_by', $actor->id);
        } elseif (! empty($filters['user_id'])) {
            $query->where('uploaded_by', (int) $filters['user_id']);
        }

        $query->when($filters['collection'] ?? null, fn (Builder $q, string $c) => $q->where('collection', $c));

        if (array_key_exists('temporary', $filters) && $filters['temporary'] !== null) {
            $query->where('is_temporary', (bool) $filters['temporary']);
        }

        $query->when($filters['mime_type'] ?? null, function (Builder $q, string $prefix): void {
            $q->where('mime_type', 'like', $prefix.'%');
        });

        return $query->paginate($perPage);
    }

    /**
     * @return LazyCollection<int, Media>
     */
    public function cursorTemporaryOlderThan(DateTimeInterface $cutoff): LazyCollection
    {
        // @phpstan-ignore-next-line return.type (Eloquent cursor yields Model; rows are always Media)
        return $this->newQuery()
            ->where('is_temporary', true)
            ->where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->cursor();
    }
}
