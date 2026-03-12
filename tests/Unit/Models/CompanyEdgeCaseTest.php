<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\FundingRound;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_founded_within_returns_recent_companies(): void
    {
        $recent = Company::factory()->create(['founded_date' => now()->subYear()]);
        $old = Company::factory()->create(['founded_date' => now()->subYears(10)]);

        $results = Company::foundedWithin(2)->get();

        $this->assertTrue($results->contains($recent));
        $this->assertFalse($results->contains($old));
    }

    public function test_company_with_no_funding_rounds_has_null_latest_funding_round(): void
    {
        $company = Company::factory()->create();

        $this->assertNull($company->latestFundingRound);
    }

    public function test_company_with_no_headcount_snapshots_returns_empty_collection(): void
    {
        $company = Company::factory()->create();

        $this->assertCount(0, $company->headcountSnapshots);
    }

    public function test_company_with_no_news_mentions_returns_empty_collection(): void
    {
        $company = Company::factory()->create();

        $this->assertCount(0, $company->newsMentions);
    }

    public function test_company_with_no_people_returns_empty_collection(): void
    {
        $company = Company::factory()->create();

        $this->assertCount(0, $company->people);
    }

    public function test_category_label_returns_category_key_for_unknown_category(): void
    {
        $company = Company::factory()->create(['category' => 'unknown_custom_category']);

        $this->assertEquals('unknown_custom_category', $company->category_label);
    }

    public function test_founded_date_cast_to_date(): void
    {
        $company = Company::factory()->create(['founded_date' => '2020-06-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $company->founded_date);
        $this->assertEquals('2020-06-15', $company->founded_date->format('Y-m-d'));
    }

    public function test_product_highlights_defaults_to_null_when_not_set(): void
    {
        $company = Company::factory()->create(['product_highlights' => null]);

        $this->assertNull($company->product_highlights);
    }

    public function test_product_highlights_stored_as_array(): void
    {
        $highlights = ['Fast', 'Reliable', 'Scalable'];
        $company = Company::factory()->create(['product_highlights' => $highlights]);

        $this->assertIsArray($company->product_highlights);
        $this->assertCount(3, $company->product_highlights);
        $this->assertContains('Fast', $company->product_highlights);
    }

    public function test_scope_funded_excludes_company_with_no_rounds(): void
    {
        $company = Company::factory()->create();

        $results = Company::funded()->get();

        $this->assertFalse($results->contains($company));
    }

    public function test_multiple_funding_rounds_latest_is_correct(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->create(['company_id' => $company->id, 'announced_date' => '2021-01-01']);
        FundingRound::factory()->create(['company_id' => $company->id, 'announced_date' => '2023-06-01']);
        $latest = FundingRound::factory()->create(['company_id' => $company->id, 'announced_date' => '2024-12-01']);

        $company->refresh();
        $this->assertEquals($latest->id, $company->latestFundingRound->id);
    }

    public function test_is_indie_and_is_open_source_defaults(): void
    {
        $company = Company::factory()->create(['is_indie' => false, 'is_open_source' => false]);

        $this->assertFalse($company->is_indie);
        $this->assertFalse($company->is_open_source);
    }

    public function test_categories_constant_contains_expected_keys(): void
    {
        $this->assertArrayHasKey('ai_ml', Company::CATEGORIES);
        $this->assertArrayHasKey('fintech', Company::CATEGORIES);
        $this->assertArrayHasKey('healthcare', Company::CATEGORIES);
        $this->assertArrayHasKey('defense', Company::CATEGORIES);
    }
}
