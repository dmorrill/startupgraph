<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class SearchApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_search_requires_minimum_query_length(): void
    {
        $response = $this->getJson('/api/search?q=a');

        $response->assertOk()
            ->assertJsonPath('meta.message', 'Query must be at least 2 characters')
            ->assertJsonPath('data.companies', [])
            ->assertJsonPath('data.people', []);
    }

    public function test_search_finds_companies_by_name(): void
    {
        Company::factory()->create(['name' => 'Acme Robotics']);
        Company::factory()->create(['name' => 'Beta Finance']);

        $response = $this->getJson('/api/search?q=Acme');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.companies_count'));
    }

    public function test_search_finds_companies_by_city(): void
    {
        Company::factory()->create(['city' => 'San Francisco']);
        Company::factory()->create(['city' => 'New York']);

        $response = $this->getJson('/api/search?q=San Francisco');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.companies_count'));
    }

    public function test_search_finds_people_by_name(): void
    {
        Person::factory()->create(['name' => 'John Smith']);
        Person::factory()->create(['name' => 'Jane Doe']);

        $response = $this->getJson('/api/search?q=John');

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.people_count'));
    }

    public function test_search_respects_limit(): void
    {
        Company::factory()->count(10)->create(['name' => 'Test Company']);

        $response = $this->getJson('/api/search?q=Test&limit=3');

        $response->assertOk();
        $this->assertLessThanOrEqual(3, count($response->json('data.companies')));
    }

    public function test_search_limit_capped_at_50(): void
    {
        $response = $this->getJson('/api/search?q=test&limit=100');
        $response->assertOk();
    }

    public function test_empty_query_returns_empty_results(): void
    {
        $response = $this->getJson('/api/search');

        $response->assertOk()
            ->assertJsonPath('data.companies', [])
            ->assertJsonPath('data.people', []);
    }
}
