<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubOrgImporter extends BaseBulkImporter
{
    private const GITHUB_API = 'https://api.github.com';

    private const SEARCH_QUERIES = [
        'type:org location:"san francisco" repos:>5',
        'type:org location:"new york" repos:>5',
        'type:org location:"london" repos:>5',
        'type:org location:"berlin" repos:>5',
        'type:org location:"seattle" repos:>5',
        'type:org location:"austin" repos:>5',
        'type:org location:"denver" repos:>5',
        'type:org location:"los angeles" repos:>5',
        'type:org location:"boston" repos:>5',
        'type:org location:"toronto" repos:>5',
        'type:org created:>2020-01-01 repos:>10',
    ];

    private ?string $token;
    private int $requestCount = 0;
    private float $windowStart;

    public function source(): string
    {
        return 'github-orgs';
    }

    public function import(array $options = []): void
    {
        $this->token = env('GITHUB_TOKEN') ?: null;
        $this->windowStart = microtime(true);

        Log::info("GitHub org import starting", ['has_token' => (bool) $this->token]);

        foreach (self::SEARCH_QUERIES as $query) {
            Log::info("Searching GitHub orgs: {$query}");
            $this->searchAndImport($query, $options['max_pages'] ?? 10);
        }

        Log::info("GitHub org import complete", $this->getStats());
    }

    private function searchAndImport(string $query, int $maxPages): void
    {
        // GitHub search API returns max 1000 results (10 pages of 100)
        $maxPages = min($maxPages, 10);

        for ($page = 1; $page <= $maxPages; $page++) {
            $this->enforceRateLimit();

            $response = $this->githubGet('/search/users', [
                'q' => $query,
                'per_page' => 100,
                'page' => $page,
            ]);

            if (!$response || !$response->successful()) {
                if ($response && $response->status() === 403) {
                    Log::warning("GitHub: Rate limited, waiting 60s");
                    sleep(60);
                    $page--; // Retry
                    continue;
                }
                Log::warning("GitHub: Search failed for query: {$query}");
                break;
            }

            $data = $response->json();
            $items = $data['items'] ?? [];

            if (empty($items)) break;

            foreach ($items as $item) {
                $login = $item['login'] ?? '';
                if (!$login) continue;

                $this->importOrg($login);
            }

            // Check if there are more results
            $totalCount = $data['total_count'] ?? 0;
            if ($page * 100 >= $totalCount) break;
        }
    }

    private function importOrg(string $login): void
    {
        $this->enforceRateLimit();

        $response = $this->githubGet("/orgs/{$login}");

        if (!$response || !$response->successful()) return;

        $org = $response->json();

        $name = $org['name'] ?? $org['login'] ?? '';
        $blog = $org['blog'] ?? '';
        $description = $org['description'] ?? '';
        $location = $org['location'] ?? '';
        $email = $org['email'] ?? '';
        $twitter = $org['twitter_username'] ?? '';
        $createdAt = $org['created_at'] ?? '';

        // Skip orgs without a name or website (less likely to be companies)
        if (!$name || strlen($name) < 2) {
            $this->skipped++;
            $this->processed++;
            return;
        }

        // Parse location into city/country
        $locationParts = $this->parseLocation($location);

        // Parse website
        $website = $blog;
        if ($website && !str_starts_with($website, 'http')) {
            $website = 'https://' . $website;
        }

        // Parse founded date from created_at
        $foundedDate = null;
        if ($createdAt && preg_match('/^(\d{4}-\d{2}-\d{2})/', $createdAt, $m)) {
            $foundedDate = $m[1];
        }

        $this->upsertCompany([
            'name' => $name,
            'website' => $website ?: null,
            'description' => $description ? substr($description, 0, 500) : null,
            'city' => $locationParts['city'] ?? null,
            'country' => $locationParts['country'] ?? null,
            'status' => 'operating',
            'founded_date' => $foundedDate,
        ]);
    }

    private function githubGet(string $path, array $query = []): ?\Illuminate\Http\Client\Response
    {
        $this->requestCount++;

        $request = Http::timeout(30)
            ->withUserAgent('StartupGraph/1.0')
            ->accept('application/vnd.github.v3+json');

        if ($this->token) {
            $request = $request->withToken($this->token);
        }

        try {
            return $request->get(self::GITHUB_API . $path, $query);
        } catch (\Exception $e) {
            Log::warning("GitHub API error: {$e->getMessage()}");
            return null;
        }
    }

    private function enforceRateLimit(): void
    {
        // 30 requests per minute
        $elapsed = microtime(true) - $this->windowStart;

        if ($this->requestCount >= 28 && $elapsed < 60) {
            $wait = 60 - $elapsed + 1;
            Log::info("GitHub: Rate limit pause for {$wait}s");
            sleep((int) ceil($wait));
            $this->requestCount = 0;
            $this->windowStart = microtime(true);
        }

        if ($elapsed >= 60) {
            $this->requestCount = 0;
            $this->windowStart = microtime(true);
        }
    }

    private function parseLocation(?string $location): array
    {
        if (!$location) return [];

        $cityCountryMap = [
            'san francisco' => ['city' => 'San Francisco', 'country' => 'US'],
            'new york' => ['city' => 'New York', 'country' => 'US'],
            'london' => ['city' => 'London', 'country' => 'GB'],
            'berlin' => ['city' => 'Berlin', 'country' => 'DE'],
            'seattle' => ['city' => 'Seattle', 'country' => 'US'],
            'austin' => ['city' => 'Austin', 'country' => 'US'],
            'denver' => ['city' => 'Denver', 'country' => 'US'],
            'los angeles' => ['city' => 'Los Angeles', 'country' => 'US'],
            'boston' => ['city' => 'Boston', 'country' => 'US'],
            'toronto' => ['city' => 'Toronto', 'country' => 'CA'],
        ];

        $lower = strtolower($location);
        foreach ($cityCountryMap as $key => $val) {
            if (str_contains($lower, $key)) return $val;
        }

        // Try to parse "City, Country" or "City, State, Country"
        $parts = array_map('trim', explode(',', $location));
        if (count($parts) >= 2) {
            return ['city' => $parts[0]];
        }

        return ['city' => $location];
    }
}
