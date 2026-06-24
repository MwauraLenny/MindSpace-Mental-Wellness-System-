<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointsTest extends TestCase
{
    public function test_health_endpoint_returns_ok_payload(): void
    {
        $response = $this->getJson('/health');

        $response
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure([
                'status',
                'service',
                'timestamp',
            ]);
    }

    public function test_ready_endpoint_returns_expected_structure(): void
    {
        $response = $this->getJson('/ready');

        $this->assertContains($response->status(), [200, 503]);

        $response->assertJsonStructure([
            'status',
            'service',
            'environment',
            'timestamp',
            'checks' => [
                'database',
                'cache',
                'queue_connection',
                'sessions_table',
            ],
        ]);
    }
}
