<?php

namespace App\Services\Discovery;

use App\Contracts\CompanyDiscoverySource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WellfoundDiscoverySource implements CompanyDiscoverySource
{
    private const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    private const GRAPHQL_URL = 'https://wellfound.com/graphql';

    public function name(): string
    {
        return 'wellfound';
    }

    public function discover(int $days = 7): array
    {
        try {
            $companies = $this->discoverViaGraphQL();

            if (!empty($companies)) {
                return $companies;
            }

            return $this->discoverViaScrape();
        } catch (\Exception $e) {
            Log::warning("Wellfound discovery error: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Attempt discovery via Wellfound's GraphQL API (used by their frontend).
     */
    private function discoverViaGraphQL(): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])->timeout(30)->post(self::GRAPHQL_URL, [
                'operationName' => 'StartupSearchResults',
                'variables' => [
                    'filter' => [
                        'raised' => ['min' => 100000],
                        'signal' => ['slug' => 'recently-funded'],
                    ],
                    'page' => 1,
                    'perPage' => 50,
                ],
                'query' => <<<'GRAPHQL'
                    query StartupSearchResults($filter: StartupFilter, $page: Int, $perPage: Int) {
                        startups(filter: $filter, page: $page, perPage: $perPage) {
                            edges {
                                node {
                                    name
                                    highConcept
                                    companyUrl
                                    logoUrl
                                    slug
                                    companySize
                                    locationTags {
                                        displayName
                                    }
                                    totalRaised {
                                        amount
                                        currency
                                    }
                                    lastRoundType
                                }
                            }
                        }
                    }
                GRAPHQL,
            ]);

            if (!$response->successful()) {
                Log::info("Wellfound GraphQL returned HTTP {$response->status()}, falling back to scrape");
                return [];
            }

            $data = $response->json();
            $edges = $data['data']['startups']['edges'] ?? [];

            if (empty($edges)) {
                return [];
            }

            $companies = [];
            foreach ($edges as $edge) {
                $node = $edge['node'] ?? [];
                $name = $node['name'] ?? null;

                if (!$name) {
                    continue;
                }

                $company = [
                    'name' => $name,
                    'description' => $node['highConcept'] ?? null,
                    'website' => $node['companyUrl'] ?? null,
                    'source_url' => isset($node['slug'])
                        ? "https://wellfound.com/company/{$node['slug']}"
                        : null,
                ];

                // Location
                $locations = $node['locationTags'] ?? [];
                if (!empty($locations)) {
                    $company['location'] = $locations[0]['displayName'] ?? null;
                }

                // Funding
                $totalRaised = $node['totalRaised'] ?? null;
                if ($totalRaised && isset($totalRaised['amount'])) {
                    $company['funding_amount'] = (float) $totalRaised['amount'];
                }

                $roundType = $node['lastRoundType'] ?? null;
                if ($roundType) {
                    $company['funding_round'] = $roundType;
                }

                $companies[] = $company;
            }

            return $companies;
        } catch (\Exception $e) {
            Log::info("Wellfound GraphQL failed: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fallback: scrape the trending/recently funded startups page.
     */
    private function discoverViaScrape(): array
    {
        $urls = [
            'https://wellfound.com/startups/trending',
            'https://wellfound.com/startups',
        ];

        foreach ($urls as $url) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => self::USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])->timeout(30)->get($url);

                if (!$response->successful()) {
                    continue;
                }

                $companies = $this->parseHtml($response->body());

                if (!empty($companies)) {
                    return $companies;
                }
            } catch (\Exception $e) {
                Log::info("Wellfound scrape of {$url} failed: {$e->getMessage()}");
                continue;
            }
        }

        return [];
    }

    private function parseHtml(string $html): array
    {
        $companies = [];

        // Try Next.js data first
        if (preg_match('/<script id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/s', $html, $match)) {
            $data = json_decode($match[1], true);
            $startups = $data['props']['pageProps']['startups']
                ?? $data['props']['pageProps']['results']
                ?? [];

            foreach (array_slice($startups, 0, 50) as $startup) {
                $name = $startup['name'] ?? null;
                if (!$name) {
                    continue;
                }

                $company = [
                    'name' => $name,
                    'description' => $startup['high_concept'] ?? ($startup['highConcept'] ?? null),
                    'website' => $startup['company_url'] ?? ($startup['companyUrl'] ?? null),
                    'source_url' => isset($startup['slug'])
                        ? "https://wellfound.com/company/{$startup['slug']}"
                        : null,
                ];

                if (isset($startup['total_raised'])) {
                    $company['funding_amount'] = (float) $startup['total_raised'];
                }

                $companies[] = $company;
            }

            return $companies;
        }

        // Fallback: parse structured data / JSON-LD
        if (preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
            foreach ($matches[1] as $json) {
                $data = json_decode($json, true);
                if (isset($data['@type']) && $data['@type'] === 'Organization') {
                    $companies[] = [
                        'name' => $data['name'] ?? null,
                        'description' => $data['description'] ?? null,
                        'website' => $data['url'] ?? null,
                    ];
                }
            }
        }

        return array_filter($companies, fn ($c) => !empty($c['name']));
    }
}
