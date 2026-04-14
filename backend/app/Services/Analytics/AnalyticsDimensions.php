<?php

namespace App\Services\Analytics;

final class AnalyticsDimensions
{
    /**
     * @param  array<string, scalar|null>  $dimensions
     */
    public static function canonicalJson(array $dimensions): string
    {
        ksort($dimensions);

        // Flat object only; remove nulls for stability.
        $clean = [];
        foreach ($dimensions as $k => $v) {
            if ($v === null) {
                continue;
            }
            $clean[(string) $k] = $v;
        }

        return json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    /**
     * @param  array<string, scalar|null>  $dimensions
     */
    public static function hash(array $dimensions): string
    {
        return hash('sha256', self::canonicalJson($dimensions));
    }
}
