<?php

namespace Tests\Unit\Models;

<<<<<<< HEAD
=======
use App\Models\Company;
>>>>>>> origin/main
use App\Models\FundingRound;
use App\Models\Investor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_can_be_created(): void
    {
        $investor = Investor::create([
            'name' => 'Andreessen Horowitz',
            'slug' => 'a16z',
            'type' => 'vc',
            'website' => 'https://a16z.com',
        ]);

        $this->assertDatabaseHas('investors', ['slug' => 'a16z']);
    }

    public function test_route_key_name_is_slug(): void
    {
        $this->assertEquals('slug', (new Investor())->getRouteKeyName());
=======
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
>>>>>>> origin/main
    }

    public function test_type_label_attribute(): void
    {
<<<<<<< HEAD
        $investor = new Investor(['type' => 'vc']);
        $this->assertEquals('Venture Capital', $investor->type_label);

        $investor->type = 'angel';
        $this->assertEquals('Angel Investor', $investor->type_label);

        $investor->type = 'corporate';
        $this->assertEquals('Corporate VC', $investor->type_label);

        $investor->type = null;
        $this->assertEquals('Unknown', $investor->type_label);
=======
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
>>>>>>> origin/main
    }

    public function test_funding_rounds_relationship(): void
    {
<<<<<<< HEAD
        $investor = Investor::create(['name' => 'Test VC', 'slug' => 'test-vc', 'type' => 'vc']);
        $round = FundingRound::factory()->create();
        $investor->fundingRounds()->attach($round, ['is_lead' => false]);

        $this->assertCount(1, $investor->fundingRounds);
=======
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
        $investor = new Investor();
        $this->assertEquals('slug', $investor->getRouteKeyName());
>>>>>>> origin/main
    }
}
