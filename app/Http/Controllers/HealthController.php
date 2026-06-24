<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HealthController extends Controller
{
    public function liveness(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name', 'MindSpace'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function readiness(): JsonResponse
    {
        $ttl = max(1, (int) config('observability.health_cache_ttl_seconds', 5));

        $payload = Cache::remember('health:readiness-payload', now()->addSeconds($ttl), function (): array {
            $checks = [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'queue_connection' => $this->checkQueueConnection(),
                'sessions_table' => $this->checkSessionsTable(),
            ];

            $isReady = collect($checks)->every(fn (bool $result): bool => $result);

            return [
                'status' => $isReady ? 'ready' : 'degraded',
                'service' => config('app.name', 'MindSpace'),
                'environment' => config('app.env'),
                'timestamp' => now()->toIso8601String(),
                'checks' => $checks,
            ];
        });

        return response()->json($payload, $payload['status'] === 'ready' ? 200 : 503);
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function checkCache(): bool
    {
        try {
            $key = 'health:cache:probe';
            $value = (string) now()->timestamp;

            Cache::put($key, $value, now()->addSeconds(30));
            $probe = Cache::get($key);
            Cache::forget($key);

            return $probe === $value;
        } catch (Throwable) {
            return false;
        }
    }

    private function checkQueueConnection(): bool
    {
        try {
            Queue::connection(config('queue.default'));

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function checkSessionsTable(): bool
    {
        try {
            return Schema::hasTable(config('session.table', 'sessions'));
        } catch (Throwable) {
            return false;
        }
    }
}
