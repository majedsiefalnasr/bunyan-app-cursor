<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\DashboardRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(private DashboardRepository $repository)
    {
    }

    /**
     * @return array{role: string, kpis: array<string, int|float|null>}
     */
    public function getOverview(User $user): array
    {
        $ttl = (int) config('dashboard.cache_ttl_seconds', 60);

        return Cache::remember($this->cacheKey('overview', $user), max(1, $ttl), function () use ($user) {
            return [
                'role' => $user->role->value,
                'kpis' => $this->repository->kpisForUser($user),
            ];
        });
    }

    /**
     * @return array{metrics: array<string, int|float|null>}
     */
    public function getMetrics(User $user): array
    {
        $ttl = (int) config('dashboard.cache_ttl_seconds', 60);

        return Cache::remember($this->cacheKey('metrics', $user), max(1, $ttl), function () use ($user) {
            return [
                'metrics' => $this->repository->kpisForUser($user),
            ];
        });
    }

    public function getRecentActivity(User $user, int $perPage): LengthAwarePaginator
    {
        $global = $user->role === UserRole::Admin;

        return $this->repository->paginateRecentActivity($global, $user->id, $perPage);
    }

    private function cacheKey(string $segment, User $user): string
    {
        return 'dashboard:'.$segment.':'.$user->id;
    }
}
