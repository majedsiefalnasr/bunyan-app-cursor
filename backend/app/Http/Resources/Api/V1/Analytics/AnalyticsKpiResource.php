<?php

namespace App\Http\Resources\Api\V1\Analytics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array{key: string, label: string, value: float|null, delta: array{value: float|null, pct: float|null}|null} $resource
 */
final class AnalyticsKpiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $r */
        $r = $this->resource;
        $delta = is_array($r['delta'] ?? null) ? $r['delta'] : null;

        return [
            'key' => (string) ($r['key'] ?? ''),
            'label' => (string) ($r['label'] ?? ''),
            'value' => isset($r['value']) ? (is_numeric($r['value']) ? (float) $r['value'] : null) : null,
            'delta' => $delta === null ? null : [
                'value' => isset($delta['value']) && is_numeric($delta['value']) ? (float) $delta['value'] : null,
                'pct' => isset($delta['pct']) && is_numeric($delta['pct']) ? (float) $delta['pct'] : null,
            ],
        ];
    }
}
