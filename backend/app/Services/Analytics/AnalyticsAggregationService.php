<?php

namespace App\Services\Analytics;

use App\Enums\AnalyticsMetricKey;
use App\Repositories\Analytics\AnalyticsMetricRollupRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class AnalyticsAggregationService
{
    public function __construct(
        private readonly AnalyticsMetricRollupRepository $rollups,
    ) {
    }

    /**
     * Recompute rollups for a rolling window (UTC dates).
     */
    public function aggregate(CarbonImmutable $fromDateUtc, CarbonImmutable $toDateUtc, string $bucket = 'day'): void
    {
        $bucketper = new AnalyticsBucket($bucket);
        $dimensions = [];
        $dimensionsHash = AnalyticsDimensions::hash($dimensions);
        $dimensionsJson = AnalyticsDimensions::canonicalJson($dimensions);

        $cursor = $bucketper->normalizeBucketStart($fromDateUtc);
        $endExclusive = $toDateUtc->addDay()->startOfDay();

        while ($cursor->lessThan($endExclusive)) {
            $next = $bucketper->nextBucketStart($cursor);

            $bucketStart = $cursor->toDateString();
            $bucketEnd = $next->toDateString();

            foreach (AnalyticsMetricKey::cases() as $metric) {
                $value = $this->computeMetric($metric, $cursor, $next);

                $this->rollups->upsertRollup(
                    [
                        'metric_key' => $metric->value,
                        'bucket' => $bucket,
                        'bucket_start' => $bucketStart,
                        'dimensions_hash' => $dimensionsHash,
                    ],
                    [
                        'bucket_end' => $bucketEnd,
                        'value' => $value,
                        'dimensions' => json_decode($dimensionsJson, true) ?? [],
                        'computed_at' => CarbonImmutable::now('UTC'),
                        'dimensions_hash' => $dimensionsHash,
                    ],
                );
            }

            $cursor = $next;
        }
    }

    private function computeMetric(AnalyticsMetricKey $metric, CarbonImmutable $fromInclusive, CarbonImmutable $toExclusive): float
    {
        return match ($metric) {
            AnalyticsMetricKey::PlatformActiveUsers => (float) DB::table('analytics_events')
                ->where('event_name', 'session.started')
                ->where('occurred_at', '>=', $fromInclusive)
                ->where('occurred_at', '<', $toExclusive)
                ->whereNotNull('user_id')
                ->distinct()
                ->count('user_id'),

            AnalyticsMetricKey::PlatformNewRegistrations => (float) DB::table('analytics_events')
                ->where('event_name', 'user.registered')
                ->where('occurred_at', '>=', $fromInclusive)
                ->where('occurred_at', '<', $toExclusive)
                ->count(),

            AnalyticsMetricKey::CommerceOrderVolume => (float) DB::table('analytics_events')
                ->where('event_name', 'order.placed')
                ->where('occurred_at', '>=', $fromInclusive)
                ->where('occurred_at', '<', $toExclusive)
                ->count(),

            // NOTE: For MVP we compute GMV/avg-order-value from domain table when available.
            AnalyticsMetricKey::CommerceGmv => (float) DB::table('orders')
                ->where('created_at', '>=', $fromInclusive)
                ->where('created_at', '<', $toExclusive)
                ->sum('total_amount'),

            AnalyticsMetricKey::CommerceAvgOrderValue => $this->safeDivide(
                (float) DB::table('orders')
                    ->where('created_at', '>=', $fromInclusive)
                    ->where('created_at', '<', $toExclusive)
                    ->sum('total_amount'),
                (float) DB::table('orders')
                    ->where('created_at', '>=', $fromInclusive)
                    ->where('created_at', '<', $toExclusive)
                    ->count(),
            ),

            AnalyticsMetricKey::CommerceConversionRate => $this->safeDivide(
                (float) DB::table('analytics_events')
                    ->where('event_name', 'order.placed')
                    ->where('occurred_at', '>=', $fromInclusive)
                    ->where('occurred_at', '<', $toExclusive)
                    ->count(),
                (float) DB::table('analytics_events')
                    ->where('event_name', 'session.started')
                    ->where('occurred_at', '>=', $fromInclusive)
                    ->where('occurred_at', '<', $toExclusive)
                    ->count(),
            ),

            AnalyticsMetricKey::ProjectsNewProjects => (float) DB::table('projects')
                ->where('created_at', '>=', $fromInclusive)
                ->where('created_at', '<', $toExclusive)
                ->count(),

            AnalyticsMetricKey::SuppliersNewSuppliers => (float) DB::table('supplier_profiles')
                ->where('created_at', '>=', $fromInclusive)
                ->where('created_at', '<', $toExclusive)
                ->count(),

            // Not implemented yet: set to 0.0 (rollups exist, values filled later)
            default => 0.0,
        };
    }

    private function safeDivide(float $num, float $den): float
    {
        if ($den == 0.0) {
            return 0.0;
        }

        return $num / $den;
    }
}
