<?php

namespace App\Services\Analytics;

use Carbon\CarbonImmutable;

final readonly class AnalyticsBucket
{
    public function __construct(
        public string $bucket,
    ) {
    }

    public function normalizeBucketStart(CarbonImmutable $dtUtc): CarbonImmutable
    {
        return match ($this->bucket) {
            'day' => $dtUtc->startOfDay(),
            'week' => $dtUtc->startOfWeek(CarbonImmutable::MONDAY)->startOfDay(),
            'month' => $dtUtc->startOfMonth()->startOfDay(),
            default => $dtUtc->startOfDay(),
        };
    }

    public function nextBucketStart(CarbonImmutable $bucketStartUtc): CarbonImmutable
    {
        return match ($this->bucket) {
            'day' => $bucketStartUtc->addDay(),
            'week' => $bucketStartUtc->addWeek(),
            'month' => $bucketStartUtc->addMonth(),
            default => $bucketStartUtc->addDay(),
        };
    }

    public function estimatePoints(AnalyticsDateRange $range): int
    {
        $start = $this->normalizeBucketStart($range->fromInclusive);
        $end = $range->toExclusive;
        $count = 0;

        while ($start->lessThan($end)) {
            $count++;
            $start = $this->nextBucketStart($start);
            if ($count > 5000) {
                break;
            }
        }

        return $count;
    }
}
