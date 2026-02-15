<?php

namespace App\Services\Discovery;

use App\Contracts\CompanyDiscoverySource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductHuntDiscoverySource implements CompanyDiscoverySource
{
    private const API_URL = 'https://api.producthunt.com/v2/api/graphql';

    /**
     * Keywords that signal indie/AI-built projects.
     */
    private const INDIE_KEYWORDS = [
        'vibe coded',
        'vibe-coded',
        'built with cursor',
        'built with ai',
        'built with claude',
        'built with chatgpt',
        'built with copilot',
        'weekend project',
        'solo maker',
        'solo founder',
        'indie hacker',
        'side project',
        'one person',
        'built in a weekend',
        'bootstrapped',
        'no-code',
        'open source',
    ];

    public function name(): string
    {
        return 'producthunt';
    }

    public function discover(int $days = 7): array
    {
        $token = config('services.producthunt.token');

        if ($token) {
            return $this->discoverViaApi($token, $days);
        }

        Log::info('ProductHunt: No API token configured, falling back to web scraping');
        return $this->discoverViaScraping();
    }

    private function discoverViaApi(string $token, int $days): array
    {
        $companies = [];
        $postedAfter = now()->subDays($days)->toIso8601String();

        $query = <<<'GRAPHQL'
        query($postedAfter: DateTime, $cursor: String) {
            posts(first: 50, postedAfter: $postedAfter, after: $cursor) {
                edges {
                    node {
                        id
                        name
                        tagline
                        description
                        url
                        website
                        votesCount
                        makers {
                            id
                            name
                        }
                    }
                }
                pageInfo {
                    endCursor
                    hasNextPage
                }
            }
        }
        GRAPHQL;

        $cursor = null;
        $pages = 0;
        $maxPages = 5;

        while ($pages < $maxPages) {
            try {
                $response = Http::timeout(15)
                    ->withHeaders([
                        'Authorization' => "Bearer {$token}",
                        'Content-Type' => 'application/json',
                    ])
                    ->post(self::API_URL, [
                        'query' => $query,
                        'variables' => [
                            'postedAfter' => $postedAfter,
                            'cursor' => $cursor,
                        ],
                    ]);

                if (!$response->successful()) {
                    Log::warning("ProductHunt API error: HTTP {$response->status()}");
                    break;
                }

                $data = $response->json('data.posts');
                if (!$data) {
                    break;
                }

                foreach ($data['edges'] as $edge) {
                    $post = $edge['node'];
                    $combined = strtolower(($post['tagline'] ?? '') . ' ' . ($post['description'] ?? ''));

                    if (!$this->hasIndieSignals($combined, $post)) {
                        continue;
                    }

                    $companies[] = [
                        'name' => $post['name'],
                        'description' => $post['tagline'] ?? $post['description'] ?? null,
                        'website' => $post['website'] ?? null,
                        'source_url' => $post['url'] ?? null,
                        'is_indie' => true,
                        'solo_builder' => count($post['makers'] ?? []) <= 1,
                    ];
                }

                if (!($data['pageInfo']['hasNextPage'] ?? false)) {
                    break;
                }

                $cursor = $data['pageInfo']['endCursor'];
                $pages++;
            } catch (\Exception $e) {
                Log::warning("ProductHunt API error: {$e->getMessage()}");
                break;
            }
        }

        return $companies;
    }

    private function discoverViaScraping(): array
    {
        $companies = [];

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; StartupGraph/1.0)',
                ])
                ->get('https://www.producthunt.com/leaderboard/daily');

            if (!$response->successful()) {
                Log::warning("ProductHunt scrape error: HTTP {$response->status()}");
                return [];
            }

            $html = $response->body();

            // Extract product names and links from the leaderboard page
            // PH uses data attributes and structured markup
            preg_match_all(
                '/<a[^>]*href="\/posts\/([^"]+)"[^>]*>.*?<h3[^>]*>([^<]+)<\/h3>/s',
                $html,
                $matches,
                PREG_SET_ORDER
            );

            if (empty($matches)) {
                // Try alternate pattern - PH changes markup frequently
                preg_match_all(
                    '/data-test="post-name"[^>]*>([^<]+)</s',
                    $html,
                    $nameMatches
                );

                foreach ($nameMatches[1] ?? [] as $name) {
                    $companies[] = [
                        'name' => trim($name),
                        'description' => 'Discovered via Product Hunt daily leaderboard',
                        'source_url' => 'https://www.producthunt.com/leaderboard/daily',
                        'is_indie' => true,
                    ];
                }
            } else {
                foreach ($matches as $match) {
                    $companies[] = [
                        'name' => trim($match[2]),
                        'website' => null,
                        'description' => 'Discovered via Product Hunt daily leaderboard',
                        'source_url' => "https://www.producthunt.com/posts/{$match[1]}",
                        'is_indie' => true,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("ProductHunt scrape error: {$e->getMessage()}");
        }

        return $companies;
    }

    private function hasIndieSignals(string $text, array $post): bool
    {
        foreach (self::INDIE_KEYWORDS as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        // Solo maker signal
        if (count($post['makers'] ?? []) === 1) {
            return true;
        }

        return false;
    }
}
