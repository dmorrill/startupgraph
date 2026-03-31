<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_returns_healthy_when_database_connected(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'healthy',
            'database' => 'connected',
        ]);
        
        $data = $response->json();
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertIsString($data['timestamp']);
    }

    public function test_health_check_returns_degraded_when_database_disconnected(): void
    {
        // Mock DB connection failure
        DB::shouldReceive('connection->getPdo')
            ->once()
            ->andThrow(new \Exception('Connection failed'));

        $response = $this->get('/health');

        $response->assertStatus(503);
        $response->assertJson([
            'status' => 'degraded',
            'database' => 'disconnected',
        ]);
        
        $data = $response->json();
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertIsString($data['timestamp']);
    }

    public function test_health_check_returns_json_response(): void
    {
        $response = $this->get('/health');

        $this->assertJson($response->getContent());
        $response->assertHeader('content-type', 'application/json');
    }

    public function test_health_check_includes_iso8601_timestamp(): void
    {
        $response = $this->get('/health');

        $data = $response->json();
        $timestamp = $data['timestamp'];
        
        // Validate ISO 8601 format
        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}/',
            $timestamp
        );
    }
}