<?php

namespace App\Http\Controllers\Api\V1\Analytics;

use App\Enums\AnalyticsMetricKey;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Analytics\AnalyticsMetricRequest;
use App\Http\Requests\Analytics\AnalyticsOverviewRequest;
use App\Http\Requests\Analytics\AnalyticsTrendsRequest;
use App\Http\Resources\Api\V1\Analytics\AnalyticsKpiResource;
use App\Services\Analytics\AnalyticsBucket;
use App\Services\Analytics\AnalyticsDateRange;
use App\Services\Analytics\AnalyticsReadService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class AnalyticsController extends BaseController
{
    public function __construct(
        private readonly AnalyticsReadService $read,
    ) {
    }

    public function overview(AnalyticsOverviewRequest $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $range = $this->rangeFromRequest($request->validated());
        $bucket = (string) ($request->validated('bucket') ?? 'day');
        $compare = (string) ($request->validated('compare') ?? 'none');

        $this->guardPointsCap($bucket, $range);
        $data = $this->read->overview($range, $bucket, $compare, $user->id, $user->role->value);
        $kpis = $data['kpis'];
        $data['kpis'] = AnalyticsKpiResource::collection(collect($kpis))->resolve();

        return $this->sendSuccess($data, 'OK');
    }

    public function metric(string $metric, AnalyticsMetricRequest $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $metricKey = AnalyticsMetricKey::tryFrom($metric);
        if ($metricKey === null) {
            abort(404);
        }

        $range = $this->rangeFromRequest($request->validated());
        $bucket = (string) ($request->validated('bucket') ?? 'day');

        $this->guardPointsCap($bucket, $range);
        $data = $this->read->metricSeries($range, $bucket, $metricKey, $user->id, $user->role->value);

        return $this->sendSuccess($data, 'OK');
    }

    public function trends(AnalyticsTrendsRequest $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $range = $this->rangeFromRequest($request->validated());
        $bucket = (string) ($request->validated('bucket') ?? 'day');

        $rawKeys = (array) $request->validated('keys');
        $keys = [];
        foreach ($rawKeys as $k) {
            $metricKey = AnalyticsMetricKey::tryFrom((string) $k);
            if ($metricKey !== null) {
                $keys[] = $metricKey;
            }
        }

        if ($keys === []) {
            throw ValidationException::withMessages([
                'keys' => ['No valid metric keys provided.'],
            ]);
        }

        $this->guardPointsCap($bucket, $range);
        $data = $this->read->trends($range, $bucket, $keys, $user->id, $user->role->value);

        return $this->sendSuccess($data, 'OK');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function rangeFromRequest(array $validated): AnalyticsDateRange
    {
        $today = CarbonImmutable::now('UTC')->startOfDay();
        $to = isset($validated['to']) ? CarbonImmutable::parse($validated['to'], 'UTC') : $today;
        $from = isset($validated['from']) ? CarbonImmutable::parse($validated['from'], 'UTC') : $to->subDays(13);

        return AnalyticsDateRange::fromDates($from, $to);
    }

    private function guardPointsCap(string $bucket, AnalyticsDateRange $range): void
    {
        $points = (new AnalyticsBucket($bucket))->estimatePoints($range);
        if ($points > 400) {
            throw ValidationException::withMessages([
                'bucket' => ['Too many points for requested range/bucket. Choose a larger bucket or narrower date range.'],
            ]);
        }
    }
}
