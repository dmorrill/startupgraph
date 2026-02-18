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

    public function test_funding_round_can_be_created_with_factory(): void
    {
        $round = FundingRound::factory()->create();
        $this->assertDatabaseHas('funding_rounds', ['id' => $round->id]);
    }

    public function test_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $round = FundingRound::factory()->create(['company_id' => $company->id]);

        $this->assertEquals($company->id, $round->company->id);
    }

    public function test_amount_cast_to_decimal(): void
    {
        $round = FundingRound::factory()->create(['amount' => 5000000.50]);
        $round->refresh();
        $this->assertEquals('5000000.50', $round->amount);
    }

    public function test_announced_date_cast_to_date(): void
    {
        $round = FundingRound::factory()->create(['announced_date' => '2024-03-15']);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $round->announced_date);
    }

    public function test_fillable_attributes(): void
    {
        $company = Company::factory()->create();
        $round = FundingRound::factory()->create([
            'company_id' => $company->id,
            'round_type' => 'Series A',
            'amount' => 10000000,
            'currency' => 'USD',
            'source_url' => 'https://techcrunch.com/article',
        ]);

        $this->assertEquals('Series A', $round->round_type);
        $this->assertEquals('USD', $round->currency);
        $this->assertEquals('https://techcrunch.com/article', $round->source_url);
    }

    public function test_belongs_to_many_investors(): void
    {
        $round = FundingRound::factory()->create();
        $investor = Investor::create([
            'name' => 'Sequoia Capital',
            'slug' => 'sequoia-capital',
            'type' => 'vc',
        ]);

        $round->investors()->attach($investor, ['is_lead' => true]);

        $this->assertCount(1, $round->investors);
        $this->assertTrue((bool) $round->investors->first()->pivot->is_lead);
    }
}
