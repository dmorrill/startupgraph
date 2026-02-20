<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WikipediaCategoryImporter extends BaseBulkImporter
{
    private const WIKIPEDIA_API = 'https://en.wikipedia.org/w/api.php';

    private const CATEGORIES = [
        // Technology companies by year
        'Category:Technology_companies_established_in_2020' => 'operating',
        'Category:Technology_companies_established_in_2021' => 'operating',
        'Category:Technology_companies_established_in_2022' => 'operating',
        'Category:Technology_companies_established_in_2023' => 'operating',
        'Category:Technology_companies_established_in_2024' => 'operating',
        'Category:Technology_companies_established_in_2025' => 'operating',
        // American companies by year
        'Category:American_companies_established_in_2020' => 'operating',
        'Category:American_companies_established_in_2021' => 'operating',
        'Category:American_companies_established_in_2022' => 'operating',
        'Category:American_companies_established_in_2023' => 'operating',
        'Category:American_companies_established_in_2024' => 'operating',
        'Category:American_companies_established_in_2025' => 'operating',
    ];

    public function source(): string
    {
        return 'wikipedia-categories';
    }

    public function import(array $options = []): void
    {
        Log::info('Wikipedia category companies import starting');

        foreach (self::CATEGORIES as $category => $status) {
            Log::info("Importing from {$category}");
            $this->importCategory($category, $status);
        }

        Log::info('Wikipedia category companies import complete', $this->getStats());
    }

    private function importCategory(string $category, string $status): void
    {
        $continue = null;

        // Extract year from category name for founded_date
        $year = null;
        if (preg_match('/(\d{4})/', $category, $m)) {
            $year = $m[1];
        }

        // Determine country from category
        $country = str_contains($category, 'American') ? 'US' : null;

        do {
            $params = [
                'action' => 'query',
                'list' => 'categorymembers',
                'cmtitle' => $category,
                'cmlimit' => 500,
                'cmtype' => 'page',
                'cmnamespace' => 0,
                'format' => 'json',
            ];

            if ($continue) {
                $params['cmcontinue'] = $continue;
            }

            $response = Http::timeout(30)
                ->withUserAgent('StartupGraph/1.0 (https://startupgraph.com)')
                ->get(self::WIKIPEDIA_API, $params);

            if (! $response->successful()) {
                Log::warning("Wikipedia: Failed to fetch category {$category}");
                break;
            }

            $data = $response->json();
            $members = $data['query']['categorymembers'] ?? [];
            $continue = $data['continue']['cmcontinue'] ?? null;

            // Batch fetch extracts
            $titles = array_map(fn ($m) => $m['title'], $members);
            $chunks = array_chunk($titles, 50);

            foreach ($chunks as $chunk) {
                $this->fetchAndImportExtracts($chunk, $status, $year, $country);
                $this->rateLimitSleep(1.0);
            }

        } while ($continue);
    }

    private function fetchAndImportExtracts(array $titles, string $status, ?string $year, ?string $country): void
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

        if (! $response->successful()) {
            foreach ($titles as $title) {
                $this->upsertCompany([
                    'name' => $title,
                    'status' => $status,
                    'founded_date' => $year ? "{$year}-01-01" : null,
                    'country' => $country,
                ]);
            }

            return;
        }

        $pages = $response->json()['query']['pages'] ?? [];

        foreach ($pages as $page) {
            $title = $page['title'] ?? '';
            $extract = $page['extract'] ?? '';

            if (! $title || str_starts_with($title, 'Category:')) {
                continue;
            }

            // Skip disambiguation and list pages
            if (str_contains(strtolower($extract), 'may refer to') ||
                str_contains(strtolower($title), 'list of')) {
                continue;
            }

            $description = $extract ? substr(trim($extract), 0, 500) : null;

            $this->upsertCompany([
                'name' => $title,
                'status' => $status,
                'description' => $description,
                'founded_date' => $year ? "{$year}-01-01" : null,
                'country' => $country,
            ]);
        }
    }
}
