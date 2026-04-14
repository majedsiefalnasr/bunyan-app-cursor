<?php

namespace App\Repositories\Analytics;

use App\Models\AnalyticsMetricRollup;
use App\Repositories\BaseRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class AnalyticsMetricRollupRepository extends BaseRepository
{
    protected function model(): string
    {
        return AnalyticsMetricRollup::class;
    }

    /**
     * @return Collection<int, AnalyticsMetricRollup>
     */
    public function series(
        string $metricKey,
        string $bucket,
        CarbonImmutable $bucketStartFromInclusive,
        CarbonImmutable $bucketStartToExclusive,
        string $dimensionsHash = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855' // sha256("{}")
    ): Collection {
        /** @var Collection<int, AnalyticsMetricRollup> $rollups */
        $rollups = $this->newQuery()
            ->where('metric_key', $metricKey)
            ->where('bucket', $bucket)
            ->where('dimensions_hash', $dimensionsHash)
            ->where('bucket_start', '>=', $bucketStartFromInclusive->toDateString())
            ->where('bucket_start', '<', $bucketStartToExclusive->toDateString())
            ->orderBy('bucket_start')
            ->get();

        return $rollups;
    }

    public function upsertRollup(array $attributes, array $values): AnalyticsMetricRollup
    {
        /** @var AnalyticsMetricRollup $model */
        $model = $this->newQuery()->firstOrNew($attributes);
        $model->fill($values);
        $model->save();

        return $model->fresh() ?? $model;
    }

    public function query(): Builder
    {
        return $this->newQuery();
    }
}
