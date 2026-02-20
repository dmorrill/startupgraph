<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WikipediaAcquisitionsImporter extends BaseBulkImporter
{
    private const WIKIPEDIA_API = 'https://en.wikipedia.org/w/api.php';

    private const ACQUIRER_PAGES = [
        'Alphabet' => 'List_of_mergers_and_acquisitions_by_Alphabet',
        'Apple' => 'List_of_mergers_and_acquisitions_by_Apple',
        'Meta Platforms' => 'List_of_mergers_and_acquisitions_by_Meta_Platforms',
        'Microsoft' => 'List_of_mergers_and_acquisitions_by_Microsoft',
        'Amazon' => 'List_of_mergers_and_acquisitions_by_Amazon',
        'Salesforce' => 'List_of_mergers_and_acquisitions_by_Salesforce',
        'Oracle' => 'List_of_mergers_and_acquisitions_by_Oracle',
        'Cisco Systems' => 'List_of_mergers_and_acquisitions_by_Cisco_Systems',
        'IBM' => 'List_of_mergers_and_acquisitions_by_IBM',
        'Intel' => 'List_of_mergers_and_acquisitions_by_Intel',
        'Yahoo!' => 'List_of_mergers_and_acquisitions_by_Yahoo!',
        'Twitter' => 'List_of_mergers_and_acquisitions_by_Twitter',
    ];

    public function source(): string
    {
        return 'wikipedia-acquisitions';
    }

    public function import(array $options = []): void
    {
        Log::info('Wikipedia acquisitions import starting');

        foreach (self::ACQUIRER_PAGES as $acquirer => $page) {
            Log::info("Importing acquisitions by {$acquirer}");
            $this->importFromPage($acquirer, $page);
            $this->rateLimitSleep(1.0);
        }

        Log::info('Wikipedia acquisitions import complete', $this->getStats());
    }

    private function importFromPage(string $acquirer, string $pageTitle): void
    {
        $response = Http::timeout(30)
            ->withUserAgent('StartupGraph/1.0 (https://startupgraph.com)')
            ->get(self::WIKIPEDIA_API, [
                'action' => 'parse',
                'page' => $pageTitle,
                'format' => 'json',
                'prop' => 'wikitext',
            ]);

        if (! $response->successful()) {
            Log::warning("Wikipedia: Failed to fetch {$pageTitle}");

            return;
        }

        $data = $response->json();
        $wikitext = $data['parse']['wikitext']['*'] ?? '';

        if (empty($wikitext)) {
            Log::warning("Wikipedia: Empty wikitext for {$pageTitle}");

            return;
        }

        $this->parseAcquisitionTables($wikitext, $acquirer);
    }

    private function parseAcquisitionTables(string $wikitext, string $acquirer): void
    {
        // Split into table sections
        $rows = preg_split('/\n\|-/', $wikitext);

        $columns = [];
        $headerFound = false;

        foreach ($rows as $i => $row) {
            // Look for header rows with ! markers
            if (! $headerFound && preg_match_all('/\n!\s*(.+)/', "\n".$row, $headerMatches)) {
                $headers = $headerMatches[1] ?? [];
                if (count($headers) >= 2) {
                    $columns = array_map(fn ($h) => strtolower($this->cleanWikiText($h)), $headers);
                    // Check if this looks like an acquisitions table
                    $hasCompany = $this->findColumn($columns, ['company', 'acquisition', 'name', 'target']);
                    if ($hasCompany !== null) {
                        $headerFound = true;

                        continue;
                    }
                }
            }

            if (! $headerFound) {
                continue;
            }

            // End of table
            if (str_contains($row, '|}')) {
                $headerFound = false;

                continue;
            }

            // Parse cells
            $cells = $this->extractCells($row);
            if (count($cells) < 2) {
                continue;
            }

            $nameCol = $this->findColumn($columns, ['company', 'acquisition', 'name', 'target']);
            $dateCol = $this->findColumn($columns, ['date', 'announced', 'completed', 'year']);
            $priceCol = $this->findColumn($columns, ['price', 'value', 'amount', 'cost']);
            $descCol = $this->findColumn($columns, ['description', 'business', 'area', 'notes', 'used for', 'products/services']);

            $name = $this->cleanWikiText($cells[$nameCol] ?? '');
            if (! $name || strlen($name) < 2 || is_numeric($name)) {
                continue;
            }
            if (str_contains(strtolower($name), 'total') || str_contains($name, '=')) {
                continue;
            }

            $date = $dateCol !== null ? $this->cleanWikiText($cells[$dateCol] ?? '') : null;
            $description = $descCol !== null ? $this->cleanWikiText($cells[$descCol] ?? '') : null;
            $price = $priceCol !== null ? $this->cleanWikiText($cells[$priceCol] ?? '') : null;

            // Build description with price info
            $fullDesc = $description;
            if ($price && $price !== '' && strtolower($price) !== 'n/a' && strtolower($price) !== 'undisclosed') {
                $fullDesc = ($fullDesc ? $fullDesc.'. ' : '')."Acquired for {$price}.";
            }

            // Parse founded/acquisition date
            $foundedDate = null;
            if ($date) {
                // Try to extract a year
                if (preg_match('/(\d{4})/', $date, $m)) {
                    $foundedDate = $m[1].'-01-01';
                }
            }

            $this->upsertCompany([
                'name' => $name,
                'status' => 'acquired',
                'acquired_by' => $acquirer,
                'description' => $fullDesc ? substr($fullDesc, 0, 500) : null,
            ]);
        }
    }

    private function extractCells(string $row): array
    {
        $cells = [];
        $lines = explode("\n", $row);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line === '|-' || $line === '|}') {
                continue;
            }
            if (str_starts_with($line, '|')) {
                // Handle multiple cells on one line separated by ||
                $parts = explode('||', substr($line, 1));
                foreach ($parts as $part) {
                    $cells[] = trim($part);
                }
            }
        }

        return $cells;
    }

    private function findColumn(array $headers, array $keywords): ?int
    {
        foreach ($headers as $i => $header) {
            foreach ($keywords as $kw) {
                if (str_contains($header, $kw)) {
                    return $i;
                }
            }
        }

        return null;
    }

    private function cleanWikiText(string $text): string
    {
        $text = preg_replace('/\[\[(?:[^|\]]*\|)?([^\]]+)\]\]/', '$1', $text);
        $text = preg_replace('/\{\{[^}]*\}\}/', '', $text);
        $text = strip_tags($text);
        $text = preg_replace('/<ref[^>]*>.*?<\/ref>/s', '', $text);
        $text = preg_replace('/<ref[^>]*\/?>/s', '', $text);
        $text = trim(preg_replace('/\s+/', ' ', $text));

        return $text;
    }
}
