<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class CategoriesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_categories_endpoint_returns_list(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['key', 'label']],
                'meta' => ['source', 'version'],
            ]);

        $this->assertCount(10, $response->json('data'));
    }
}
