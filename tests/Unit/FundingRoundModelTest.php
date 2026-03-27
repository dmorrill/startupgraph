<?php

namespace Tests\Unit;

use App\Models\FundingRound;
use App\Models\Company;
use App\Models\Investor;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundingRoundModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_funding_round_belongs_to_a_company(): void
    {
        $company = Company::factory()->create();
        $round = FundingRound::factory()->create(['company_id' => $company->id]);
        $this->assertInstanceOf(Company::class, $round->company);
    }

    public function test_funding_round_has_many_investors(): void
    {
        $round = FundingRound::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $round->investors());
    }

    public function test_funding_round_has_amount(): void
    {
        $round = FundingRound::factory()->create(['amount' => 5000000]);
        $this->assertEquals(5000000, $round->amount);
    }
}
