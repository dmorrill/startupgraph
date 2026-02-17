<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EdgarBulkImporter extends BaseBulkImporter
{
    private const EFTS_URL = 'https://efts.sec.gov/LATEST/search-index';
    private const FULL_TEXT_URL = 'https://efts.sec.gov/LATEST/search';
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
            ])->timeout(30)->get(self::FULL_TEXT_URL, [
                'q' => '"form D" OR "form D/A"',
                'dateRange' => 'custom',
                'startdt' => now()->subYear()->format('Y-m-d'),
                'enddt' => now()->format('Y-m-d'),
                'forms' => 'D,D/A',
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
                'metadata' => ['total_available' => $total],
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

        $entityName = $source['entity_name'] ?? ($source['display_names'][0] ?? null);
        if (!$entityName) return;

        // Skip clearly non-startup entities (funds, trusts, etc.)
        $skipPatterns = ['/\bfund\b/i', '/\btrust\b/i', '/\bLP$/i', '/\bLLC$/i', '/\bpartners?\b/i'];
        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $entityName)) {
                $this->skipped++;
                $this->processed++;
                return;
            }
        }

        $state = $source['state_of_incorp'] ?? ($source['entity_state'] ?? null);
        $filedAt = $source['file_date'] ?? ($source['date_filed'] ?? null);

        $this->upsertCompany([
            'name' => $this->cleanEntityName($entityName),
            'state' => $state,
            'country' => 'US',
            'founded_date' => $filedAt ? substr($filedAt, 0, 10) : null,
        ]);
    }

    private function cleanEntityName(string $name): string
    {
        // Remove common suffixes like ", Inc.", ", Corp.", etc.
        $name = preg_replace('/,?\s*(Inc\.?|Corp\.?|Co\.?|Ltd\.?)$/i', '', $name);
        return trim($name);
    }
}
