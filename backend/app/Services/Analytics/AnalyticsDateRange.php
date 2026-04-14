<?php

namespace App\Services\Analytics;

use Carbon\CarbonImmutable;

final readonly class AnalyticsDateRange
{
    public function __construct(
        public CarbonImmutable $fromInclusive,
        public CarbonImmutable $toExclusive,
    ) {
    }

    public static function fromDates(CarbonImmutable $fromDateUtc, CarbonImmutable $toDateUtc): self
    {
        $fromInclusive = $fromDateUtc->startOfDay();
        $toExclusive = $toDateUtc->addDay()->startOfDay();

        return new self($fromInclusive, $toExclusive);
    }
}
