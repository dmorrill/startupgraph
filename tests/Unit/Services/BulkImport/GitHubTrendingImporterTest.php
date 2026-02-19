<?php

namespace Tests\Unit\Services\BulkImport;

use App\Models\Company;
use App\Services\BulkImport\GitHubTrendingImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GitHubTrendingImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_name(): void
    {
        $importer = new GitHubTrendingImporter();
        $this->assertEquals('github_trending', $importer->source());
    }

    public function test_imports_trending_repos(): void
    {
        $trendingHtml = <<<'HTML'
<article class="Box-row">
  <h2 class="h3 lh-condensed">
    <a href="/vercel/next.js" class="">vercel / next.js</a>
  </h2>
</article>
HTML;

        Http::fake([
            'github.com/trending*' => Http::response($trendingHtml),
            'api.github.com/repos/vercel/next.js' => Http::response([
                'name' => 'next.js',
                'full_name' => 'vercel/next.js',
                'description' => 'The React Framework for the Web',
                'homepage' => 'https://nextjs.org',
                'fork' => false,
                'language' => 'JavaScript',
                'owner' => [
                    'login' => 'vercel',
                    'type' => 'Organization',
                ],
            ]),
            'api.github.com/orgs/vercel' => Http::response([
                'login' => 'vercel',
                'name' => 'Vercel',
                'blog' => 'https://vercel.com',
                'description' => 'Develop. Preview. Ship.',
            ]),
        ]);

        $importer = new GitHubTrendingImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);

        $company = Company::where('name', 'Vercel')->first();
        $this->assertNotNull($company);
        $this->assertEquals('https://nextjs.org', $company->website);
    }

    public function test_skips_forks(): void
    {
        $trendingHtml = '<h2 class="h3 lh-condensed"><a href="/user/forked-repo"></a></h2>';

        Http::fake([
            'github.com/trending*' => Http::response($trendingHtml),
            'api.github.com/repos/user/forked-repo' => Http::response([
                'name' => 'forked-repo',
                'fork' => true,
                'owner' => ['login' => 'user', 'type' => 'User'],
            ]),
        ]);

        $importer = new GitHubTrendingImporter();
        $result = $importer->start();

        $this->assertEquals(0, $result->companies_created);
    }

    public function test_skips_awesome_lists(): void
    {
        $trendingHtml = '<h2 class="h3 lh-condensed"><a href="/user/awesome-python"></a></h2>';

        Http::fake([
            'github.com/trending*' => Http::response($trendingHtml),
            'api.github.com/repos/user/awesome-python' => Http::response([
                'name' => 'awesome-python',
                'fork' => false,
                'description' => 'A curated list of awesome Python frameworks',
                'owner' => ['login' => 'user', 'type' => 'User'],
            ]),
        ]);

        $importer = new GitHubTrendingImporter();
        $result = $importer->start();

        $this->assertEquals(0, $result->companies_created);
    }

    public function test_guesses_ai_category(): void
    {
        $trendingHtml = '<h2 class="h3 lh-condensed"><a href="/org/ml-tool"></a></h2>';

        Http::fake([
            'github.com/trending*' => Http::response($trendingHtml),
            'api.github.com/repos/org/ml-tool' => Http::response([
                'name' => 'ml-tool',
                'fork' => false,
                'description' => 'A machine learning framework for llm applications',
                'language' => 'Python',
                'homepage' => null,
                'owner' => ['login' => 'org', 'type' => 'User'],
            ]),
        ]);

        $importer = new GitHubTrendingImporter();
        $importer->start();

        $company = Company::where('name', 'Ml-tool')->first();
        $this->assertNotNull($company);
        $this->assertEquals('ai_ml', $company->category);
    }

    public function test_handles_empty_trending_page(): void
    {
        Http::fake([
            'github.com/trending*' => Http::response('<html><body>No repos</body></html>'),
        ]);

        $importer = new GitHubTrendingImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);
        $this->assertEquals(0, $result->companies_created);
    }

    public function test_handles_trending_page_failure(): void
    {
        Http::fake([
            'github.com/trending*' => Http::response(null, 500),
        ]);

        $importer = new GitHubTrendingImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);
    }
}
