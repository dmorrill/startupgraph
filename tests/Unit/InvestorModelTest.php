<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Investor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
