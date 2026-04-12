<?php

namespace App\Services;

use App\Enums\ActivityLogAction;
use App\Repositories\ActivityLogRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    private const DENYLIST = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function __construct(private ActivityLogRepository $repository)
    {
    }

    public function record(Model $subject, ActivityLogAction $action, ?array $properties = null): void
    {
        $request = request();

        $payload = $properties !== null ? $this->sanitizeProperties($properties) : null;

        $this->repository->create([
            'user_id' => auth()->user()?->id,
            'action' => $action->value,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'properties_json' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateAdmin(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateAdmin($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateForSubject(Model $subject, array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForSubject($subject, $filters);
    }

    public function pruneOlderThanDays(int $days, bool $dryRun = false): int
    {
        if ($days < 1) {
            return 0;
        }

        $cutoff = CarbonImmutable::now()->subDays($days)->startOfDay();

        if ($dryRun) {
            return $this->repository->countOlderThan($cutoff);
        }

        return $this->repository->deleteOlderThan($cutoff);
    }

    /**
     * @return array<string, mixed>
     */
    private function sanitizeProperties(array $properties): array
    {
        if (isset($properties['old'], $properties['new']) && is_array($properties['old']) && is_array($properties['new'])) {
            $old = $this->stripSensitiveKeys($properties['old']);
            $new = $this->stripSensitiveKeys($properties['new']);

            return ['old' => $old, 'new' => $new];
        }

        return $this->stripSensitiveKeys($properties);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function stripSensitiveKeys(array $row): array
    {
        foreach (self::DENYLIST as $key) {
            unset($row[$key]);
        }

        return collect($row)->take(40)->all();
    }
}
