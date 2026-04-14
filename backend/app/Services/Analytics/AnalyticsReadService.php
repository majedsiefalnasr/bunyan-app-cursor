<?php

namespace App\Services\Analytics;

use App\Enums\AnalyticsMetricKey;
use App\Repositories\Analytics\AnalyticsMetricRollupRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class AnalyticsReadService
{
    private const CACHE_TTL_SECONDS = 300; // ~5m

    private const CACHE_LOCK_TTL_SECONDS = 30;

    public function __construct(
        private readonly AnalyticsMetricRollupRepository $rollups,
    ) {
    }

    /**
     * @return array{range: array{from: string, to: string, bucket: string}, compare: array{mode: string}, kpis: array<int, array<string, mixed>>}
     */
    public function overview(AnalyticsDateRange $range, string $bucket, string $compareMode, int $userId, string $role): array
    {
        $this->audit('analytics.overview', $range, $bucket, $userId, $role, 0);

        $cacheKey = $this->cacheKey('overview', [
            'from' => $range->fromInclusive->toDateString(),
            'to' => $range->toExclusive->subSecond()->toDateString(),
            'bucket' => $bucket,
            'compare' => $compareMode,
        ]);

        return $this->cached($cacheKey, function () use ($range, $bucket, $compareMode) {
            $kpis = [];

            // Minimal initial KPI set (expand in aggregation implementation)
            foreach ([
                AnalyticsMetricKey::PlatformActiveUsers,
                AnalyticsMetricKey::PlatformNewRegistrations,
                AnalyticsMetricKey::CommerceGmv,
                AnalyticsMetricKey::CommerceOrderVolume,
                AnalyticsMetricKey::ProjectsNewProjects,
                AnalyticsMetricKey::SuppliersNewSuppliers,
            ] as $metric) {
                $value = $this->sumRollup($metric->value, $bucket, $range);
                $baseline = $compareMode !== 'none'
                    ? $this->sumRollup($metric->value, $bucket, $this->compareRange($range, $compareMode))
                    : null;

                $kpis[] = [
                    'key' => $metric->value,
                    'label' => $metric->value,
                    'value' => $value,
                    'delta' => $this->delta($value, $baseline),
                ];
            }

            return [
                'range' => [
                    'from' => $range->fromInclusive->toDateString(),
                    'to' => $range->toExclusive->subSecond()->toDateString(),
                    'bucket' => $bucket,
                ],
                'compare' => ['mode' => $compareMode],
                'kpis' => $kpis,
            ];
        });
    }

    /**
     * @return array{range: array{from: string, to: string, bucket: string}, key: string, bucket: string, series: array<int, array{t: string, v: float|null}>}
     */
    public function metricSeries(AnalyticsDateRange $range, string $bucket, AnalyticsMetricKey $metric, int $userId, string $role): array
    {
        $this->audit('analytics.metric', $range, $bucket, $userId, $role, 1);

        $cacheKey = $this->cacheKey('metric', [
            'metric' => $metric->value,
            'from' => $range->fromInclusive->toDateString(),
            'to' => $range->toExclusive->subSecond()->toDateString(),
            'bucket' => $bucket,
        ]);

        return $this->cached($cacheKey, function () use ($range, $bucket, $metric) {
            $bucketFrom = (new AnalyticsBucket($bucket))->normalizeBucketStart($range->fromInclusive);
            $bucketTo = (new AnalyticsBucket($bucket))->normalizeBucketStart($range->toExclusive);

            $rollups = $this->rollups->series($metric->value, $bucket, $bucketFrom, $bucketTo);
            $series = $rollups->map(fn ($r) => [
                't' => (string) $r->bucket_start,
                'v' => $this->asNumber($r->value),
            ])->values()->all();

            return [
                'range' => [
                    'from' => $range->fromInclusive->toDateString(),
                    'to' => $range->toExclusive->subSecond()->toDateString(),
                    'bucket' => $bucket,
                ],
                'key' => $metric->value,
                'bucket' => $bucket,
                'series' => $series,
            ];
        });
    }

    /**
     * @param  list<AnalyticsMetricKey>  $keys
     * @return array{range: array{from: string, to: string, bucket: string}, bucket: string, series: array<int, array{key: string, points: array<int, array{t: string, v: float|null}>}>}
     */
    public function trends(AnalyticsDateRange $range, string $bucket, array $keys, int $userId, string $role): array
    {
        $this->audit('analytics.trends', $range, $bucket, $userId, $role, count($keys));

        $cacheKey = $this->cacheKey('trends', [
            'keys' => implode(',', array_map(fn ($k) => $k->value, $keys)),
            'from' => $range->fromInclusive->toDateString(),
            'to' => $range->toExclusive->subSecond()->toDateString(),
            'bucket' => $bucket,
        ]);

        return $this->cached($cacheKey, function () use ($range, $bucket, $keys) {
            $bucketFrom = (new AnalyticsBucket($bucket))->normalizeBucketStart($range->fromInclusive);
            $bucketTo = (new AnalyticsBucket($bucket))->normalizeBucketStart($range->toExclusive);

            $series = [];
            foreach ($keys as $key) {
                $rollups = $this->rollups->series($key->value, $bucket, $bucketFrom, $bucketTo);
                $points = $rollups->map(fn ($r) => [
                    't' => (string) $r->bucket_start,
                    'v' => $this->asNumber($r->value),
                ])->values()->all();

                $series[] = [
                    'key' => $key->value,
                    'points' => $points,
                ];
            }

            return [
                'range' => [
                    'from' => $range->fromInclusive->toDateString(),
                    'to' => $range->toExclusive->subSecond()->toDateString(),
                    'bucket' => $bucket,
                ],
                'bucket' => $bucket,
                'series' => $series,
            ];
        });
    }

    private function sumRollup(string $metricKey, string $bucket, AnalyticsDateRange $range): float
    {
        $bucketFrom = (new AnalyticsBucket($bucket))->normalizeBucketStart($range->fromInclusive);
        $bucketTo = (new AnalyticsBucket($bucket))->normalizeBucketStart($range->toExclusive);

        $sum = $this->rollups->query()
            ->where('metric_key', $metricKey)
            ->where('bucket', $bucket)
            ->where('dimensions_hash', hash('sha256', '{}'))
            ->where('bucket_start', '>=', $bucketFrom->toDateString())
            ->where('bucket_start', '<', $bucketTo->toDateString())
            ->sum('value');

        return (float) $sum;
    }

    /**
     * @return array{value: float|null, pct: float|null}
     */
    private function delta(float $value, ?float $baseline): array
    {
        if ($baseline === null) {
            return ['value' => null, 'pct' => null];
        }

        $deltaValue = $value - $baseline;
        $pct = $baseline == 0.0 ? null : ($deltaValue / $baseline);

        return [
            'value' => $deltaValue,
            'pct' => $pct,
        ];
    }

    private function compareRange(AnalyticsDateRange $range, string $mode): AnalyticsDateRange
    {
        $from = $range->fromInclusive;
        $toExclusive = $range->toExclusive;
        $days = $from->diffInDays($toExclusive);

        return match ($mode) {
            'previous_year' => new AnalyticsDateRange($from->subYear(), $toExclusive->subYear()),
            'previous_period' => new AnalyticsDateRange($from->subDays($days), $toExclusive->subDays($days)),
            default => $range,
        };
    }

    private function cacheKey(string $type, array $parts): string
    {
        return 'analytics:'.$type.':'.hash('sha256', json_encode($parts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
    }

    /**
     * @template T
     *
     * @param  callable():T  $compute
     * @return T
     */
    private function cached(string $cacheKey, callable $compute)
    {
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $lock = Cache::lock("analytics:cache:lock:{$cacheKey}", self::CACHE_LOCK_TTL_SECONDS);
        if (! $lock->get()) {
            // stale-while-revalidate: try a few bounded retries
            for ($i = 0; $i < 3; $i++) {
                usleep(random_int(50_000, 150_000));
                $cachedRetry = Cache::get($cacheKey);
                if (is_array($cachedRetry)) {
                    return $cachedRetry;
                }
            }

            // Serve empty payload shape rather than live aggregate
            /** @var mixed $empty */
            $empty = $compute();

            return $empty;
        }

        try {
            /** @var mixed $value */
            $value = $compute();
            Cache::put($cacheKey, $value, self::CACHE_TTL_SECONDS);

            return $value;
        } finally {
            $lock->release();
        }
    }

    private function audit(string $action, AnalyticsDateRange $range, string $bucket, int $userId, string $role, int $keysCount): void
    {
        Log::channel('audit')->info('Analytics read', [
            'action' => $action,
            'user_id' => $userId,
            'role' => $role,
            'from' => $range->fromInclusive->toDateString(),
            'to' => $range->toExclusive->subSecond()->toDateString(),
            'bucket' => $bucket,
            'keys_count' => $keysCount,
        ]);
    }

    private function asNumber(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        return (float) $value;
    }
}
