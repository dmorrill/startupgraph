<?php

namespace Tests\Unit;

use App\Models\Investor;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_investor_has_a_name(): void
    {
        $investor = Investor::factory()->create(['name' => 'Sequoia Capital']);
        $this->assertEquals('Sequoia Capital', $investor->name);
    }

    public function test_investor_has_many_funding_rounds(): void
    {
        $investor = Investor::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $investor->fundingRounds());
    }
}
