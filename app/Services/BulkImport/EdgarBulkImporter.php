<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EdgarBulkImporter extends BaseBulkImporter
{
    private const SEARCH_INDEX_URL = 'https://efts.sec.gov/LATEST/search-index';
    private const PER_PAGE = 100;

    public function source(): string
    {
        return 'edgar';
    }

    public function import(array $options = []): void
    {
        $startFrom = $options['resume_from'] ?? 0;
        $maxResults = $options['max_results'] ?? 10000;

        Log::info("SEC EDGAR Form D import starting from offset {$startFrom}");

        $offset = $startFrom;

        while ($offset < $maxResults) {
            $response = Http::withHeaders([
                'User-Agent' => 'StartupGraph research@startupgraph.com',
                'Accept' => 'application/json',
            ])->timeout(30)->get(self::SEARCH_INDEX_URL, [
                'q' => '"form D"',
                'forms' => 'D',
                'dateRange' => 'custom',
                'startdt' => now()->subYears(2)->format('Y-m-d'),
                'enddt' => now()->format('Y-m-d'),
                'from' => $offset,
                'size' => self::PER_PAGE,
            ]);

            if (!$response->successful()) {
                Log::warning("EDGAR API returned HTTP {$response->status()} at offset {$offset}");
                break;
            }

            $data = $response->json();
            $hits = $data['hits']['hits'] ?? [];

            if (empty($hits)) break;

            foreach ($hits as $hit) {
                $this->importFiling($hit);
            }

            $total = $data['hits']['total']['value'] ?? 0;

            $this->importLog->update([
                'last_offset' => (string) $offset,
                'total_processed' => $this->processed,
                'companies_created' => $this->created,
            ]);

            $offset += self::PER_PAGE;

            Log::info("EDGAR: offset {$offset}/{$total}, processed: {$this->processed}, created: {$this->created}");

            if ($offset >= $total) break;

            $this->rateLimitSleep(1.0); // SEC requires polite crawling
        }

        Log::info("EDGAR import complete: {$this->created} created, {$this->updated} updated");
    }

    private function importFiling(array $hit): void
    {
        $source = $hit['_source'] ?? [];

        $displayNames = $source['display_names'] ?? [];
        if (empty($displayNames)) return;

        // Each filing can have multiple entities; import each
        $bizLocations = $source['biz_locations'] ?? [];
        $bizStates = $source['biz_states'] ?? [];
        $incStates = $source['inc_states'] ?? [];
        $filedAt = $source['file_date'] ?? null;
        $sics = $source['sics'] ?? [];

        foreach ($displayNames as $i => $displayName) {
            // Extract entity name from format: "Company Name  (CIK 0001234567)"
            $entityName = preg_replace('/\s*\((?:CIK\s+)?\d+\)\s*$/', '', $displayName);
            // Also strip ticker symbols: "Company Name  (TICKER)  (CIK ...)" already handled above
            $entityName = preg_replace('/\s*\([A-Z]+\)\s*/', ' ', $entityName);
            $entityName = trim($entityName);

            if (!$entityName) continue;

            // Skip clearly non-startup entities (funds, trusts, LPs, etc.)
            $skipPatterns = [
                '/\bfund\b/i', '/\btrust\b/i', '/\bLP$/i',
                '/\bpartners?\b/i', '/\bREIT\b/i', '/\bportfolio\b/i',
                '/\bholding/i', '/\badvisers?\b/i', '/\badvisors?\b/i',
                '/\bcapital\b/i', '/\binvestment/i', '/\bventure/i',
                '/\bhedge\b/i', '/\boffshore\b/i', '/\bonshore\b/i',
                '/\bL\.?L\.?C\.?\s*$/i',
            ];

            $skip = false;
            foreach ($skipPatterns as $pattern) {
                if (preg_match($pattern, $entityName)) {
                    $skip = true;
                    break;
                }
            }

            if ($skip) {
                $this->skipped++;
                $this->processed++;
                continue;
            }

            $state = $bizStates[$i] ?? ($bizStates[0] ?? null);
            $location = $bizLocations[$i] ?? ($bizLocations[0] ?? null);
            $city = null;
            if ($location) {
                // Parse "City, ST" format
                $parts = explode(',', $location);
                $city = trim($parts[0] ?? '');
            }

            $this->upsertCompany([
                'name' => $this->cleanEntityName($entityName),
                'state' => $state,
                'city' => $city,
                'country' => 'US',
                'founded_date' => $filedAt ? substr($filedAt, 0, 10) : null,
            ]);
        }
    }

    private function cleanEntityName(string $name): string
    {
        // Remove common suffixes like ", Inc.", ", Corp.", etc.
        $name = preg_replace('/,?\s*(Inc\.?|Corp\.?|Co\.?|Ltd\.?)$/i', '', $name);
        return trim($name);
    }
}
