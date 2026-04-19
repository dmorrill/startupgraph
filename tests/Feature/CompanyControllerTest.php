<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_index_page_loads_successfully(): void
    {
        $response = $this->get('/companies');

        $response->assertStatus(200);
        $response->assertViewIs('companies.index');
    }

    public function test_company_index_displays_companies(): void
    {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'description' => 'A test company description',
        ]);

        $response = $this->get('/companies');

        $response->assertSee('Test Company');
        $response->assertSee('A test company description');
    }

    public function test_company_index_filters_by_search(): void
    {
        Company::factory()->create(['name' => 'Acme Corp']);
        Company::factory()->create(['name' => 'Beta Inc']);

        $response = $this->get('/companies?search=Acme');

        $response->assertSee('Acme Corp');
        $response->assertDontSee('Beta Inc');
    }

    public function test_company_index_filters_by_category(): void
    {
        Company::factory()->create(['name' => 'AI Company', 'category' => 'ai_ml']);
        Company::factory()->create(['name' => 'Fintech Company', 'category' => 'fintech']);

        $response = $this->get('/companies?category=ai_ml');

        $response->assertSee('AI Company');
        $response->assertDontSee('Fintech Company');
    }

    public function test_company_show_page_loads_successfully(): void
    {
        $company = Company::factory()->create();

        $response = $this->get("/companies/{$company->slug}");

        $response->assertStatus(200);
        $response->assertViewIs('companies.show');
        $response->assertSee($company->name);
    }

    public function test_export_csv_endpoint_is_rate_limited(): void
    {
        // Make 6 requests (exceeding the 5 per minute limit)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->get('/companies/export/csv');

            if ($i < 5) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    public function test_export_json_returns_json_response(): void
    {
        Company::factory()->create(['name' => 'Test Company']);

        $response = $this->get('/companies/export/json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'companies' => [
                '*' => [
                    'name',
                    'slug',
                    'website',
                    'description',
                    'category',
                ],
            ],
        ]);
    }

    public function test_export_csv_returns_csv_response(): void
    {
        Company::factory()->create(['name' => 'Test Company']);

        $response = $this->get('/companies/export/csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Test Company', $response->getContent());
    }
}
