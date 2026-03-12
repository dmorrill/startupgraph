<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\HeadcountSnapshot;
use App\Models\NewsMention;
use App\Models\OpenSourceProject;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_company(): void
    {
        $company = Company::factory()->create();

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function test_category_label_attribute(): void
    {
        $company = Company::factory()->create(['category' => 'ai_ml']);
        $this->assertEquals('AI/ML', $company->category_label);

        $company2 = Company::factory()->create(['category' => null]);
        $this->assertNull($company2->category_label);
    }

    public function test_funding_rounds_relationship(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->count(3)->create(['company_id' => $company->id]);

        $this->assertCount(3, $company->fundingRounds);
    }

    public function test_latest_funding_round_relationship(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->create(['company_id' => $company->id, 'announced_date' => '2023-01-01']);
        $latest = FundingRound::factory()->create(['company_id' => $company->id, 'announced_date' => '2024-06-01']);

        $this->assertEquals($latest->id, $company->latestFundingRound->id);
    }

    public function test_headcount_snapshots_relationship(): void
    {
        $company = Company::factory()->create();
        HeadcountSnapshot::factory()->count(2)->create(['company_id' => $company->id]);

        $this->assertCount(2, $company->headcountSnapshots);
    }

    public function test_news_mentions_relationship(): void
    {
        $company = Company::factory()->create();
        NewsMention::factory()->count(2)->create(['company_id' => $company->id]);

        $this->assertCount(2, $company->newsMentions);
    }

    public function test_people_relationship(): void
    {
        $company = Company::factory()->create();
        $person = Person::factory()->create();

        $company->people()->attach($person->id, ['role' => 'CTO', 'is_current' => true]);

        $this->assertCount(1, $company->people);
    }

    public function test_oss_alternatives_relationship(): void
    {
        $company = Company::factory()->create();
        $project = OpenSourceProject::factory()->create();

        $company->ossAlternatives()->attach($project->id, ['relationship_type' => 'alternative_to']);

        $this->assertCount(1, $company->ossAlternatives);
    }

    public function test_scope_funded(): void
    {
        $funded = Company::factory()->create();
        FundingRound::factory()->create(['company_id' => $funded->id]);
        $unfunded = Company::factory()->create();

        $results = Company::funded()->get();

        $this->assertTrue($results->contains($funded));
        $this->assertFalse($results->contains($unfunded));
    }

    public function test_scope_in_category(): void
    {
        $ai = Company::factory()->create(['category' => 'ai_ml']);
        $fin = Company::factory()->create(['category' => 'fintech']);

        $results = Company::inCategory('ai_ml')->get();

        $this->assertTrue($results->contains($ai));
        $this->assertFalse($results->contains($fin));
    }

    public function test_route_key_name_is_slug(): void
    {
        $company = new Company;
        $this->assertEquals('slug', $company->getRouteKeyName());
    }

    public function test_casts(): void
    {
        $company = Company::factory()->create([
            'is_indie' => true,
            'is_open_source' => false,
            'product_highlights' => ['Fast', 'Reliable'],
        ]);

        $this->assertIsBool($company->is_indie);
        $this->assertIsBool($company->is_open_source);
        $this->assertIsArray($company->product_highlights);
    }
}
