<?php

namespace App\Services\Discovery;

use App\Contracts\CompanyDiscoverySource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HackerNewsDiscoverySource implements CompanyDiscoverySource
{
    private const API_BASE = 'https://hacker-news.firebaseio.com/v0';

    public function name(): string
    {
        return 'hackernews';
    }

    public function discover(int $days = 7): array
    {
        $companies = [];
        $seen = [];
        $cutoff = now()->subDays($days)->timestamp;

        foreach (['topstories', 'newstories'] as $feed) {
            try {
                $ids = $this->fetchStoryIds($feed);
                $companies = array_merge($companies, $this->processStories($ids, $cutoff, $seen));
            } catch (\Exception $e) {
                Log::warning("HackerNews discovery error [{$feed}]: {$e->getMessage()}");
            }
        }

        return $companies;
    }

    private function fetchStoryIds(string $feed): array
    {
        $response = Http::timeout(15)->retry(2, 1000)
            ->get(self::API_BASE . "/{$feed}.json");

        if (!$response->successful()) {
            Log::warning("HackerNews API error fetching {$feed}: HTTP {$response->status()}");
            return [];
        }

        return $response->json() ?? [];
    }

    private function processStories(array $ids, int $cutoff, array &$seen): array
    {
        $companies = [];

        // HN returns up to 500 IDs; process in batches to be respectful
        $ids = array_slice($ids, 0, 200);

        foreach ($ids as $id) {
            try {
                $item = $this->fetchItem($id);
                if (!$item) {
                    continue;
                }

                // Skip items older than cutoff
                $time = $item['time'] ?? 0;
                if ($time < $cutoff) {
                    continue;
                }

                $title = $item['title'] ?? '';

                // Filter for Show HN / Launch HN posts
                if (!preg_match('/^(Show HN|Launch HN)\s*:\s*/i', $title, $matches)) {
                    continue;
                }

                // Extract company name by stripping the prefix
                $companyName = trim(preg_replace('/^(Show HN|Launch HN)\s*:\s*/i', '', $title));

                // Strip trailing descriptions after dash/em-dash/comma/colon
                $companyName = preg_replace('/\s*[\–\—\-]\s+.*$/', '', $companyName);
                $companyName = preg_replace('/\s*[,:]\s+.*$/', '', $companyName);

                // Strip trailing emoji/special chars
                $companyName = preg_replace('/\s*[^\w\s.&\'+()-]+\s*$/', '', $companyName);
                $companyName = trim($companyName);

                if (empty($companyName)) {
                    continue;
                }

                // Skip entries that look like descriptions, not company names
                // Company names are typically 1-4 words; long strings are descriptions
                $wordCount = str_word_count($companyName);
                if ($wordCount > 6) {
                    Log::debug("HackerNews: Skipping likely non-company name: {$companyName}");
                    continue;
                }

                // Skip names that contain common description patterns
                if (preg_match('/\b(that|which|for|with|how|what|this|your|the|and|from|using)\b/i', $companyName)
                    && $wordCount > 3) {
                    Log::debug("HackerNews: Skipping descriptive title: {$companyName}");
                    continue;
                }

                // Deduplicate by normalized name
                $normalizedName = strtolower($companyName);
                if (isset($seen[$normalizedName])) {
                    continue;
                }
                $seen[$normalizedName] = true;

                $url = $item['url'] ?? null;
                $hnUrl = "https://news.ycombinator.com/item?id={$id}";

                $company = [
                    'name' => $companyName,
                    'description' => "Discovered via Hacker News: {$title}",
                    'website' => $url,
                    'source_url' => $hnUrl,
                ];

                $companies[] = $company;
            } catch (\Exception $e) {
                // Skip individual item errors silently
                continue;
            }
        }

        return $companies;
    }

    private function fetchItem(int $id): ?array
    {
        $response = Http::timeout(10)->get(self::API_BASE . "/item/{$id}.json");

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }
}
