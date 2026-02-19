<?php

namespace App\Services\Discovery;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductHuntDiscoveryService
{
    private string $apiUrl = 'https://api.producthunt.com/v2/api/graphql';

    public function __construct(
        private ?string $token = null,
    ) {
        $this->token = $token ?? config('services.producthunt.token');
    }

    /**
     * Discover companies from Product Hunt launches.
     */
    public function discover(int $limit = 50, ?string $cursor = null): array
    {
        if (!$this->token) {
            throw new \RuntimeException('Product Hunt API token not configured. Set PRODUCTHUNT_TOKEN in .env');
        }

        $query = <<<'GRAPHQL'
        query($first: Int!, $after: String) {
            posts(first: $first, after: $after, order: VOTES) {
                edges {
                    node {
                        id
                        name
                        tagline
                        url
                        website
                        votesCount
                        topics { edges { node { name } } }
                        makers { id name }
                    }
                    cursor
                }
                pageInfo { hasNextPage endCursor }
            }
        }
        GRAPHQL;

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl, [
            'query' => $query,
            'variables' => ['first' => $limit, 'after' => $cursor],
        ]);

        if (!$response->successful()) {
            Log::error('Product Hunt API error', ['status' => $response->status()]);
            return ['companies' => [], 'cursor' => null, 'hasMore' => false];
        }

        $data = $response->json('data.posts');
        $companies = [];

        foreach ($data['edges'] as $edge) {
            $node = $edge['node'];
            $companies[] = [
                'name' => $node['name'],
                'website' => $node['website'] ?? $node['url'],
                'description' => $node['tagline'],
                'source' => 'producthunt',
                'source_id' => $node['id'],
                'metadata' => [
                    'votes' => $node['votesCount'],
                    'topics' => collect($node['topics']['edges'])->pluck('node.name')->toArray(),
                ],
            ];
        }

        return [
            'companies' => $companies,
            'cursor' => $data['pageInfo']['endCursor'],
            'hasMore' => $data['pageInfo']['hasNextPage'],
        ];
    }
}
