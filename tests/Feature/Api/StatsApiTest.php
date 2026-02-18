<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class StatsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_stats_returns_counts(): void
    {
        Company::factory()->count(3)->create();
        Person::factory()->count(5)->create();
        FundingRound::factory()->count(2)->create();

        $response = $this->getJson('/api/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'companies_count',
                    'people_count',
                    'funding_rounds_count',
                    'total_funding_tracked',
                    'total_funding_formatted',
                    'categories',
                    'countries',
                ],
                'meta',
            ]);

        $this->assertGreaterThanOrEqual(3, $response->json('data.companies_count'));
        $this->assertGreaterThanOrEqual(5, $response->json('data.people_count'));
    }

    public function test_stats_empty_database(): void
    {
        $response = $this->getJson('/api/stats');

        $response->assertOk()
            ->assertJsonPath('data.companies_count', 0)
            ->assertJsonPath('data.people_count', 0);
    }
}
