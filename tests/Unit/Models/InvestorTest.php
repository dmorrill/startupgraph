<?php

namespace Tests\Unit\Models;

use App\Models\FundingRound;
use App\Models\Investor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorTest extends TestCase
{
    use RefreshDatabase;

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
    }

    public function test_type_label_attribute(): void
    {
        $investor = new Investor(['type' => 'vc']);
        $this->assertEquals('Venture Capital', $investor->type_label);

        $investor->type = 'angel';
        $this->assertEquals('Angel Investor', $investor->type_label);

        $investor->type = 'corporate';
        $this->assertEquals('Corporate VC', $investor->type_label);

        $investor->type = null;
        $this->assertEquals('Unknown', $investor->type_label);
    }

    public function test_funding_rounds_relationship(): void
    {
        $investor = Investor::create(['name' => 'Test VC', 'slug' => 'test-vc', 'type' => 'vc']);
        $round = FundingRound::factory()->create();
        $investor->fundingRounds()->attach($round, ['is_lead' => false]);

        $this->assertCount(1, $investor->fundingRounds);
    }
}
