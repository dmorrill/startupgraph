<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Investor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FundingRoundModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_funding_round_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $round = FundingRound::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(BelongsTo::class, $round->company());
        $this->assertEquals($company->id, $round->company->id);
    }

    public function test_funding_round_has_investors_relationship(): void
    {
        $round = FundingRound::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $round->investors());
    }

    public function test_funding_round_casts_amount_to_decimal(): void
    {
        $round = FundingRound::factory()->create(['amount' => 5000000.50]);
        $round->refresh();
        $this->assertEquals('5000000.50', $round->amount);
    }

    public function test_funding_round_casts_pre_money_valuation_to_decimal(): void
    {
        $round = FundingRound::factory()->create(['pre_money_valuation' => 100000000.00]);
        $round->refresh();
        $this->assertEquals('100000000.00', $round->pre_money_valuation);
    }

    public function test_funding_round_casts_announced_date_to_date(): void
    {
        $round = FundingRound::factory()->create(['announced_date' => '2024-06-15']);
        $this->assertInstanceOf(Carbon::class, $round->announced_date);
        $this->assertEquals(2024, $round->announced_date->year);
        $this->assertEquals(6, $round->announced_date->month);
    }

    public function test_funding_round_stores_all_fillable_attributes(): void
    {
        $company = Company::factory()->create();
        $round = FundingRound::factory()->create([
            'company_id' => $company->id,
            'round_type' => 'Series B',
            'amount' => 25000000,
            'currency' => 'EUR',
            'announced_date' => '2024-03-01',
            'pre_money_valuation' => 200000000,
            'source_url' => 'https://example.com/funding-news',
        ]);

        $this->assertEquals('Series B', $round->round_type);
        $this->assertEquals('EUR', $round->currency);
        $this->assertEquals('https://example.com/funding-news', $round->source_url);
    }

    public function test_funding_round_can_attach_investors_with_is_lead_pivot(): void
    {
        $round = FundingRound::factory()->create();
        $lead = Investor::factory()->create();
        $follower = Investor::factory()->create();

        $round->investors()->attach($lead->id, ['is_lead' => true]);
        $round->investors()->attach($follower->id, ['is_lead' => false]);

        $round->refresh();
        $this->assertCount(2, $round->investors);

        $leadInvestor = $round->investors->firstWhere('id', $lead->id);
        $followerInvestor = $round->investors->firstWhere('id', $follower->id);
        $this->assertTrue((bool) $leadInvestor->pivot->is_lead);
        $this->assertFalse((bool) $followerInvestor->pivot->is_lead);
    }

    public function test_funding_round_amount_can_be_null(): void
    {
        $round = FundingRound::factory()->create(['amount' => null]);
        $this->assertNull($round->amount);
    }

    public function test_funding_round_pre_money_valuation_can_be_null(): void
    {
        $round = FundingRound::factory()->create(['pre_money_valuation' => null]);
        $this->assertNull($round->pre_money_valuation);
    }

    public function test_funding_round_source_url_can_be_null(): void
    {
        $round = FundingRound::factory()->create(['source_url' => null]);
        $this->assertNull($round->source_url);
    }

    public function test_deleting_company_cascades_to_funding_rounds(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->count(3)->create(['company_id' => $company->id]);

        $this->assertEquals(3, FundingRound::where('company_id', $company->id)->count());

        $company->delete();

        $this->assertEquals(0, FundingRound::where('company_id', $company->id)->count());
    }

    public function test_multiple_funding_rounds_belong_to_same_company(): void
    {
        $company = Company::factory()->create();
        $seed = FundingRound::factory()->create([
            'company_id' => $company->id,
            'round_type' => 'Seed',
            'announced_date' => now()->subYears(2),
        ]);
        $seriesA = FundingRound::factory()->create([
            'company_id' => $company->id,
            'round_type' => 'Series A',
            'announced_date' => now()->subYear(),
        ]);

        $this->assertEquals($company->id, $seed->company->id);
        $this->assertEquals($company->id, $seriesA->company->id);
        $this->assertCount(2, $company->fundingRounds);
    }
}
