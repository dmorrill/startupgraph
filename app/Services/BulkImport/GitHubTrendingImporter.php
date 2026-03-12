<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubTrendingImporter extends BaseBulkImporter
{
    private const TRENDING_URL = 'https://github.com/trending';

    private const API_URL = 'https://api.github.com';

    public function source(): string
    {
        return 'github_trending';
    }

    public function import(array $options = []): void
    {
        Log::info('GitHub Trending import starting');

        // Scrape trending repos for different time ranges
        $ranges = ['daily', 'weekly', 'monthly'];
        $repos = [];

        foreach ($ranges as $range) {
            $scraped = $this->scrapeTrending($range);
            foreach ($scraped as $repo) {
                $repos[$repo] = true; // dedupe
            }
            $this->rateLimitSleep(1.0);
        }

        Log::info('GitHub Trending: found '.count($repos).' unique repos');

        foreach (array_keys($repos) as $repoPath) {
            $this->processRepo($repoPath);
            $this->rateLimitSleep(0.5); // Be polite to GitHub API
        }

        $this->importLog->update([
            'total_processed' => $this->processed,
            'companies_created' => $this->created,
        ]);

        Log::info("GitHub Trending import complete: {$this->created} created, {$this->updated} updated");
    }

    private function scrapeTrending(string $since = 'daily'): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
        ])->timeout(15)->get(self::TRENDING_URL, ['since' => $since]);

        if (! $response->successful()) {
            Log::warning("GitHub trending page returned HTTP {$response->status()} for {$since}");

            return [];
        }

        $html = $response->body();

        // Extract repo paths from trending page
        preg_match_all('/class="h3 lh-condensed".*?href="([^"]+)"/s', $html, $matches);

        $repos = [];
        if (! empty($matches[1])) {
            foreach ($matches[1] as $path) {
                $path = ltrim($path, '/');
                if (substr_count($path, '/') === 1) {
                    $repos[] = $path;
                }
            }
        }

        return $repos;
    }

    private function processRepo(string $repoPath): void
    {
        $response = Http::withHeaders([
            'User-Agent' => 'StartupGraph/1.0 research@startupgraph.com',
            'Accept' => 'application/vnd.github.v3+json',
        ])->timeout(15)->get(self::API_URL."/repos/{$repoPath}");

        if (! $response->successful()) {
            $this->skipped++;
            $this->processed++;

            return;
        }

        $repo = $response->json();

        // Skip forks
        if ($repo['fork'] ?? false) {
            $this->skipped++;
            $this->processed++;

            return;
        }

        $homepage = $repo['homepage'] ?? null;
        $description = $repo['description'] ?? null;
        $owner = $repo['owner'] ?? [];
        $ownerType = $owner['type'] ?? 'User'; // "Organization" or "User"

        // Try to get org name if it's an org
        $name = null;
        if ($ownerType === 'Organization') {
            $name = $owner['login'] ?? null;
            // Try to get a better name from the org API
            $orgResponse = Http::withHeaders([
                'User-Agent' => 'StartupGraph/1.0',
                'Accept' => 'application/vnd.github.v3+json',
            ])->timeout(10)->get(self::API_URL."/orgs/{$name}");

            if ($orgResponse->successful()) {
                $orgData = $orgResponse->json();
                $name = $orgData['name'] ?? $orgData['login'] ?? $name;
                $homepage = $homepage ?: ($orgData['blog'] ?? null);
                $description = $description ?: ($orgData['description'] ?? null);
            }
        } else {
            // For user repos, use the repo name as the "company/product" name
            $name = $repo['name'] ?? null;
        }

        if (! $name) {
            $this->skipped++;
            $this->processed++;

            return;
        }

        // Skip repos that are clearly not companies/products
        $skipPatterns = ['/^awesome-/i', '/^list-/i', '/^\./', '/^dotfiles$/i'];
        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $name)) {
                $this->skipped++;
                $this->processed++;

                return;
            }
        }

        $website = null;
        if ($homepage && filter_var($homepage, FILTER_VALIDATE_URL)) {
            $website = $homepage;
        }

        $this->upsertCompany([
            'name' => ucfirst($name),
            'description' => $description ? mb_substr($description, 0, 500) : null,
            'website' => $website,
            'category' => $this->guessCategory($repo['language'] ?? '', $description ?? ''),
        ]);
    }

    private function guessCategory(string $language, string $description): ?string
    {
        $desc = strtolower($description);

        if (str_contains($desc, 'machine learning') || str_contains($desc, ' ai ') || str_contains($desc, 'llm')) {
            return 'ai_ml';
        }
        if (str_contains($desc, 'fintech') || str_contains($desc, 'payment') || str_contains($desc, 'banking')) {
            return 'fintech';
        }
        if (str_contains($desc, 'health') || str_contains($desc, 'medical')) {
            return 'healthcare';
        }
        if (str_contains($desc, 'developer') || str_contains($desc, 'devtool') || str_contains($desc, 'sdk')) {
            return 'developer_tools';
        }

        return null;
    }
}
