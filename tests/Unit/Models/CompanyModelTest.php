<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\HeadcountSnapshot;
use App\Models\NewsMention;
use App\Models\Person;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_has_many_funding_rounds(): void
    {
        $company = Company::factory()->create();
        
        $this->assertInstanceOf(HasMany::class, $company->fundingRounds());
    }

    public function test_company_has_many_people(): void
    {
        $company = Company::factory()->create();
        
        $this->assertInstanceOf(BelongsToMany::class, $company->people());
    }

    public function test_company_has_many_news_mentions(): void
    {
        $company = Company::factory()->create();
        
        $this->assertInstanceOf(HasMany::class, $company->newsMentions());
    }

    public function test_company_has_many_headcount_snapshots(): void
    {
        $company = Company::factory()->create();
        
        $this->assertInstanceOf(HasMany::class, $company->headcountSnapshots());
    }

    public function test_company_has_one_latest_funding_round(): void
    {
        $company = Company::factory()->create();
        
        $this->assertInstanceOf(HasOne::class, $company->latestFundingRound());
    }

    public function test_company_uses_slug_for_route_key(): void
    {
        $company = new Company();
        
        $this->assertEquals('slug', $company->getRouteKeyName());
    }

    public function test_category_label_attribute_returns_human_readable_category(): void
    {
        $company = Company::factory()->create(['category' => 'ai_ml']);
        
        $this->assertEquals('AI/ML', $company->category_label);
    }

    public function test_category_label_attribute_returns_null_for_no_category(): void
    {
        $company = Company::factory()->create(['category' => null]);
        
        $this->assertNull($company->category_label);
    }

    public function test_funded_scope_filters_companies_with_funding_rounds(): void
    {
        $companyWithFunding = Company::factory()->create();
        $companyWithoutFunding = Company::factory()->create();
        
        $companyWithFunding->fundingRounds()->create([
            'round_type' => 'seed',
            'amount' => 1000000,
            'currency' => 'USD',
            'announced_date' => now(),
        ]);

        $fundedCompanies = Company::funded()->get();
        
        $this->assertTrue($fundedCompanies->contains($companyWithFunding));
        $this->assertFalse($fundedCompanies->contains($companyWithoutFunding));
    }

    public function test_founded_within_scope_filters_companies_by_founded_date(): void
    {
        $recentCompany = Company::factory()->create([
            'founded_date' => now()->subYear(),
        ]);
        
        $oldCompany = Company::factory()->create([
            'founded_date' => now()->subYears(5),
        ]);

        $recentCompanies = Company::foundedWithin(2)->get();
        
        $this->assertTrue($recentCompanies->contains($recentCompany));
        $this->assertFalse($recentCompanies->contains($oldCompany));
    }

    public function test_in_category_scope_filters_companies_by_category(): void
    {
        $aiCompany = Company::factory()->create(['category' => 'ai_ml']);
        $fintechCompany = Company::factory()->create(['category' => 'fintech']);

        $aiCompanies = Company::inCategory('ai_ml')->get();
        
        $this->assertTrue($aiCompanies->contains($aiCompany));
        $this->assertFalse($aiCompanies->contains($fintechCompany));
    }

    public function test_company_categories_constant_contains_expected_values(): void
    {
        $categories = Company::CATEGORIES;
        
        $this->assertIsArray($categories);
        $this->assertArrayHasKey('ai_ml', $categories);
        $this->assertArrayHasKey('fintech', $categories);
        $this->assertArrayHasKey('enterprise', $categories);
        $this->assertEquals('AI/ML', $categories['ai_ml']);
        $this->assertEquals('Fintech', $categories['fintech']);
    }
}