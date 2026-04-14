<?php

namespace App\Repositories\Analytics;

use App\Models\AnalyticsEvent;
use App\Repositories\BaseRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class AnalyticsEventRepository extends BaseRepository
{
    protected function model(): string
    {
        return AnalyticsEvent::class;
    }

    public function queryBetween(CarbonImmutable $fromInclusive, CarbonImmutable $toExclusive): Builder
    {
        return $this->newQuery()
            ->where('occurred_at', '>=', $fromInclusive)
            ->where('occurred_at', '<', $toExclusive);
    }

    /**
     * @return Collection<int, AnalyticsEvent>
     */
    public function getByNameBetween(string $eventName, CarbonImmutable $fromInclusive, CarbonImmutable $toExclusive): Collection
    {
        /** @var Collection<int, AnalyticsEvent> $events */
        $events = $this->queryBetween($fromInclusive, $toExclusive)
            ->where('event_name', $eventName)
            ->get();

        return $events;
    }
}
