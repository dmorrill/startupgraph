<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompareTest extends TestCase
{
    use RefreshDatabase;

    public function test_compare_page_loads_without_companies(): void
    {
        $response = $this->get('/compare');
        $response->assertStatus(200);
        $response->assertSee('Compare Companies');
        $response->assertSee('Select at least 2 companies');
    }

    public function test_compare_page_shows_single_company_prompt(): void
    {
        Company::factory()->create(['name' => 'TestCo', 'slug' => 'testco']);

        $response = $this->get('/compare?companies=testco');
        $response->assertStatus(200);
        $response->assertSee('Select at least 2 companies');
    }

    public function test_compare_page_shows_two_companies(): void
    {
        Company::factory()->create(['name' => 'Alpha Inc', 'slug' => 'alpha-inc']);
        Company::factory()->create(['name' => 'Beta Corp', 'slug' => 'beta-corp']);

        $response = $this->get('/compare?companies=alpha-inc,beta-corp');
        $response->assertStatus(200);
        $response->assertSee('Alpha Inc');
        $response->assertSee('Beta Corp');
    }

    public function test_compare_handles_invalid_slugs(): void
    {
        $response = $this->get('/compare?companies=nonexistent,also-fake');
        $response->assertStatus(200);
        $response->assertSee('Select at least 2 companies');
    }

    public function test_compare_link_in_navigation(): void
    {
        $response = $this->get('/');
        $response->assertSee('Compare');
    }
}
