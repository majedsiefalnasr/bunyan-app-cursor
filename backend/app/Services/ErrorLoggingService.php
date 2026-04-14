<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class ErrorLoggingService
{
    public function log(array $attributes): ?ErrorLog
    {
        if (! Schema::hasTable('error_logs')) {
            return null;
        }

        return ErrorLog::query()->create($attributes);
    }

    /**
     * @return Collection<int, ErrorLog>
     */
    public function findByCorrelationId(string $correlationId)
    {
        if (! Schema::hasTable('error_logs')) {
            /** @var Collection<int, ErrorLog> $empty */
            $empty = new Collection;

            return $empty;
        }

        return ErrorLog::query()->where('correlation_id', $correlationId)->orderByDesc('id')->get();
    }

    /**
     * @return array<string, int>
     */
    public function getStatistics(int $sinceHours = 24): array
    {
        if (! Schema::hasTable('error_logs')) {
            return [];
        }

        $since = now()->subHours($sinceHours);

        return ErrorLog::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('error_code, COUNT(*) as c')
            ->groupBy('error_code')
            ->pluck('c', 'error_code')
            ->all();
    }
}
