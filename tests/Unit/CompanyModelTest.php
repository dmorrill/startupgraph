<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\NewsMention;
use App\Models\Person;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CompanyModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_has_many_funding_rounds(): void
    {
        $company = Company::factory()->create();
        $this->assertInstanceOf(HasMany::class, $company->fundingRounds());
    }

    public function test_company_has_people_via_belongs_to_many(): void
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

    public function test_company_has_many_scheduled_task_executions(): void
    {
        $company = Company::factory()->create();
        $this->assertInstanceOf(HasMany::class, $company->scheduledTaskExecutions());
    }

    public function test_company_has_oss_alternatives_relationship(): void
    {
        $company = Company::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $company->ossAlternatives());
    }

    public function test_company_name_is_required(): void
    {
        $this->expectException(QueryException::class);
        Company::factory()->create(['name' => null]);
    }

    public function test_company_slug_must_be_unique(): void
    {
        Company::factory()->create(['slug' => 'test-company']);
        $this->expectException(QueryException::class);
        Company::factory()->create(['slug' => 'test-company']);
    }

    public function test_company_uses_slug_for_route_key(): void
    {
        $company = new Company;
        $this->assertEquals('slug', $company->getRouteKeyName());
    }

    public function test_company_casts_founded_date_to_date(): void
    {
        $company = Company::factory()->create(['founded_date' => '2020-01-15']);
        $this->assertInstanceOf(Carbon::class, $company->founded_date);
        $this->assertEquals(2020, $company->founded_date->year);
    }

    public function test_company_casts_product_highlights_to_array(): void
    {
        $highlights = ['Fast delivery', 'AI-powered', 'Enterprise ready'];
        $company = Company::factory()->create(['product_highlights' => $highlights]);
        $company->refresh();
        $this->assertIsArray($company->product_highlights);
        $this->assertEquals($highlights, $company->product_highlights);
    }

    public function test_company_casts_boolean_fields_correctly(): void
    {
        $company = Company::factory()->create([
            'is_indie' => true,
            'is_open_source' => false,
            'solo_builder' => true,
        ]);
        $company->refresh();
        $this->assertTrue($company->is_indie);
        $this->assertFalse($company->is_open_source);
        $this->assertTrue($company->solo_builder);
    }

    public function test_company_casts_headcount_history_to_array(): void
    {
        $history = [['date' => '2024-01', 'count' => 50], ['date' => '2024-06', 'count' => 75]];
        $company = Company::factory()->create(['headcount_history' => $history]);
        $company->refresh();
        $this->assertIsArray($company->headcount_history);
        $this->assertCount(2, $company->headcount_history);
    }

    public function test_company_casts_datetime_fields_correctly(): void
    {
        $now = now();
        $company = Company::factory()->create([
            'profile_refreshed_at' => $now,
            'headcount_fetched_at' => $now,
        ]);
        $company->refresh();
        $this->assertInstanceOf(Carbon::class, $company->profile_refreshed_at);
        $this->assertInstanceOf(Carbon::class, $company->headcount_fetched_at);
    }

    public function test_category_label_returns_human_readable_name(): void
    {
        $company = Company::factory()->create(['category' => 'ai_ml']);
        $this->assertEquals('AI/ML', $company->category_label);
    }

    public function test_category_label_returns_raw_value_for_unknown_category(): void
    {
        $company = Company::factory()->create(['category' => 'unknown_cat']);
        $this->assertEquals('unknown_cat', $company->category_label);
    }

    public function test_category_label_returns_null_when_no_category(): void
    {
        $company = Company::factory()->create(['category' => null]);
        $this->assertNull($company->category_label);
    }

    public function test_categories_constant_contains_all_expected_keys(): void
    {
        $expected = ['ai_ml', 'fintech', 'enterprise', 'healthcare', 'robotics', 'space', 'climate', 'consumer', 'developer_tools', 'defense'];
        $this->assertEquals($expected, array_keys(Company::CATEGORIES));
    }

    public function test_funded_scope_returns_only_companies_with_funding_rounds(): void
    {
        $funded = Company::factory()->create();
        $unfunded = Company::factory()->create();
        FundingRound::factory()->create(['company_id' => $funded->id]);

        $results = Company::funded()->pluck('id')->toArray();
        $this->assertContains($funded->id, $results);
        $this->assertNotContains($unfunded->id, $results);
    }

    public function test_founded_within_scope_filters_by_years(): void
    {
        $recent = Company::factory()->create(['founded_date' => now()->subMonths(6)]);
        $old = Company::factory()->create(['founded_date' => now()->subYears(10)]);

        $results = Company::foundedWithin(2)->pluck('id')->toArray();
        $this->assertContains($recent->id, $results);
        $this->assertNotContains($old->id, $results);
    }

    public function test_in_category_scope_filters_by_category(): void
    {
        $ai = Company::factory()->create(['category' => 'ai_ml']);
        $fintech = Company::factory()->create(['category' => 'fintech']);

        $results = Company::inCategory('ai_ml')->pluck('id')->toArray();
        $this->assertContains($ai->id, $results);
        $this->assertNotContains($fintech->id, $results);
    }

    public function test_scopes_can_be_chained(): void
    {
        $match = Company::factory()->create([
            'category' => 'ai_ml',
            'founded_date' => now()->subMonths(6),
        ]);
        FundingRound::factory()->create(['company_id' => $match->id]);

        $noFunding = Company::factory()->create([
            'category' => 'ai_ml',
            'founded_date' => now()->subMonths(6),
        ]);

        $results = Company::funded()->inCategory('ai_ml')->foundedWithin(2)->pluck('id')->toArray();
        $this->assertContains($match->id, $results);
        $this->assertNotContains($noFunding->id, $results);
    }

    public function test_latest_funding_round_returns_most_recent_by_date(): void
    {
        $company = Company::factory()->create();

        FundingRound::factory()->create([
            'company_id' => $company->id,
            'round_type' => 'Seed',
            'announced_date' => now()->subYear(),
        ]);
        $latest = FundingRound::factory()->create([
            'company_id' => $company->id,
            'round_type' => 'Series A',
            'announced_date' => now()->subMonth(),
        ]);

        $this->assertEquals($latest->id, $company->latestFundingRound->id);
        $this->assertEquals('Series A', $company->latestFundingRound->round_type);
    }

    public function test_company_can_attach_people_with_pivot_data(): void
    {
        $company = Company::factory()->create();
        $person = Person::factory()->create();

        $company->people()->attach($person->id, [
            'role' => 'CTO',
            'is_current' => true,
        ]);

        $this->assertCount(1, $company->people);
        $this->assertEquals('CTO', $company->people->first()->pivot->role);
        $this->assertTrue((bool) $company->people->first()->pivot->is_current);
    }

    public function test_company_can_have_multiple_people(): void
    {
        $company = Company::factory()->create();
        $ceo = Person::factory()->create();
        $cto = Person::factory()->create();

        $company->people()->attach($ceo->id, ['role' => 'CEO', 'is_current' => true]);
        $company->people()->attach($cto->id, ['role' => 'CTO', 'is_current' => true]);

        $company->refresh();
        $this->assertCount(2, $company->people);
    }

    public function test_company_can_have_multiple_funding_rounds(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->count(3)->create(['company_id' => $company->id]);

        $this->assertCount(3, $company->fundingRounds);
    }

    public function test_company_can_have_multiple_news_mentions(): void
    {
        $company = Company::factory()->create();
        NewsMention::factory()->count(5)->create(['company_id' => $company->id]);

        $this->assertCount(5, $company->newsMentions);
    }
}
