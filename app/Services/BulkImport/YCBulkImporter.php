<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YCBulkImporter extends BaseBulkImporter
{
    private const BROWSE_URL = 'https://45BWZJ1SGC-dsn.algolia.net/1/indexes/YCCompany_production/browse';

    private const QUERY_URL = 'https://45BWZJ1SGC-dsn.algolia.net/1/indexes/YCCompany_production/query';

    private const APP_ID = '45BWZJ1SGC';

    private const API_KEY = 'ZjA3NWMwMmNhMzEwZmMxOThkZDlkMjFmNDAwNTNjNjdkZjdhNWJkOWRjMThiODQwMjUyZTVkYjA4YjFlMmU2YnJlc3RyaWN0SW5kaWNlcz0lNUIlMjJZQ0NvbXBhbnlfcHJvZHVjdGlvbiUyMiUyQyUyMllDQ29tcGFueV9CeV9MYXVuY2hfRGF0ZV9wcm9kdWN0aW9uJTIyJTVEJnRhZ0ZpbHRlcnM9JTVCJTIyeWNkY19wdWJsaWMlMjIlNUQmYW5hbHl0aWNzVGFncz0lNUIlMjJ5Y2RjJTIyJTVE';

    private const HITS_PER_PAGE = 1000;

    public function source(): string
    {
        return 'yc';
    }

    public function import(array $options = []): void
    {
        Log::info('YC bulk import starting — fetching batches');

        // First, get all batch facets
        $batches = $this->fetchBatches();
        if (empty($batches)) {
            Log::warning('YC: no batches found');

            return;
        }

        $resumeFrom = $options['resume_cursor'] ?? null; // batch name to resume from
        $started = $resumeFrom === null;
        $batchNum = 0;

        foreach ($batches as $batch => $count) {
            $batchNum++;
            if (! $started) {
                if ($batch === $resumeFrom) {
                    $started = true;
                } else {
                    continue;
                }
            }

            Log::info("YC: importing batch '{$batch}' ({$count} companies, batch {$batchNum}/".count($batches).')');

            $page = 0;
            while (true) {
                $response = Http::withHeaders([
                    'X-Algolia-Application-Id' => self::APP_ID,
                    'X-Algolia-API-Key' => self::API_KEY,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post(self::QUERY_URL, [
                    'query' => '',
                    'hitsPerPage' => self::HITS_PER_PAGE,
                    'page' => $page,
                    'facetFilters' => [["batch:{$batch}"]],
                ]);

                if (! $response->successful()) {
                    Log::warning("YC API returned HTTP {$response->status()} for batch '{$batch}' page {$page}");
                    break;
                }

                $data = $response->json();
                $hits = $data['hits'] ?? [];

                if (empty($hits)) {
                    break;
                }

                foreach ($hits as $hit) {
                    $this->importHit($hit);
                }

                $totalPages = $data['nbPages'] ?? 0;
                $page++;

                if ($page >= $totalPages) {
                    break;
                }

                $this->rateLimitSleep(0.3);
            }

            $this->importLog->update([
                'last_offset' => $batch,
                'last_page' => $batchNum,
                'total_processed' => $this->processed,
                'companies_created' => $this->created,
            ]);

            $this->rateLimitSleep(0.5);
        }

        Log::info("YC bulk import complete: {$this->created} created, {$this->updated} updated, {$this->skipped} skipped");
    }

    private function fetchBatches(): array
    {
        $response = Http::withHeaders([
            'X-Algolia-Application-Id' => self::APP_ID,
            'X-Algolia-API-Key' => self::API_KEY,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post(self::QUERY_URL, [
            'query' => '',
            'hitsPerPage' => 0,
            'facets' => ['batch'],
        ]);

        if (! $response->successful()) {
            return [];
        }

        return $response->json('facets.batch') ?? [];
    }

    private function importHit(array $hit): void
    {
        $name = $hit['name'] ?? null;
        if (! $name) {
            return;
        }

        $status = 'operating';
        if (isset($hit['status'])) {
            $status = $this->mapStatus($hit['status']);
        }

        $location = $hit['location'] ?? '';
        $locationParts = array_map('trim', explode(',', $location));

        $this->upsertCompany([
            'name' => $name,
            'slug' => $hit['slug'] ?? null,
            'description' => $hit['one_liner'] ?? ($hit['long_description'] ?? null),
            'website' => $hit['website'] ?? null,
            'city' => $locationParts[0] ?? null,
            'state' => $locationParts[1] ?? null,
            'country' => $locationParts[2] ?? ($locationParts[1] ?? null),
            'status' => $status,
            'current_headcount' => $hit['team_size'] ?? null,
            'category' => $this->mapYCIndustry($hit['industries'] ?? []),
            'founded_date' => isset($hit['batch']) ? $this->batchToDate($hit['batch']) : null,
        ]);
    }

    private function mapYCIndustry(array $industries): ?string
    {
        $map = [
            'Artificial Intelligence' => 'ai_ml',
            'Machine Learning' => 'ai_ml',
            'Fintech' => 'fintech',
            'Healthcare' => 'healthcare',
            'Developer Tools' => 'developer_tools',
            'Climate' => 'climate',
            'Consumer' => 'consumer',
            'Enterprise' => 'enterprise',
            'Robotics' => 'robotics',
            'Defense' => 'defense',
        ];

        foreach ($industries as $industry) {
            if (isset($map[$industry])) {
                return $map[$industry];
            }
        }

        return null;
    }

    private function batchToDate(string $batch): ?string
    {
        // e.g., "W2024" -> 2024-01-01, "S2024" -> 2024-06-01
        if (preg_match('/^([WS])(\d{4})$/', $batch, $m)) {
            $month = $m[1] === 'W' ? '01' : '06';

            return "{$m[2]}-{$month}-01";
        }

        return null;
    }
}
