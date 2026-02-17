<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WikipediaDefunctImporter extends BaseBulkImporter
{
    private const WIKIPEDIA_API = 'https://en.wikipedia.org/w/api.php';

    private const CATEGORIES = [
        'Category:Defunct_software_companies_of_the_United_States',
        'Category:Defunct_technology_companies_of_the_United_States',
    ];

    public function source(): string
    {
        return 'wikipedia-defunct';
    }

    public function import(array $options = []): void
    {
        Log::info("Wikipedia defunct companies import starting");

        foreach (self::CATEGORIES as $category) {
            Log::info("Importing from {$category}");
            $this->importCategory($category, 'closed');
        }

        Log::info("Wikipedia defunct companies import complete", $this->getStats());
    }

    private function importCategory(string $category, string $status): void
    {
        $continue = null;

        do {
            $params = [
                'action' => 'query',
                'list' => 'categorymembers',
                'cmtitle' => $category,
                'cmlimit' => 500,
                'cmtype' => 'page',
                'cmnamespace' => 0, // Main namespace only
                'format' => 'json',
            ];

            if ($continue) {
                $params['cmcontinue'] = $continue;
            }

            $response = Http::timeout(30)
                ->withUserAgent('StartupGraph/1.0 (https://startupgraph.com)')
                ->get(self::WIKIPEDIA_API, $params);

            if (!$response->successful()) {
                Log::warning("Wikipedia: Failed to fetch category {$category}");
                break;
            }

            $data = $response->json();
            $members = $data['query']['categorymembers'] ?? [];
            $continue = $data['continue']['cmcontinue'] ?? null;

            // Batch fetch extracts for up to 50 titles at a time
            $titles = array_map(fn($m) => $m['title'], $members);
            $chunks = array_chunk($titles, 50);

            foreach ($chunks as $chunk) {
                $this->fetchAndImportExtracts($chunk, $status);
                $this->rateLimitSleep(1.0);
            }

        } while ($continue);
    }

    private function fetchAndImportExtracts(array $titles, string $status): void
    {
        $response = Http::timeout(30)
            ->withUserAgent('StartupGraph/1.0 (https://startupgraph.com)')
            ->get(self::WIKIPEDIA_API, [
                'action' => 'query',
                'titles' => implode('|', $titles),
                'prop' => 'extracts',
                'exintro' => true,
                'explaintext' => true,
                'exlimit' => count($titles),
                'format' => 'json',
            ]);

        if (!$response->successful()) {
            // Fall back to just importing names
            foreach ($titles as $title) {
                $this->upsertCompany([
                    'name' => $title,
                    'status' => $status,
                    'country' => 'US',
                ]);
            }
            return;
        }

        $pages = $response->json()['query']['pages'] ?? [];

        foreach ($pages as $page) {
            $title = $page['title'] ?? '';
            $extract = $page['extract'] ?? '';

            if (!$title || str_starts_with($title, 'Category:')) continue;

            // Skip disambiguation pages and lists
            if (str_contains(strtolower($extract), 'may refer to') ||
                str_contains(strtolower($title), 'list of')) continue;

            $description = $extract ? substr(trim($extract), 0, 500) : null;

            $this->upsertCompany([
                'name' => $title,
                'status' => $status,
                'description' => $description,
                'country' => 'US',
            ]);
        }
    }
}
