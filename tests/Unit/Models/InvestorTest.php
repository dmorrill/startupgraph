<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Investor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_investor(): void
    {
        $investor = Investor::factory()->create();

        $this->assertDatabaseHas('investors', ['id' => $investor->id]);
    }

    public function test_fillable_attributes(): void
    {
        $investor = Investor::factory()->create([
            'name' => 'Acme Ventures',
            'type' => 'vc',
        ]);

        $this->assertEquals('Acme Ventures', $investor->name);
        $this->assertEquals('vc', $investor->type);
    }

    public function test_type_label_attribute(): void
    {
        $mappings = [
            'vc' => 'Venture Capital',
            'angel' => 'Angel Investor',
            'corporate' => 'Corporate VC',
            'accelerator' => 'Accelerator',
            'pe' => 'Private Equity',
            'other' => 'other',
        ];

        foreach ($mappings as $type => $label) {
            $investor = Investor::factory()->create(['type' => $type]);
            $this->assertEquals($label, $investor->type_label);
        }
    }

    public function test_funding_rounds_relationship(): void
    {
        $investor = Investor::factory()->create();
        $fundingRound = FundingRound::factory()->create();

        $investor->fundingRounds()->attach($fundingRound->id, ['is_lead' => true]);

        $this->assertCount(1, $investor->fundingRounds);
        $this->assertEquals(1, $investor->fundingRounds->first()->pivot->is_lead);
    }

    public function test_companies_method(): void
    {
        $investor = Investor::factory()->create();
        $company = Company::factory()->create();
        $fundingRound = FundingRound::factory()->create(['company_id' => $company->id]);

        $investor->fundingRounds()->attach($fundingRound->id);

        $companies = $investor->companies();
        $this->assertCount(1, $companies);
        $this->assertEquals($company->id, $companies->first()->id);
    }

    public function test_route_key_name_is_slug(): void
    {
        $investor = new Investor;
        $this->assertEquals('slug', $investor->getRouteKeyName());
    }
}
