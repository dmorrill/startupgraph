<?php

namespace Tests\Unit;

use App\Services\GitHubDiscoveryService;
use App\Models\OpenSourceProject;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class GitHubDiscoveryServiceTest extends TestCase
{
    use RefreshDatabase;

    private GitHubDiscoveryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GitHubDiscoveryService();
        
        // Mock GitHub token
        Config::set('services.github.token', 'fake-token');
    }

    public function test_discovers_projects_by_topic()
    {
        $mockResponse = [
            'items' => [
                [
                    'id' => 123456,
                    'name' => 'awesome-project',
                    'full_name' => 'user/awesome-project',
                    'description' => 'An awesome self-hosted project',
                    'html_url' => 'https://github.com/user/awesome-project',
                    'stargazers_count' => 1500,
                    'language' => 'Python',
                    'topics' => ['self-hosted', 'python'],
                    'archived' => false,
                    'fork' => false,
                    'created_at' => '2022-01-01T00:00:00Z',
                    'updated_at' => '2023-01-01T00:00:00Z',
                    'pushed_at' => '2023-12-01T00:00:00Z'
                ]
            ]
        ];

        Http::fake([
            'api.github.com/search/repositories*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->service->discoverByTopic('self-hosted');

        $this->assertEquals(1, $result['created']);
        $this->assertEquals(0, $result['updated']);
        
        // Verify project was created in database
        $this->assertDatabaseHas('open_source_projects', [
            'name' => 'awesome-project',
            'github_owner' => 'user',
            'github_repo' => 'awesome-project'
        ]);
    }

    public function test_updates_existing_projects()
    {
        // Create existing project
        OpenSourceProject::factory()->create([
            'github_id' => 123456,
            'name' => 'old-name',
            'stargazers_count' => 1000
        ]);

        $mockResponse = [
            'items' => [
                [
                    'id' => 123456,
                    'name' => 'updated-name',
                    'full_name' => 'user/updated-name',
                    'description' => 'Updated description',
                    'html_url' => 'https://github.com/user/updated-name',
                    'stargazers_count' => 2000, // Increased stars
                    'language' => 'Python',
                    'topics' => ['self-hosted'],
                    'archived' => false,
                    'fork' => false,
                    'created_at' => '2022-01-01T00:00:00Z',
                    'updated_at' => '2023-01-01T00:00:00Z',
                    'pushed_at' => '2023-12-01T00:00:00Z'
                ]
            ]
        ];

        Http::fake([
            'api.github.com/search/repositories*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->service->discoverByTopic('self-hosted');

        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['updated']);

        // Verify project was updated
        $this->assertDatabaseHas('open_source_projects', [
            'github_id' => 123456,
            'name' => 'updated-name',
            'stargazers_count' => 2000
        ]);
    }

    public function test_filters_out_archived_repositories()
    {
        $mockResponse = [
            'items' => [
                [
                    'id' => 123456,
                    'name' => 'archived-project',
                    'full_name' => 'user/archived-project',
                    'description' => 'An archived project',
                    'html_url' => 'https://github.com/user/archived-project',
                    'stargazers_count' => 1500,
                    'language' => 'Python',
                    'topics' => ['self-hosted'],
                    'archived' => true, // Archived repository
                    'fork' => false,
                    'created_at' => '2022-01-01T00:00:00Z',
                    'updated_at' => '2023-01-01T00:00:00Z',
                    'pushed_at' => '2023-12-01T00:00:00Z'
                ]
            ]
        ];

        Http::fake([
            'api.github.com/search/repositories*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->service->discoverByTopic('self-hosted');

        $this->assertEquals(0, $result['created']);
        
        // Should not create archived repositories
        $this->assertDatabaseMissing('open_source_projects', [
            'github_id' => 123456
        ]);
    }

    public function test_filters_out_fork_repositories()
    {
        $mockResponse = [
            'items' => [
                [
                    'id' => 123456,
                    'name' => 'forked-project',
                    'full_name' => 'user/forked-project',
                    'description' => 'A forked project',
                    'html_url' => 'https://github.com/user/forked-project',
                    'stargazers_count' => 1500,
                    'language' => 'Python',
                    'topics' => ['self-hosted'],
                    'archived' => false,
                    'fork' => true, // Fork repository
                    'created_at' => '2022-01-01T00:00:00Z',
                    'updated_at' => '2023-01-01T00:00:00Z',
                    'pushed_at' => '2023-12-01T00:00:00Z'
                ]
            ]
        ];

        Http::fake([
            'api.github.com/search/repositories*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->service->discoverByTopic('self-hosted');

        $this->assertEquals(0, $result['created']);
        
        // Should not create fork repositories
        $this->assertDatabaseMissing('open_source_projects', [
            'github_id' => 123456
        ]);
    }

    public function test_filters_by_minimum_stars()
    {
        $mockResponse = [
            'items' => [
                [
                    'id' => 123456,
                    'name' => 'low-star-project',
                    'full_name' => 'user/low-star-project',
                    'description' => 'A project with few stars',
                    'html_url' => 'https://github.com/user/low-star-project',
                    'stargazers_count' => 100, // Below 500 minimum
                    'language' => 'Python',
                    'topics' => ['self-hosted'],
                    'archived' => false,
                    'fork' => false,
                    'created_at' => '2022-01-01T00:00:00Z',
                    'updated_at' => '2023-01-01T00:00:00Z',
                    'pushed_at' => '2023-12-01T00:00:00Z'
                ]
            ]
        ];

        Http::fake([
            'api.github.com/search/repositories*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->service->discoverByTopic('self-hosted');

        $this->assertEquals(0, $result['created']);
        
        // Should not create projects below star threshold
        $this->assertDatabaseMissing('open_source_projects', [
            'github_id' => 123456
        ]);
    }

    public function test_handles_github_api_rate_limit()
    {
        Http::fake([
            'api.github.com/search/repositories*' => Http::response([
                'message' => 'API rate limit exceeded'
            ], 403)
        ]);

        $result = $this->service->discoverByTopic('self-hosted');

        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['errors']);
    }

    public function test_handles_github_api_error()
    {
        Http::fake([
            'api.github.com/search/repositories*' => Http::response([
                'message' => 'Internal Server Error'
            ], 500)
        ]);

        $result = $this->service->discoverByTopic('self-hosted');

        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['errors']);
    }

    public function test_handles_malformed_api_response()
    {
        Http::fake([
            'api.github.com/search/repositories*' => Http::response([
                // Missing 'items' key
                'total_count' => 1
            ], 200)
        ]);

        $result = $this->service->discoverByTopic('self-hosted');

        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['errors']);
    }

    public function test_includes_authentication_header()
    {
        Http::fake([
            'api.github.com/search/repositories*' => Http::response(['items' => []], 200)
        ]);

        $this->service->discoverByTopic('self-hosted');

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer fake-token') &&
                   $request->hasHeader('Accept', 'application/vnd.github.v3+json');
        });
    }

    public function test_searches_with_correct_parameters()
    {
        Http::fake([
            'api.github.com/search/repositories*' => Http::response(['items' => []], 200)
        ]);

        $this->service->discoverByTopic('self-hosted');

        Http::assertSent(function ($request) {
            $query = parse_url($request->url(), PHP_URL_QUERY);
            parse_str($query, $params);
            
            return str_contains($params['q'], 'topic:self-hosted') &&
                   str_contains($params['q'], 'stars:>=500') &&
                   $params['sort'] === 'stars' &&
                   $params['order'] === 'desc';
        });
    }

    public function test_full_discovery_processes_all_topics()
    {
        Http::fake([
            'api.github.com/search/repositories*' => Http::response(['items' => []], 200),
            'api.github.com/repos/awesome-selfhosted/awesome-selfhosted/contents/README.md' => 
                Http::response(['content' => base64_encode('# Awesome Self-Hosted\nNo projects found')], 200)
        ]);

        $result = $this->service->discover();

        $this->assertArrayHasKey('created', $result);
        $this->assertArrayHasKey('updated', $result);
        $this->assertArrayHasKey('errors', $result);

        // Should make requests for all configured topics + awesome-selfhosted
        Http::assertSentCount(7); // 6 topics + 1 awesome-selfhosted
    }
}