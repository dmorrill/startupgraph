<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\HeadcountSnapshot;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

<<<<<<< HEAD
    public function test_index_returns_paginated_companies(): void
    {
        Company::factory()->count(5)->create();

        $response = $this->getJson('/api/companies');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['source', 'version', 'generated_at'],
                'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
            ]);

        $this->assertEquals(5, $response->json('pagination.total'));
    }

    public function test_index_search_by_name(): void
    {
        Company::factory()->create(['name' => 'Acme AI']);
        Company::factory()->create(['name' => 'Beta Corp']);

        $response = $this->getJson('/api/companies?q=Acme');

        $response->assertOk();
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    public function test_index_filter_by_country(): void
    {
        Company::factory()->create(['country' => 'US']);
        Company::factory()->create(['country' => 'UK']);

        $response = $this->getJson('/api/companies?country=US');

        $response->assertOk();
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    public function test_index_filter_by_category(): void
    {
        Company::factory()->create(['category' => 'ai_ml']);
        Company::factory()->create(['category' => 'fintech']);

        $response = $this->getJson('/api/companies?category=ai_ml');

        $response->assertOk();
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    public function test_index_filter_by_funding_stage(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->create([
            'company_id' => $company->id,
            'round_type' => 'Series A',
            'announced_date' => now(),
        ]);
        $other = Company::factory()->create();
        FundingRound::factory()->create([
            'company_id' => $other->id,
            'round_type' => 'Seed',
            'announced_date' => now(),
        ]);

        $response = $this->getJson('/api/companies?funding_stage=Series A');
        $response->assertOk();
    }

    public function test_index_sorting(): void
    {
        Company::factory()->create(['name' => 'Zebra']);
        Company::factory()->create(['name' => 'Alpha']);

        $response = $this->getJson('/api/companies?sort=name&order=asc');
        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertEquals('Alpha', $names[0]);
    }

    public function test_index_per_page_limit(): void
    {
        Company::factory()->count(5)->create();

        $response = $this->getJson('/api/companies?per_page=2');
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_per_page_max_100(): void
    {
        $response = $this->getJson('/api/companies?per_page=200');
        $response->assertOk();
        $this->assertEquals(100, $response->json('pagination.per_page'));
    }

    public function test_show_returns_company_detail(): void
=======
    public function test_companies_index(): void
    {
        Company::factory()->count(3)->create();

        $response = $this->getJson('/api/companies');

        $response->assertStatus(200);
    }

    public function test_companies_show(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->create(['company_id' => $company->id, 'amount' => 5000000]);

        $response = $this->getJson("/api/companies/{$company->slug}");

        $response->assertStatus(200);
    }

    public function test_companies_funding(): void
>>>>>>> origin/main
    {
        $company = Company::factory()->create();
        FundingRound::factory()->create(['company_id' => $company->id]);

<<<<<<< HEAD
        $response = $this->getJson("/api/companies/{$company->slug}");

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['source', 'version', 'generated_at'],
            ]);
    }

    public function test_show_404_for_missing_company(): void
    {
        $response = $this->getJson('/api/companies/nonexistent-slug');
        $response->assertNotFound();
    }

    public function test_funding_endpoint(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->count(2)->create(['company_id' => $company->id]);

        $response = $this->getJson("/api/companies/{$company->slug}/funding");

        $response->assertOk()
            ->assertJsonPath('data.rounds_count', 2);
    }

    public function test_people_endpoint(): void
    {
        $company = Company::factory()->create();
        $person = Person::factory()->create();
        $company->people()->attach($person, ['role' => 'CEO', 'is_current' => true]);

        $response = $this->getJson("/api/companies/{$company->slug}/people");

        $response->assertOk()
            ->assertJsonPath('data.total_count', 1)
            ->assertJsonPath('data.current_count', 1);
    }

    public function test_headcount_endpoint(): void
    {
        $company = Company::factory()->create(['current_headcount' => 100]);
        HeadcountSnapshot::factory()->create(['company_id' => $company->id, 'headcount' => 50, 'recorded_date' => now()->subMonths(6)]);
        HeadcountSnapshot::factory()->create(['company_id' => $company->id, 'headcount' => 100, 'recorded_date' => now()]);

        $response = $this->getJson("/api/companies/{$company->slug}/headcount");

        $response->assertOk()
            ->assertJsonPath('data.current_headcount', 100)
            ->assertJsonPath('data.snapshots_count', 2)
            ->assertJsonPath('data.growth_percent', 100);
    }

    public function test_funded_after_filter(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->create([
            'company_id' => $company->id,
            'announced_date' => '2024-06-01',
        ]);
        $old = Company::factory()->create();
        FundingRound::factory()->create([
            'company_id' => $old->id,
            'announced_date' => '2020-01-01',
        ]);

        $response = $this->getJson('/api/companies?funded_after=2024-01-01');
        $response->assertOk();
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    public function test_funded_recent_filter(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->create([
            'company_id' => $company->id,
            'announced_date' => now()->subMonth(),
        ]);

        $response = $this->getJson('/api/companies?funded_recent=3m');
        $response->assertOk();
        $this->assertEquals(1, $response->json('pagination.total'));
=======
        $response = $this->getJson("/api/companies/{$company->slug}/funding");

        $response->assertStatus(200);
    }

    public function test_companies_headcount(): void
    {
        $company = Company::factory()->create();
        HeadcountSnapshot::factory()->create(['company_id' => $company->id]);

        $response = $this->getJson("/api/companies/{$company->slug}/headcount");

        $response->assertStatus(200);
    }

    public function test_companies_people(): void
    {
        $company = Company::factory()->create();
        $person = Person::factory()->create();
        $company->people()->attach($person->id, ['role' => 'CEO', 'is_current' => true]);

        $response = $this->getJson("/api/companies/{$company->slug}/people");

        $response->assertStatus(200);
    }

    public function test_search_endpoint(): void
    {
        Company::factory()->create(['name' => 'Acme AI Corp']);

        $response = $this->getJson('/api/search?q=Acme');

        $response->assertStatus(200);
    }

    public function test_stats_endpoint(): void
    {
        $response = $this->getJson('/api/stats');

        $response->assertStatus(200);
    }

    public function test_categories_endpoint(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);
>>>>>>> origin/main
    }
}
