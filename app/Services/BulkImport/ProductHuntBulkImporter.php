<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductHuntBulkImporter extends BaseBulkImporter
{
    private const GRAPHQL_URL = 'https://api.producthunt.com/v2/api/graphql';

    private const PER_PAGE = 20;

    public function source(): string
    {
        return 'producthunt';
    }

    public function import(array $options = []): void
    {
        $token = config('services.producthunt.token');
        if (! $token) {
            throw new \RuntimeException('PRODUCT_HUNT_TOKEN not configured. Set it in .env');
        }

        $cursor = $options['resume_cursor'] ?? null;
        $maxPages = $options['max_pages'] ?? 500; // Safety limit
        $page = 0;

        Log::info('Product Hunt bulk import starting'.($cursor ? " from cursor {$cursor}" : ''));

        while ($page < $maxPages) {
            $query = $this->buildQuery($cursor);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post(self::GRAPHQL_URL, ['query' => $query]);

            if (! $response->successful()) {
                Log::warning("Product Hunt API returned HTTP {$response->status()}");
                break;
            }

            $data = $response->json();
            $posts = $data['data']['posts']['edges'] ?? [];

            if (empty($posts)) {
                break;
            }

            foreach ($posts as $edge) {
                $this->importPost($edge['node']);
            }

            $pageInfo = $data['data']['posts']['pageInfo'] ?? [];
            $hasNext = $pageInfo['hasNextPage'] ?? false;
            $cursor = $pageInfo['endCursor'] ?? null;

            $this->importLog->update([
                'last_offset' => $cursor,
                'last_page' => $page,
                'total_processed' => $this->processed,
                'companies_created' => $this->created,
            ]);

            $page++;
            Log::info("Product Hunt: page {$page}, processed: {$this->processed}, created: {$this->created}");

            if (! $hasNext) {
                break;
            }

            $this->rateLimitSleep(1.0);
        }

        Log::info("Product Hunt import complete: {$this->created} created, {$this->updated} updated");
    }

    private function buildQuery(?string $cursor): string
    {
        $after = $cursor ? ', after: "'.$cursor.'"' : '';

        return <<<GRAPHQL
        {
            posts(first: 20{$after}, order: NEWEST) {
                edges {
                    node {
                        id
                        name
                        tagline
                        website
                        url
                        votesCount
                        createdAt
                        topics {
                            edges {
                                node {
                                    name
                                }
                            }
                        }
                        makers {
                            id
                            name
                        }
                    }
                }
                pageInfo {
                    hasNextPage
                    endCursor
                }
            }
        }
        GRAPHQL;
    }

    private function importPost(array $post): void
    {
        $name = $post['name'] ?? null;
        $website = $post['website'] ?? null;

        if (! $name) {
            return;
        }

        $topics = collect($post['topics']['edges'] ?? [])
            ->pluck('node.name')
            ->implode(', ');

        $this->upsertCompany([
            'name' => $name,
            'description' => $post['tagline'] ?? null,
            'website' => $website,
            'category' => $this->mapTopicToCategory($topics),
            'founded_date' => isset($post['createdAt']) ? substr($post['createdAt'], 0, 10) : null,
        ]);
    }

    private function mapTopicToCategory(string $topics): ?string
    {
        $topics = strtolower($topics);

        $map = [
            'artificial intelligence' => 'ai_ml',
            'machine learning' => 'ai_ml',
            'fintech' => 'fintech',
            'health' => 'healthcare',
            'developer tools' => 'developer_tools',
            'productivity' => 'enterprise',
            'consumer' => 'consumer',
        ];

        foreach ($map as $keyword => $category) {
            if (str_contains($topics, $keyword)) {
                return $category;
            }
        }

        return null;
    }
}
