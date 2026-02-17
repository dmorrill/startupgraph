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
    {
        $company = Company::factory()->create();
        FundingRound::factory()->create(['company_id' => $company->id]);

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
    }
}
