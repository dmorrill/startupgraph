<?php

namespace Tests\Feature;

use App\Models\Investor;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvestorTest extends TestCase
{
    use RefreshDatabase;

    public function test_investor_index_page_loads(): void
    {
        $user = User::factory()->create();
        Investor::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/investors');

        $response->assertStatus(200);
    }
}
