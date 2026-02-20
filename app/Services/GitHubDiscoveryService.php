<?php

namespace App\Services;

use App\Models\OpenSourceProject;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubDiscoveryService
{
    private const SEARCH_TOPICS = [
        'self-hosted',
        'open-source-alternative',
        'selfhosted',
        'ai',
        'llm',
        'machine-learning',
    ];

    private const MIN_STARS = 500;

    private const AWESOME_SELFHOSTED_REPO = 'awesome-selfhosted/awesome-selfhosted';

    private ?string $token;

    public function __construct()
    {
        $this->token = config('services.github.token') ?: env('GITHUB_TOKEN');
    }

    /**
     * Run full discovery: topic search + awesome-selfhosted parsing.
     */
    public function discover(): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'errors' => 0];

        foreach (self::SEARCH_TOPICS as $topic) {
            try {
                $topicStats = $this->discoverByTopic($topic);
                $stats['created'] += $topicStats['created'];
                $stats['updated'] += $topicStats['updated'];
            } catch (\Throwable $e) {
                Log::error("GitHub discovery error for topic '{$topic}': {$e->getMessage()}");
                $stats['errors']++;
            }
            sleep(2); // Rate limit between topic searches
        }

        try {
            $awesomeStats = $this->discoverFromAwesomeSelfhosted();
            $stats['created'] += $awesomeStats['created'];
            $stats['updated'] += $awesomeStats['updated'];
        } catch (\Throwable $e) {
            Log::error("Awesome-selfhosted discovery error: {$e->getMessage()}");
            $stats['errors']++;
        }

        Log::info('GitHub OSS discovery complete', $stats);

        return $stats;
    }

    /**
     * Search GitHub repos by topic, filtered by stars and activity.
     */
    public function discoverByTopic(string $topic): array
    {
        $stats = ['created' => 0, 'updated' => 0];
        $pushedAfter = now()->subYear()->format('Y-m-d');

        $page = 1;
        do {
            $response = $this->githubRequest('https://api.github.com/search/repositories', [
                'q' => "topic:{$topic} stars:>".self::MIN_STARS." pushed:>{$pushedAfter}",
                'sort' => 'stars',
                'order' => 'desc',
                'per_page' => 100,
                'page' => $page,
            ]);

            if (! $response->successful()) {
                Log::warning("GitHub search failed for topic '{$topic}': {$response->status()}");
                break;
            }

            $data = $response->json();
            $items = $data['items'] ?? [];

            foreach ($items as $repo) {
                $result = $this->upsertProject($repo);
                $stats[$result]++;
            }

            $page++;
            $totalPages = ceil(($data['total_count'] ?? 0) / 100);
            sleep(2); // Rate limit between pages

        } while ($page <= $totalPages && $page <= 10); // Max 10 pages (1000 results)

        return $stats;
    }

    /**
     * Parse awesome-selfhosted README for GitHub project URLs.
     */
    public function discoverFromAwesomeSelfhosted(): array
    {
        $stats = ['created' => 0, 'updated' => 0];

        $response = $this->githubRequest(
            'https://api.github.com/repos/'.self::AWESOME_SELFHOSTED_REPO.'/readme',
            [],
            ['Accept' => 'application/vnd.github.raw']
        );

        if (! $response->successful()) {
            Log::warning('Failed to fetch awesome-selfhosted README: '.$response->status());

            return $stats;
        }

        $readme = $response->body();

        // Extract GitHub URLs from markdown links: [Name](https://github.com/owner/repo)
        preg_match_all(
            '/\[([^\]]+)\]\((https:\/\/github\.com\/([a-zA-Z0-9._-]+)\/([a-zA-Z0-9._-]+))\)/',
            $readme,
            $matches,
            PREG_SET_ORDER
        );

        $seen = [];
        foreach ($matches as $match) {
            $githubUrl = rtrim($match[2], '/');
            if (isset($seen[$githubUrl])) {
                continue;
            }
            $seen[$githubUrl] = true;

            // Fetch repo details from API to get stars etc.
            try {
                $repoResponse = $this->githubRequest(
                    "https://api.github.com/repos/{$match[3]}/{$match[4]}"
                );

                if (! $repoResponse->successful()) {
                    continue;
                }

                $repo = $repoResponse->json();
                if (($repo['stargazers_count'] ?? 0) < self::MIN_STARS) {
                    continue;
                }

                $result = $this->upsertProject($repo, true);
                $stats[$result]++;

                sleep(1); // Rate limit
            } catch (\Throwable $e) {
                Log::debug("Failed to fetch repo {$match[3]}/{$match[4]}: {$e->getMessage()}");
            }
        }

        return $stats;
    }

    /**
     * Upsert a project from GitHub API repo data.
     */
    private function upsertProject(array $repo, bool $selfHostable = false): string
    {
        $githubUrl = $repo['html_url'];

        $data = [
            'name' => $repo['name'],
            'github_url' => $githubUrl,
            'github_owner' => $repo['owner']['login'] ?? '',
            'github_repo' => $repo['name'],
            'description' => $repo['description'] ?? null,
            'stars' => $repo['stargazers_count'] ?? 0,
            'forks' => $repo['forks_count'] ?? 0,
            'watchers' => $repo['watchers_count'] ?? 0,
            'primary_language' => $repo['language'] ?? null,
            'topics' => $repo['topics'] ?? null,
            'license' => $repo['license']['spdx_id'] ?? null,
            'last_commit_at' => isset($repo['pushed_at']) ? $repo['pushed_at'] : null,
            'github_created_at' => isset($repo['created_at']) ? $repo['created_at'] : null,
        ];

        if ($selfHostable) {
            $data['self_hostable'] = true;
        }

        $existing = OpenSourceProject::where('github_url', $githubUrl)->first();

        if ($existing) {
            $existing->update($data);

            return 'updated';
        }

        OpenSourceProject::create($data);

        return 'created';
    }

    /**
     * Make a GitHub API request with optional token auth.
     */
    private function githubRequest(string $url, array $query = [], array $headers = []): \Illuminate\Http\Client\Response
    {
        $request = Http::timeout(30)
            ->withHeaders(array_merge([
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
            ], $headers));

        if ($this->token) {
            $request = $request->withToken($this->token);
        }

        return $request->get($url, $query);
    }
}
