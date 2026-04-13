<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dashboard aggregate cache TTL
    |--------------------------------------------------------------------------
    |
    | Seconds to cache per-user overview and metrics responses. Recent activity
    | is not cached to avoid pagination serialization issues.
    |
    */
    'cache_ttl_seconds' => (int) env('DASHBOARD_CACHE_TTL', 60),
];
