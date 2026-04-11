<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    abstract protected function model(): string;

    protected function newQuery(): Builder
    {
        /** @var Model $instance */
        $instance = app($this->model());

        return $instance->newQuery();
    }

    public function findById(int $id): ?Model
    {
        return $this->newQuery()->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        $model = $this->findById($id);

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel($this->model(), $id);
        }

        return $model;
    }

    public function all(array $filters = []): LengthAwarePaginator
    {
        return $this->paginate($this->newQuery(), (int) ($filters['per_page'] ?? 15));
    }

    public function create(array $data): Model
    {
        return $this->newQuery()->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->fresh() ?? $model;
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function restore(int $id): Model
    {
        $model = $this->newQuery()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->find($id);

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel($this->model(), $id);
        }

        /** @phpstan-ignore-next-line */
        $model->restore();

        return $model->fresh() ?? $model;
    }

    protected function paginate(Builder $query, int $perPage = 15): LengthAwarePaginator
    {
        return $query->orderByDesc('created_at')->paginate($perPage);
    }
}
