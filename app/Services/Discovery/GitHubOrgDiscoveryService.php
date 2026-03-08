<?php

namespace App\Services\Discovery;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubOrgDiscoveryService
{
    private string $baseUrl = 'https://api.github.com';

    public function __construct(
        private ?string $token = null,
    ) {
        $this->token = $token ?? config('services.github.token');
    }

    /**
     * Discover companies from GitHub organizations.
     * Uses the search API to find orgs with high follower counts.
     */
    public function discoverOrgs(int $minFollowers = 100, int $page = 1, int $perPage = 30): array
    {
        $headers = ['Accept' => 'application/vnd.github+json'];
        if ($this->token) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }

        $response = Http::withHeaders($headers)->get("{$this->baseUrl}/search/users", [
            'q' => "type:org followers:>={$minFollowers}",
            'sort' => 'followers',
            'order' => 'desc',
            'per_page' => $perPage,
            'page' => $page,
        ]);

        if (!$response->successful()) {
            Log::error('GitHub API error', ['status' => $response->status()]);
            return ['orgs' => [], 'total' => 0];
        }

        $data = $response->json();

        $orgs = collect($data['items'])->map(function ($org) use ($headers) {
            // Fetch full org details
            $details = Http::withHeaders($headers)->get($org['url'])->json();

            return [
                'name' => $details['name'] ?? $org['login'],
                'website' => $details['blog'] ?? null,
                'description' => $details['description'] ?? null,
                'source' => 'github',
                'source_id' => $org['login'],
                'metadata' => [
                    'github_url' => $org['html_url'],
                    'followers' => $details['followers'] ?? 0,
                    'public_repos' => $details['public_repos'] ?? 0,
                    'location' => $details['location'] ?? null,
                ],
            ];
        })->toArray();

        return ['orgs' => $orgs, 'total' => $data['total_count']];
    }
}
