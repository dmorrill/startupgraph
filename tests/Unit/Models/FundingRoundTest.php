<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Investor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundingRoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_funding_round(): void
    {
        $round = FundingRound::factory()->create();

        $this->assertDatabaseHas('funding_rounds', ['id' => $round->id]);
    }

    public function test_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $round = FundingRound::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(Company::class, $round->company);
        $this->assertEquals($company->id, $round->company->id);
    }

    public function test_investors_relationship_is_belongs_to_many(): void
    {
        $round = FundingRound::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $round->investors()
        );
    }

    public function test_amount_is_cast_to_decimal(): void
    {
        $round = FundingRound::factory()->create(['amount' => 5000000]);

        $this->assertEquals(5000000.0, (float) $round->amount);
    }

    public function test_announced_date_is_cast_to_date(): void
    {
        $round = FundingRound::factory()->create(['announced_date' => '2024-03-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $round->announced_date);
        $this->assertEquals('2024-03-15', $round->announced_date->format('Y-m-d'));
    }

    public function test_source_url_can_be_stored(): void
    {
        $url = 'https://techcrunch.com/funding-article';
        $round = FundingRound::factory()->create(['source_url' => $url]);

        $this->assertEquals($url, $round->source_url);
    }

    public function test_source_url_can_be_null(): void
    {
        $round = FundingRound::factory()->create(['source_url' => null]);

        $this->assertNull($round->source_url);
    }

    public function test_round_type_can_be_set(): void
    {
        $round = FundingRound::factory()->create(['round_type' => 'Series A']);

        $this->assertEquals('Series A', $round->round_type);
    }

    public function test_pre_money_valuation_is_cast_to_decimal(): void
    {
        $round = FundingRound::factory()->create(['pre_money_valuation' => 50000000]);

        $this->assertEquals(50000000.0, (float) $round->pre_money_valuation);
    }

    public function test_investors_can_be_attached_with_is_lead_pivot(): void
    {
        $round = FundingRound::factory()->create();
        $investor = Investor::factory()->create();

        $round->investors()->attach($investor->id, ['is_lead' => true]);

        $this->assertCount(1, $round->investors);
        $this->assertTrue((bool) $round->investors->first()->pivot->is_lead);
    }
}
