<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_index_page_loads(): void
    {
        $user = User::factory()->create();
        Company::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/companies');

        $response->assertStatus(200);
    }

    public function test_company_search_returns_results(): void
    {
        $user = User::factory()->create();
        Company::factory()->create(['name' => 'TestCorp']);

        $response = $this->actingAs($user)->get('/companies?search=TestCorp');

        $response->assertStatus(200);
    }
}
