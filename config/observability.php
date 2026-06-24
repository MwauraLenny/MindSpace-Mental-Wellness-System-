<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Slow Request Threshold (ms)
    |--------------------------------------------------------------------------
    |
    | Requests slower than this threshold are logged at warning level with
    | route and status details to support production performance analysis.
    |
    */

    'slow_request_ms' => (int) env('OBSERVABILITY_SLOW_REQUEST_MS', 1000),

    /*
    |--------------------------------------------------------------------------
    | Health Check Cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | Small cache windows help avoid expensive readiness checks being repeated
    | too frequently under high-volume probe traffic.
    |
    */

    'health_cache_ttl_seconds' => (int) env('HEALTH_CACHE_TTL_SECONDS', 5),
];
