<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_companies_list()
    {
        Company::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get('/companies');

        $response->assertStatus(200);
        $response->assertViewIs('companies.index');
        $response->assertViewHas('companies');
    }

    public function test_search_filters_companies_by_name()
    {
        $targetCompany = Company::factory()->create(['name' => 'Awesome Startup']);
        Company::factory()->create(['name' => 'Different Company']);

        $response = $this->actingAs($this->user)
            ->get('/companies?search=Awesome');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $this->assertTrue($companies->contains('id', $targetCompany->id));
        $this->assertCount(1, $companies);
    }

    public function test_search_filters_companies_by_description()
    {
        $targetCompany = Company::factory()->create([
            'name' => 'Company A',
            'description' => 'This company builds awesome AI tools'
        ]);
        Company::factory()->create([
            'name' => 'Company B', 
            'description' => 'This is a fintech startup'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/companies?search=awesome');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $this->assertTrue($companies->contains('id', $targetCompany->id));
        $this->assertCount(1, $companies);
    }

    public function test_search_escapes_special_characters()
    {
        Company::factory()->create(['name' => 'Company with % wildcard']);
        Company::factory()->create(['name' => 'Company without wildcard']);

        $response = $this->actingAs($this->user)
            ->get('/companies?search=%');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $this->assertCount(1, $companies);
        $this->assertEquals('Company with % wildcard', $companies->first()->name);
    }

    public function test_filters_companies_by_country()
    {
        $usCompany = Company::factory()->create(['country' => 'United States']);
        Company::factory()->create(['country' => 'Canada']);

        $response = $this->actingAs($this->user)
            ->get('/companies?country=United States');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $this->assertTrue($companies->contains('id', $usCompany->id));
        $this->assertCount(1, $companies);
    }

    public function test_filters_companies_by_category()
    {
        $aiCompany = Company::factory()->create(['category' => 'AI']);
        Company::factory()->create(['category' => 'Fintech']);

        $response = $this->actingAs($this->user)
            ->get('/companies?category=AI');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $this->assertTrue($companies->contains('id', $aiCompany->id));
        $this->assertCount(1, $companies);
    }

    public function test_filters_companies_by_funding_date_range()
    {
        $recentCompany = Company::factory()->create();
        $oldCompany = Company::factory()->create();
        
        // Create funding rounds
        FundingRound::factory()->create([
            'company_id' => $recentCompany->id,
            'announced_date' => '2023-06-15'
        ]);
        
        FundingRound::factory()->create([
            'company_id' => $oldCompany->id,
            'announced_date' => '2020-01-15'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/companies?funded_after=2023-01-01');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $this->assertTrue($companies->contains('id', $recentCompany->id));
        $this->assertFalse($companies->contains('id', $oldCompany->id));
    }

    public function test_filters_companies_by_funding_amount_range()
    {
        $highFundingCompany = Company::factory()->create();
        $lowFundingCompany = Company::factory()->create();
        
        FundingRound::factory()->create([
            'company_id' => $highFundingCompany->id,
            'amount' => 5000000 // $5M
        ]);
        
        FundingRound::factory()->create([
            'company_id' => $lowFundingCompany->id,
            'amount' => 500000 // $500K
        ]);

        $response = $this->actingAs($this->user)
            ->get('/companies?min_funding=2000000'); // Min $2M

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $this->assertTrue($companies->contains('id', $highFundingCompany->id));
        $this->assertFalse($companies->contains('id', $lowFundingCompany->id));
    }

    public function test_sorting_by_latest_funding_date()
    {
        $newestCompany = Company::factory()->create(['name' => 'Newest']);
        $oldestCompany = Company::factory()->create(['name' => 'Oldest']);
        
        FundingRound::factory()->create([
            'company_id' => $newestCompany->id,
            'announced_date' => '2023-12-01'
        ]);
        
        FundingRound::factory()->create([
            'company_id' => $oldestCompany->id,
            'announced_date' => '2022-01-01'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/companies?sort=latest_funding_date&order=desc');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $this->assertEquals($newestCompany->id, $companies->first()->id);
    }

    public function test_sorting_by_total_funding()
    {
        $highFundingCompany = Company::factory()->create(['name' => 'High Funding']);
        $lowFundingCompany = Company::factory()->create(['name' => 'Low Funding']);
        
        FundingRound::factory()->create([
            'company_id' => $highFundingCompany->id,
            'amount' => 10000000
        ]);
        
        FundingRound::factory()->create([
            'company_id' => $lowFundingCompany->id,
            'amount' => 1000000
        ]);

        $response = $this->actingAs($this->user)
            ->get('/companies?sort=total_funding&order=desc');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $this->assertEquals($highFundingCompany->id, $companies->first()->id);
    }

    public function test_pagination_works_correctly()
    {
        Company::factory()->count(25)->create();

        $response = $this->actingAs($this->user)
            ->get('/companies?page=2');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        // Should have pagination links
        $this->assertNotNull($companies->links());
        $this->assertLessThanOrEqual(20, $companies->count()); // Default page size
    }

    public function test_includes_funding_aggregations()
    {
        $company = Company::factory()->create();
        
        FundingRound::factory()->count(2)->create([
            'company_id' => $company->id,
            'amount' => 1000000
        ]);

        $response = $this->actingAs($this->user)
            ->get('/companies');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $foundCompany = $companies->first();
        $this->assertEquals(2, $foundCompany->funding_rounds_count);
        $this->assertEquals(2000000, $foundCompany->funding_rounds_sum_amount);
    }

    public function test_show_displays_company_details()
    {
        $company = Company::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/companies/{$company->id}");

        $response->assertStatus(200);
        $response->assertViewIs('companies.show');
        $response->assertViewHas('company', $company);
    }

    public function test_show_returns_404_for_nonexistent_company()
    {
        $response = $this->actingAs($this->user)
            ->get('/companies/99999');

        $response->assertStatus(404);
    }

    public function test_requires_authentication()
    {
        $response = $this->get('/companies');
        
        $response->assertRedirect('/login');
    }

    public function test_combines_multiple_filters()
    {
        $targetCompany = Company::factory()->create([
            'name' => 'AI Startup',
            'country' => 'United States',
            'category' => 'AI'
        ]);
        
        Company::factory()->create([
            'name' => 'Different Company',
            'country' => 'Canada',
            'category' => 'Fintech'
        ]);

        FundingRound::factory()->create([
            'company_id' => $targetCompany->id,
            'announced_date' => '2023-06-15',
            'amount' => 5000000
        ]);

        $response = $this->actingAs($this->user)
            ->get('/companies?search=AI&country=United States&category=AI&funded_after=2023-01-01');

        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        
        $this->assertCount(1, $companies);
        $this->assertEquals($targetCompany->id, $companies->first()->id);
    }
}