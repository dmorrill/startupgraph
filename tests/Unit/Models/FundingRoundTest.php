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
        $company = Company::factory()->create();
        $fundingRound = FundingRound::create([
            'company_id' => $company->id,
            'round_type' => 'Series A',
            'amount' => 5000000,
            'announced_date' => '2024-01-15',
        ]);

        $this->assertInstanceOf(FundingRound::class, $fundingRound);
        $this->assertEquals('Series A', $fundingRound->round_type);
        $this->assertEquals(5000000, $fundingRound->amount);
        $this->assertEquals($company->id, $fundingRound->company_id);
        $this->assertDatabaseHas('funding_rounds', [
            'round_type' => 'Series A',
            'amount' => 5000000,
            'company_id' => $company->id,
        ]);
    }

    public function test_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $fundingRound = FundingRound::create([
            'company_id' => $company->id,
            'round_type' => 'Seed',
            'amount' => 1000000,
        ]);

        $this->assertInstanceOf(Company::class, $fundingRound->company);
        $this->assertEquals($company->id, $fundingRound->company->id);
    }

    public function test_belongs_to_many_investors(): void
    {
        $company = Company::factory()->create();
        $fundingRound = FundingRound::create([
            'company_id' => $company->id,
            'round_type' => 'Series B',
            'amount' => 10000000,
        ]);

        $investor1 = Investor::factory()->create();
        $investor2 = Investor::factory()->create();

        $fundingRound->investors()->attach([$investor1->id, $investor2->id]);

        $this->assertCount(2, $fundingRound->investors);
        $this->assertTrue($fundingRound->investors->contains($investor1));
        $this->assertTrue($fundingRound->investors->contains($investor2));
    }

    public function test_fillable_attributes(): void
    {
        $expectedFillable = [
            'company_id',
            'round_type',
            'amount',
            'currency',
            'announced_date',
            'pre_money_valuation',
            'source_url',
        ];

        $fundingRound = new FundingRound();
        $this->assertEquals($expectedFillable, $fundingRound->getFillable());
    }

    public function test_announced_date_is_cast_to_date(): void
    {
        $company = Company::factory()->create();
        $fundingRound = FundingRound::create([
            'company_id' => $company->id,
            'round_type' => 'Seed',
            'announced_date' => '2024-03-15',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $fundingRound->announced_date);
        $this->assertEquals('2024-03-15', $fundingRound->announced_date->format('Y-m-d'));
    }

    public function test_can_create_without_amount(): void
    {
        $company = Company::factory()->create();
        $fundingRound = FundingRound::create([
            'company_id' => $company->id,
            'round_type' => 'Pre-Seed',
            'amount' => null,
            'announced_date' => '2024-01-01',
        ]);

        $this->assertInstanceOf(FundingRound::class, $fundingRound);
        $this->assertNull($fundingRound->amount);
        $this->assertEquals('Pre-Seed', $fundingRound->round_type);
    }

    public function test_can_create_without_announced_date(): void
    {
        $company = Company::factory()->create();
        $fundingRound = FundingRound::create([
            'company_id' => $company->id,
            'round_type' => 'Angel',
            'amount' => 500000,
        ]);

        $this->assertInstanceOf(FundingRound::class, $fundingRound);
        $this->assertNull($fundingRound->announced_date);
        $this->assertEquals(500000, $fundingRound->amount);
    }
}