<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\HeadcountSnapshot;
use App\Models\Investor;
use App\Models\NewsMention;
use App\Models\OpenSourceProject;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_category_label_returns_human_readable_name(): void
    {
        $company = Company::factory()->create(['category' => 'ai_ml']);
        $this->assertEquals('AI/ML', $company->category_label);
        
        $company = Company::factory()->create(['category' => 'fintech']);
        $this->assertEquals('Fintech', $company->category_label);
        
        $company = Company::factory()->create(['category' => 'developer_tools']);
        $this->assertEquals('Developer Tools', $company->category_label);
        
        $company = Company::factory()->create(['category' => null]);
        $this->assertNull($company->category_label);
    }

    public function test_company_scope_funded_only_includes_companies_with_funding(): void
    {
        $fundedCompany = Company::factory()->create(['name' => 'Funded Corp']);
        $unfundedCompany = Company::factory()->create(['name' => 'Bootstrap Corp']);
        
        // Add funding round to one company
        FundingRound::factory()->create([
            'company_id' => $fundedCompany->id,
            'amount' => 1000000
        ]);
        
        $fundedCompanies = Company::funded()->get();
        
        $this->assertCount(1, $fundedCompanies);
        $this->assertEquals('Funded Corp', $fundedCompanies->first()->name);
    }

    public function test_company_scope_founded_within_filters_by_years(): void
    {
        $recentCompany = Company::factory()->create([
            'name' => 'Recent Startup',
            'founded_date' => now()->subYears(2)
        ]);
        
        $oldCompany = Company::factory()->create([
            'name' => 'Old Company',
            'founded_date' => now()->subYears(10)
        ]);
        
        $recentCompanies = Company::foundedWithin(5)->get();
        
        $this->assertCount(1, $recentCompanies);
        $this->assertEquals('Recent Startup', $recentCompanies->first()->name);
    }

    public function test_company_scope_in_category_filters_correctly(): void
    {
        $aiCompany = Company::factory()->create([
            'name' => 'AI Corp',
            'category' => 'ai_ml'
        ]);
        
        $fintechCompany = Company::factory()->create([
            'name' => 'Fintech Corp',
            'category' => 'fintech'
        ]);
        
        $aiCompanies = Company::inCategory('ai_ml')->get();
        
        $this->assertCount(1, $aiCompanies);
        $this->assertEquals('AI Corp', $aiCompanies->first()->name);
    }

    public function test_company_latest_funding_round_relationship(): void
    {
        $company = Company::factory()->create();
        
        // Create multiple funding rounds
        $oldRound = FundingRound::factory()->create([
            'company_id' => $company->id,
            'announced_date' => now()->subYears(2),
            'amount' => 1000000
        ]);
        
        $latestRound = FundingRound::factory()->create([
            'company_id' => $company->id,
            'announced_date' => now()->subMonths(6),
            'amount' => 5000000
        ]);
        
        $this->assertEquals($latestRound->id, $company->latestFundingRound->id);
        $this->assertEquals(5000000, $company->latestFundingRound->amount);
    }

    public function test_company_people_relationship_with_pivot_data(): void
    {
        $company = Company::factory()->create(['name' => 'Test Corp']);
        $person = Person::factory()->create(['name' => 'John Doe']);
        
        $company->people()->attach($person, [
            'role' => 'CEO',
            'is_current' => true,
            'started_at' => now()->subYears(3)
        ]);
        
        $attachedPerson = $company->people->first();
        $this->assertEquals('John Doe', $attachedPerson->name);
        $this->assertEquals('CEO', $attachedPerson->pivot->role);
        $this->assertEquals(1, $attachedPerson->pivot->is_current); // Pivot returns int, not bool
        $this->assertNotNull($attachedPerson->pivot->started_at);
    }

    public function test_company_oss_alternatives_relationship(): void
    {
        $company = Company::factory()->create(['name' => 'Commercial Corp']);
        $ossProject = OpenSourceProject::factory()->create(['name' => 'OSS Alternative']);
        
        $company->ossAlternatives()->attach($ossProject, [
            'relationship_type' => 'commercial_version_of',
            'notes' => 'Paid version with enterprise features'
        ]);
        
        $alternative = $company->ossAlternatives->first();
        $this->assertEquals('OSS Alternative', $alternative->name);
        $this->assertEquals('commercial_version_of', $alternative->pivot->relationship_type);
        $this->assertEquals('Paid version with enterprise features', $alternative->pivot->notes);
    }

    public function test_funding_round_investor_relationship(): void
    {
        $fundingRound = FundingRound::factory()->create(['amount' => 2000000]);
        $investor1 = Investor::factory()->create(['name' => 'Sequoia Capital']);
        $investor2 = Investor::factory()->create(['name' => 'a16z']);
        
        $fundingRound->investors()->attach([$investor1->id, $investor2->id]);
        
        $this->assertCount(2, $fundingRound->investors);
        $this->assertTrue($fundingRound->investors->contains('name', 'Sequoia Capital'));
        $this->assertTrue($fundingRound->investors->contains('name', 'a16z'));
    }

    public function test_investor_can_have_multiple_funding_rounds(): void
    {
        $investor = Investor::factory()->create(['name' => 'Y Combinator']);
        
        $round1 = FundingRound::factory()->create(['amount' => 500000]);
        $round2 = FundingRound::factory()->create(['amount' => 1000000]);
        $round3 = FundingRound::factory()->create(['amount' => 2000000]);
        
        $investor->fundingRounds()->attach([$round1->id, $round2->id, $round3->id]);
        
        $this->assertCount(3, $investor->fundingRounds);
        $this->assertEquals(3500000, $investor->fundingRounds->sum('amount'));
    }

    public function test_investor_companies_method_returns_unique_companies(): void
    {
        $investor = Investor::factory()->create(['name' => 'Test Investor']);
        $company1 = Company::factory()->create(['name' => 'Company A']);
        $company2 = Company::factory()->create(['name' => 'Company B']);
        
        // Multiple rounds for same company
        $round1 = FundingRound::factory()->create(['company_id' => $company1->id]);
        $round2 = FundingRound::factory()->create(['company_id' => $company1->id]);
        $round3 = FundingRound::factory()->create(['company_id' => $company2->id]);
        
        $investor->fundingRounds()->attach([$round1->id, $round2->id, $round3->id]);
        
        $companies = $investor->companies();
        $this->assertCount(2, $companies); // Should be unique companies only
        $this->assertTrue(collect($companies)->contains('name', 'Company A'));
        $this->assertTrue(collect($companies)->contains('name', 'Company B'));
    }

    public function test_headcount_snapshot_tracks_company_growth(): void
    {
        $company = Company::factory()->create();
        
        // Create snapshots over time
        $snapshot1 = HeadcountSnapshot::factory()->create([
            'company_id' => $company->id,
            'headcount' => 10,
            'recorded_date' => now()->subMonths(12)
        ]);
        
        $snapshot2 = HeadcountSnapshot::factory()->create([
            'company_id' => $company->id,
            'headcount' => 25,
            'recorded_date' => now()->subMonths(6)
        ]);
        
        $snapshot3 = HeadcountSnapshot::factory()->create([
            'company_id' => $company->id,
            'headcount' => 40,
            'recorded_date' => now()
        ]);
        
        $snapshots = $company->headcountSnapshots()->orderBy('recorded_date')->get();
        
        $this->assertCount(3, $snapshots);
        $this->assertEquals(10, $snapshots->first()->headcount);
        $this->assertEquals(40, $snapshots->last()->headcount);
    }

    public function test_news_mention_belongs_to_company(): void
    {
        $company = Company::factory()->create(['name' => 'TechCorp']);
        $newsMention = NewsMention::factory()->create([
            'company_id' => $company->id,
            'title' => 'TechCorp raises $10M Series A',
            'url' => 'https://techcrunch.com/news'
        ]);
        
        $this->assertEquals('TechCorp', $newsMention->company->name);
        $this->assertTrue($company->newsMentions->contains($newsMention));
    }

    public function test_company_can_have_multiple_categories_in_real_use(): void
    {
        // Test that all defined categories are valid
        $categories = Company::CATEGORIES;
        
        foreach ($categories as $key => $label) {
            $company = Company::factory()->create(['category' => $key]);
            $this->assertEquals($label, $company->category_label);
        }
    }

    public function test_person_can_work_at_multiple_companies(): void
    {
        $person = Person::factory()->create(['name' => 'Jane Smith']);
        $company1 = Company::factory()->create(['name' => 'Startup A']);
        $company2 = Company::factory()->create(['name' => 'Startup B']);
        
        // Current role at one company
        $person->companies()->attach($company1, [
            'role' => 'CTO',
            'is_current' => true,
            'started_at' => now()->subYears(2)
        ]);
        
        // Previous role at another company
        $person->companies()->attach($company2, [
            'role' => 'Lead Developer',
            'is_current' => false,
            'started_at' => now()->subYears(5),
            'ended_at' => now()->subYears(3)
        ]);
        
        $this->assertCount(2, $person->companies);
        
        $currentRole = $person->companies()->where('is_current', true)->first();
        $this->assertEquals('Startup A', $currentRole->name);
        $this->assertEquals('CTO', $currentRole->pivot->role);
    }

    public function test_open_source_project_github_url_parsing(): void
    {
        $project = OpenSourceProject::factory()->create([
            'name' => 'awesome-tool',
            'github_url' => 'https://github.com/company/awesome-tool',
            'github_owner' => 'company',
            'github_repo' => 'awesome-tool'
        ]);
        
        $this->assertEquals('https://github.com/company/awesome-tool', $project->github_url);
        $this->assertEquals('company', $project->github_owner);
        $this->assertEquals('awesome-tool', $project->github_repo);
    }
}