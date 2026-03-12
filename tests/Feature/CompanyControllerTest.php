<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\HeadcountSnapshot;
use App\Models\NewsMention;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    // --- Index page ---

    public function test_companies_index_returns_200(): void
    {
        $response = $this->get('/companies');
        $response->assertStatus(200);
    }

    public function test_companies_index_shows_company_names(): void
    {
        Company::factory()->create(['name' => 'Acme Corp']);

        $response = $this->get('/companies');

        $response->assertStatus(200);
        $response->assertSee('Acme Corp');
    }

    public function test_companies_index_with_no_companies_still_loads(): void
    {
        $response = $this->get('/companies');

        $response->assertStatus(200);
    }

    public function test_companies_index_search_filters_by_name(): void
    {
        Company::factory()->create(['name' => 'TargetCo']);
        Company::factory()->create(['name' => 'OtherCo']);

        $response = $this->get('/companies?search=TargetCo');

        $response->assertStatus(200);
        $response->assertSee('TargetCo');
        $response->assertDontSee('OtherCo');
    }

    public function test_companies_index_search_filters_by_description(): void
    {
        Company::factory()->create(['name' => 'AlphaInc', 'description' => 'unique-search-term-xyz']);
        Company::factory()->create(['name' => 'BetaInc', 'description' => 'nothing special']);

        $response = $this->get('/companies?search=unique-search-term-xyz');

        $response->assertStatus(200);
        $response->assertSee('AlphaInc');
        $response->assertDontSee('BetaInc');
    }

    public function test_companies_index_filter_by_country(): void
    {
        Company::factory()->create(['name' => 'UKCo', 'country' => 'United Kingdom']);
        Company::factory()->create(['name' => 'USCo', 'country' => 'United States']);

        $response = $this->get('/companies?country=United+Kingdom');

        $response->assertStatus(200);
        $response->assertSee('UKCo');
        $response->assertDontSee('USCo');
    }

    public function test_companies_index_filter_by_category(): void
    {
        Company::factory()->create(['name' => 'AIStartup', 'category' => 'ai_ml']);
        Company::factory()->create(['name' => 'FinanceCo', 'category' => 'fintech']);

        $response = $this->get('/companies?category=ai_ml');

        $response->assertStatus(200);
        $response->assertSee('AIStartup');
        $response->assertDontSee('FinanceCo');
    }

    public function test_companies_index_sort_by_name_asc(): void
    {
        Company::factory()->create(['name' => 'Zebra Corp']);
        Company::factory()->create(['name' => 'Alpha Inc']);

        $response = $this->get('/companies?sort=name&direction=asc');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Alpha Inc', 'Zebra Corp']);
    }

    public function test_companies_index_sort_by_name_desc(): void
    {
        Company::factory()->create(['name' => 'Zebra Corp']);
        Company::factory()->create(['name' => 'Alpha Inc']);

        $response = $this->get('/companies?sort=name&direction=desc');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Zebra Corp', 'Alpha Inc']);
    }

    public function test_companies_index_funded_after_filter(): void
    {
        $funded = Company::factory()->create(['name' => 'FundedCo']);
        FundingRound::factory()->create(['company_id' => $funded->id, 'announced_date' => '2024-06-01']);

        $unfunded = Company::factory()->create(['name' => 'UnfundedCo']);

        $response = $this->get('/companies?funded_after=2024-01-01');

        $response->assertStatus(200);
        $response->assertSee('FundedCo');
        $response->assertDontSee('UnfundedCo');
    }

    public function test_companies_index_funded_before_filter(): void
    {
        $old = Company::factory()->create(['name' => 'OldFundedCo']);
        FundingRound::factory()->create(['company_id' => $old->id, 'announced_date' => '2020-01-01']);

        $recent = Company::factory()->create(['name' => 'RecentCo']);
        FundingRound::factory()->create(['company_id' => $recent->id, 'announced_date' => '2024-06-01']);

        $response = $this->get('/companies?funded_before=2021-01-01');

        $response->assertStatus(200);
        $response->assertSee('OldFundedCo');
        $response->assertDontSee('RecentCo');
    }

    public function test_companies_index_invalid_sort_field_is_ignored(): void
    {
        Company::factory()->create(['name' => 'SafeCo']);

        $response = $this->get('/companies?sort=password&direction=asc');

        $response->assertStatus(200);
    }

    // --- Show page ---

    public function test_company_show_returns_200(): void
    {
        $company = Company::factory()->create();

        $response = $this->get("/companies/{$company->slug}");

        $response->assertStatus(200);
    }

    public function test_company_show_displays_company_name(): void
    {
        $company = Company::factory()->create(['name' => 'Visible Corp']);

        $response = $this->get("/companies/{$company->slug}");

        $response->assertStatus(200);
        $response->assertSee('Visible Corp');
    }

    public function test_company_show_with_funding_rounds(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->create([
            'company_id' => $company->id,
            'round_type' => 'Series A',
            'amount' => 10000000,
        ]);

        $response = $this->get("/companies/{$company->slug}");

        $response->assertStatus(200);
    }

    public function test_company_show_with_people(): void
    {
        $company = Company::factory()->create();
        $person = Person::factory()->create(['name' => 'John Smith']);
        $company->people()->attach($person->id, ['role' => 'CEO', 'is_current' => true]);

        $response = $this->get("/companies/{$company->slug}");

        $response->assertStatus(200);
        $response->assertSee('John Smith');
    }

    public function test_company_show_with_news_mentions(): void
    {
        $company = Company::factory()->create();
        NewsMention::factory()->create(['company_id' => $company->id]);

        $response = $this->get("/companies/{$company->slug}");

        $response->assertStatus(200);
    }

    public function test_company_show_with_headcount_snapshots(): void
    {
        $company = Company::factory()->create();
        HeadcountSnapshot::factory()->count(3)->create(['company_id' => $company->id]);

        $response = $this->get("/companies/{$company->slug}");

        $response->assertStatus(200);
    }

    public function test_company_show_returns_404_for_unknown_slug(): void
    {
        $response = $this->get('/companies/nonexistent-slug-xyz');

        $response->assertStatus(404);
    }

    public function test_company_show_no_funding_rounds(): void
    {
        $company = Company::factory()->create();

        $response = $this->get("/companies/{$company->slug}");

        $response->assertStatus(200);
    }

    public function test_company_show_tracks_view_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $this->actingAs($user)->get("/companies/{$company->slug}");

        $this->assertDatabaseHas('company_views', [
            'user_id' => $user->id,
            'company_id' => $company->id,
        ]);
    }

    public function test_company_show_does_not_track_view_for_guest(): void
    {
        $company = Company::factory()->create();

        $this->get("/companies/{$company->slug}");

        $this->assertDatabaseMissing('company_views', [
            'company_id' => $company->id,
        ]);
    }

    // --- Export routes ---

    public function test_export_csv_returns_csv_response(): void
    {
        Company::factory()->count(3)->create();

        $response = $this->get('/companies/export/csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_export_json_returns_json_response(): void
    {
        Company::factory()->count(3)->create();

        $response = $this->get('/companies/export/json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_export_csv_with_search_filter(): void
    {
        Company::factory()->create(['name' => 'FilteredCo', 'country' => 'Canada']);
        Company::factory()->create(['name' => 'OtherCo', 'country' => 'Germany']);

        $response = $this->get('/companies/export/csv?country=Canada');

        $response->assertStatus(200);
    }

    // --- Pagination ---

    public function test_companies_index_paginates_results(): void
    {
        Company::factory()->count(55)->create();

        $response = $this->get('/companies');

        $response->assertStatus(200);
        // Page 2 should also work
        $response2 = $this->get('/companies?page=2');
        $response2->assertStatus(200);
    }
}
