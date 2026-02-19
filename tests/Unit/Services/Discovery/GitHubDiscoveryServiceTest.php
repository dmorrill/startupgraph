<?php

namespace Tests\Unit\Services\Discovery;

use App\Models\OpenSourceProject;
use App\Services\GitHubDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GitHubDiscoveryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.github.token' => 'test-token']);
    }

    public function test_discover_by_topic_creates_projects(): void
    {
        Http::fake([
            'api.github.com/search/repositories*' => Http::response([
                'total_count' => 1,
                'items' => [
                    [
                        'html_url' => 'https://github.com/org/repo',
                        'name' => 'repo',
                        'owner' => ['login' => 'org'],
                        'description' => 'A cool project',
                        'stargazers_count' => 1000,
                        'forks_count' => 100,
                        'watchers_count' => 1000,
                        'language' => 'Python',
                        'topics' => ['ai', 'self-hosted'],
                        'license' => ['spdx_id' => 'MIT'],
                        'pushed_at' => '2026-01-15T00:00:00Z',
                        'created_at' => '2025-01-01T00:00:00Z',
                    ],
                ],
            ]),
        ]);

        $service = new GitHubDiscoveryService();
        $stats = $service->discoverByTopic('self-hosted');

        $this->assertEquals(1, $stats['created']);
        $this->assertDatabaseHas('open_source_projects', [
            'github_url' => 'https://github.com/org/repo',
            'name' => 'repo',
            'stars' => 1000,
        ]);
    }

    public function test_discover_by_topic_updates_existing(): void
    {
        OpenSourceProject::create([
            'github_url' => 'https://github.com/org/repo',
            'github_owner' => 'org',
            'github_repo' => 'repo',
            'name' => 'repo',
            'stars' => 500,
        ]);

        Http::fake([
            'api.github.com/search/repositories*' => Http::response([
                'total_count' => 1,
                'items' => [
                    [
                        'html_url' => 'https://github.com/org/repo',
                        'name' => 'repo',
                        'owner' => ['login' => 'org'],
                        'description' => 'Updated desc',
                        'stargazers_count' => 2000,
                        'forks_count' => 200,
                        'watchers_count' => 2000,
                        'language' => 'Python',
                        'topics' => [],
                        'license' => null,
                        'pushed_at' => '2026-02-01T00:00:00Z',
                        'created_at' => '2025-01-01T00:00:00Z',
                    ],
                ],
            ]),
        ]);

        $service = new GitHubDiscoveryService();
        $stats = $service->discoverByTopic('ai');

        $this->assertEquals(1, $stats['updated']);
        $this->assertDatabaseHas('open_source_projects', [
            'github_url' => 'https://github.com/org/repo',
            'stars' => 2000,
        ]);
    }

    public function test_discover_from_awesome_selfhosted(): void
    {
        $readme = '# Awesome\n\n- [CoolApp](https://github.com/owner/coolapp) - A cool app\n- [SmallApp](https://github.com/owner/smallapp) - Small\n';

        Http::fake([
            'api.github.com/repos/awesome-selfhosted/awesome-selfhosted/readme' => Http::response($readme),
            'api.github.com/repos/owner/coolapp' => Http::response([
                'html_url' => 'https://github.com/owner/coolapp',
                'name' => 'coolapp',
                'owner' => ['login' => 'owner'],
                'description' => 'A cool app',
                'stargazers_count' => 1500,
                'forks_count' => 50,
                'watchers_count' => 1500,
                'language' => 'Go',
                'topics' => [],
                'license' => ['spdx_id' => 'AGPL-3.0'],
                'pushed_at' => '2026-01-20T00:00:00Z',
                'created_at' => '2024-06-01T00:00:00Z',
            ]),
            'api.github.com/repos/owner/smallapp' => Http::response([
                'html_url' => 'https://github.com/owner/smallapp',
                'name' => 'smallapp',
                'owner' => ['login' => 'owner'],
                'stargazers_count' => 100, // Below MIN_STARS
                'forks_count' => 5,
                'watchers_count' => 100,
                'language' => 'Rust',
                'topics' => [],
                'license' => null,
                'pushed_at' => '2026-01-20T00:00:00Z',
                'created_at' => '2025-01-01T00:00:00Z',
            ]),
        ]);

        $service = new GitHubDiscoveryService();
        $stats = $service->discoverFromAwesomeSelfhosted();

        $this->assertEquals(1, $stats['created']); // Only coolapp (1500 stars > 500 min)
        $this->assertDatabaseHas('open_source_projects', ['name' => 'coolapp']);
        $this->assertDatabaseMissing('open_source_projects', ['name' => 'smallapp']);
    }

    public function test_discover_handles_api_failure(): void
    {
        Http::fake([
            'api.github.com/search/repositories*' => Http::response('', 403),
        ]);

        $service = new GitHubDiscoveryService();
        $stats = $service->discoverByTopic('ai');

        $this->assertEquals(0, $stats['created']);
        $this->assertEquals(0, $stats['updated']);
    }
}
