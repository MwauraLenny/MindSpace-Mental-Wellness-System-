<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestMetricsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        $response->headers->set('Server-Timing', 'app;dur='.$durationMs);
        $response->headers->set('X-Response-Time', $durationMs.'ms');

        $this->recordRequestMetrics($request, $response, $durationMs);

        $slowThreshold = config('observability.slow_request_ms', 1000);
        if ($durationMs >= $slowThreshold) {
            Log::warning('Slow request detected', [
                'method' => $request->method(),
                'path' => '/'.ltrim($request->path(), '/'),
                'route' => optional($request->route())->getName(),
                'status' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
            ]);
        }

        return $response;
    }

    private function recordRequestMetrics(Request $request, Response $response, int $durationMs): void
    {
        try {
            $minuteBucket = now()->format('YmdHi');
            $ttl = now()->addMinutes(10);

            $requestsKey = 'metrics:http:requests:'.$minuteBucket;
            $slowKey = 'metrics:http:slow:'.$minuteBucket;
            $errorsKey = 'metrics:http:errors:'.$minuteBucket;
            $durationKey = 'metrics:http:duration-sum-ms:'.$minuteBucket;

            Cache::add($requestsKey, 0, $ttl);
            Cache::increment($requestsKey);

            Cache::add($durationKey, 0, $ttl);
            Cache::increment($durationKey, $durationMs);

            if ($durationMs >= (int) config('observability.slow_request_ms', 1000)) {
                Cache::add($slowKey, 0, $ttl);
                Cache::increment($slowKey);
            }

            if ($response->getStatusCode() >= 500) {
                Cache::add($errorsKey, 0, $ttl);
                Cache::increment($errorsKey);
            }

            $routeName = optional($request->route())->getName() ?: 'unnamed';
            $routeKey = 'metrics:http:route:'.$routeName.':'.$minuteBucket;
            Cache::add($routeKey, 0, $ttl);
            Cache::increment($routeKey);
        } catch (Throwable) {
            // Never break user traffic if metrics storage fails.
        }
    }
}
