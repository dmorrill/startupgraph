<?php

namespace App\Services\Discovery;

use App\Contracts\CompanyDiscoverySource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YCombinatorDiscoverySource implements CompanyDiscoverySource
{
    // YC's Algolia-powered API for their company directory
    private const API_URL = 'https://45bwzj1sgc-dsn.algolia.net/1/indexes/YCCompany_production/query';

    private const APP_ID = '45bwzj1sgc';

    private const API_KEY = 'MjBjYjRiMzY0NzdhZWY0NjExY2NhZjYxMGIxYjc2MTAwNWFkNTkwNTc4NjgxYjU0YzFhYTY2ZGQ5OGY5NDMxZnJlc3RyaWN0SW5kaWNlcz0lNUIlMjJZQ0NvbXBhbnlfcHJvZHVjdGlvbiUyMiUyQyUyMllDQ29tcGFueV9CeV9MYXVuY2hfRGF0ZV9wcm9kdWN0aW9uJTIyJTVEJnRhZ0ZpbHRlcnM9JTVCJTIyeWNkY19wdWJsaWMlMjIlNUQmYW5hbHl0aWNzVGFncz0lNUIlMjJ5Y2RjJTIyJTVE';

    public function name(): string
    {
        return 'yc';
    }

    public function discover(int $days = 7): array
    {
        try {
            // Query for recently launched companies
            $response = Http::withHeaders([
                'X-Algolia-Application-Id' => self::APP_ID,
                'X-Algolia-API-Key' => self::API_KEY,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(self::API_URL, [
                'query' => '',
                'hitsPerPage' => 50,
                'page' => 0,
                // Sort by newest; filter to recent batches
                'facetFilters' => $this->getRecentBatchFilters(),
            ]);

            if (! $response->successful()) {
                Log::warning("YC discovery failed: HTTP {$response->status()}");

                return $this->fallbackScrape();
            }

            $data = $response->json();
            $hits = $data['hits'] ?? [];

            return $this->parseAlgoliaHits($hits);
        } catch (\Exception $e) {
            Log::warning("YC discovery error: {$e->getMessage()}");

            return $this->fallbackScrape();
        }
    }

    private function getRecentBatchFilters(): array
    {
        // Generate current and recent batch identifiers (e.g., W2026, S2025)
        $year = (int) date('Y');
        $month = (int) date('m');

        $batches = [];
        // Current year's batches
        $batches[] = "batch:W{$year}";
        $batches[] = "batch:S{$year}";
        // Previous year's batches
        $batches[] = 'batch:W'.($year - 1);
        $batches[] = 'batch:S'.($year - 1);

        return [$batches]; // OR within the array
    }

    private function parseAlgoliaHits(array $hits): array
    {
        $companies = [];

        foreach ($hits as $hit) {
            $name = $hit['name'] ?? null;
            if (! $name) {
                continue;
            }

            $company = [
                'name' => $name,
                'description' => $hit['one_liner'] ?? ($hit['long_description'] ?? null),
                'website' => $hit['website'] ?? null,
                'batch' => $hit['batch'] ?? null,
                'source_url' => isset($hit['slug'])
                    ? "https://www.ycombinator.com/companies/{$hit['slug']}"
                    : null,
            ];

            $companies[] = $company;
        }

        return $companies;
    }

    /**
     * Fallback: scrape the YC company directory HTML if API fails.
     */
    private function fallbackScrape(): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            ])->timeout(30)->get('https://www.ycombinator.com/companies?batch=latest');

            if (! $response->successful()) {
                return [];
            }

            $html = $response->body();
            $companies = [];

            // Extract company data from Next.js JSON embedded in page
            if (preg_match('/<script id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/s', $html, $match)) {
                $data = json_decode($match[1], true);
                $companyList = $data['props']['pageProps']['companies'] ?? [];

                foreach (array_slice($companyList, 0, 50) as $item) {
                    $companies[] = [
                        'name' => $item['name'] ?? '',
                        'description' => $item['one_liner'] ?? null,
                        'website' => $item['website'] ?? null,
                        'batch' => $item['batch'] ?? null,
                        'source_url' => isset($item['slug'])
                            ? "https://www.ycombinator.com/companies/{$item['slug']}"
                            : null,
                    ];
                }
            }

            return $companies;
        } catch (\Exception $e) {
            Log::warning("YC fallback scrape failed: {$e->getMessage()}");

            return [];
        }
    }
}
