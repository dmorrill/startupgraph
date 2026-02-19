<?php

namespace Tests\Unit\Services\BulkImport;

use App\Models\Company;
use App\Services\BulkImport\GitHubOrgImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GitHubOrgImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_name(): void
    {
        $importer = new GitHubOrgImporter();
        $this->assertEquals('github-orgs', $importer->source());
    }

    public function test_imports_orgs_from_github(): void
    {
        Http::fake([
            'api.github.com/search/users*' => Http::response([
                'total_count' => 1,
                'items' => [
                    ['login' => 'vercel', 'type' => 'Organization'],
                ],
            ]),
            'api.github.com/orgs/vercel' => Http::response([
                'login' => 'vercel',
                'name' => 'Vercel',
                'blog' => 'https://vercel.com',
                'description' => 'Develop. Preview. Ship.',
                'location' => 'San Francisco, CA',
                'created_at' => '2015-06-01T00:00:00Z',
            ]),
        ]);

        $importer = new GitHubOrgImporter();
        $result = $importer->start(['max_pages' => 1]);

        $this->assertEquals('completed', $result->status);

        $vercel = Company::where('name', 'Vercel')->first();
        $this->assertNotNull($vercel);
        $this->assertEquals('operating', $vercel->status);
        $this->assertEquals('https://vercel.com', $vercel->website);
        $this->assertEquals('San Francisco', $vercel->city);
        $this->assertEquals('US', $vercel->country);
    }

    public function test_skips_orgs_without_name(): void
    {
        Http::fake([
            'api.github.com/search/users*' => Http::response([
                'total_count' => 1,
                'items' => [
                    ['login' => 'x', 'type' => 'Organization'],
                ],
            ]),
            'api.github.com/orgs/x' => Http::response([
                'login' => 'x',
                'name' => '',
                'blog' => '',
                'description' => '',
            ]),
        ]);

        $importer = new GitHubOrgImporter();
        $result = $importer->start(['max_pages' => 1]);

        $this->assertEquals(0, $result->companies_created);
    }

    public function test_handles_api_failure(): void
    {
        Http::fake([
            'api.github.com/*' => Http::response(null, 500),
        ]);

        $importer = new GitHubOrgImporter();
        $result = $importer->start(['max_pages' => 1]);

        $this->assertEquals('completed', $result->status);
        $this->assertEquals(0, $result->companies_created);
    }

    public function test_parses_location_to_city_country(): void
    {
        Http::fake([
            'api.github.com/search/users*' => Http::response([
                'total_count' => 1,
                'items' => [
                    ['login' => 'londoncorp', 'type' => 'Organization'],
                ],
            ]),
            'api.github.com/orgs/londoncorp' => Http::response([
                'login' => 'londoncorp',
                'name' => 'London Corp',
                'blog' => '',
                'description' => 'A London company',
                'location' => 'London, UK',
            ]),
        ]);

        $importer = new GitHubOrgImporter();
        $importer->start(['max_pages' => 1]);

        $company = Company::where('name', 'London Corp')->first();
        $this->assertNotNull($company);
        $this->assertEquals('London', $company->city);
        $this->assertEquals('GB', $company->country);
    }

    public function test_prepends_https_to_blog_url(): void
    {
        Http::fake([
            'api.github.com/search/users*' => Http::response([
                'total_count' => 1,
                'items' => [
                    ['login' => 'testorg', 'type' => 'Organization'],
                ],
            ]),
            'api.github.com/orgs/testorg' => Http::response([
                'login' => 'testorg',
                'name' => 'TestOrg',
                'blog' => 'testorg.com',
                'description' => 'Test',
            ]),
        ]);

        $importer = new GitHubOrgImporter();
        $importer->start(['max_pages' => 1]);

        $company = Company::where('name', 'TestOrg')->first();
        $this->assertNotNull($company);
        $this->assertEquals('https://testorg.com', $company->website);
    }

    public function test_creates_import_log(): void
    {
        Http::fake([
            'api.github.com/*' => Http::response(['total_count' => 0, 'items' => []]),
        ]);

        $importer = new GitHubOrgImporter();
        $importer->start(['max_pages' => 1]);

        $this->assertDatabaseHas('company_imports', [
            'source' => 'github-orgs',
            'status' => 'completed',
        ]);
    }
}
