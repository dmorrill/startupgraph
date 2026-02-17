<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WikipediaUnicornImporter extends BaseBulkImporter
{
    private const WIKIPEDIA_API = 'https://en.wikipedia.org/w/api.php';

    private const PAGES = [
        'List_of_unicorn_startup_companies',
    ];

    public function source(): string
    {
        return 'wikipedia';
    }

    public function import(array $options = []): void
    {
        Log::info("Wikipedia unicorn import starting");

        foreach (self::PAGES as $page) {
            $this->importFromPage($page);
        }

        Log::info("Wikipedia unicorn import complete", $this->getStats());
    }

    private function importFromPage(string $pageTitle): void
    {
        // Use Wikipedia API to get page content in parsed HTML
        $response = Http::timeout(30)->withUserAgent('StartupGraph/1.0 (https://startupgraph.com)')->get(self::WIKIPEDIA_API, [
            'action' => 'parse',
            'page' => $pageTitle,
            'format' => 'json',
            'prop' => 'wikitext',
        ]);

        if (!$response->successful()) {
            Log::warning("Wikipedia: Failed to fetch {$pageTitle}");
            return;
        }

        $data = $response->json();
        $wikitext = $data['parse']['wikitext']['*'] ?? '';

        if (empty($wikitext)) {
            Log::warning("Wikipedia: Empty wikitext for {$pageTitle}");
            return;
        }

        // Parse wikitable rows - unicorn tables typically have: Company, Valuation, Date, Country, Industry
        $this->parseWikiTables($wikitext);
    }

    private function parseWikiTables(string $wikitext): void
    {
        // Split by row separators |-
        $rows = preg_split('/\n\|-/', $wikitext);

        // Find the header row containing !Company and !Industry
        $headerIdx = null;
        $columns = [];
        foreach ($rows as $i => $row) {
            // Headers use ! prefix on each line
            if (str_contains($row, '!Company') && str_contains($row, '!Industry')) {
                // Parse header cells: each starts with ! on its own line
                preg_match_all('/\n!\s*(.+)/', "\n" . $row, $headerMatches);
                $columns = array_map(fn($h) => strtolower($this->cleanWikiText($h)), $headerMatches[1] ?? []);
                $headerIdx = $i;
                Log::info("Wikipedia: Found header at row {$i} with columns: " . implode(', ', $columns));
                break;
            }
        }

        if ($headerIdx === null || empty($columns)) {
            Log::warning("Wikipedia: Could not find company table headers");
            return;
        }

        // Column indices (0-based)
        $nameCol = $this->findColumn($columns, ['company', 'name']);
        $countryCol = $this->findColumn($columns, ['country']);
        $industryCol = $this->findColumn($columns, ['industry', 'sector']);

        if ($nameCol === null) {
            Log::warning("Wikipedia: No company column found in: " . implode(', ', $columns));
            return;
        }

        // Process data rows after header
        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Stop if we hit end of table or new section
            if (str_contains($row, '|}')) break;

            // Each data cell starts with | on its own line
            // Split by \n| but not \n|- or \n|} or \n||
            $cells = [];
            $lines = explode("\n", $row);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line === '|-' || $line === '|}') continue;
                if (str_starts_with($line, '|')) {
                    $cells[] = substr($line, 1);
                }
            }

            if (count($cells) < 3) continue;

            $name = $this->cleanWikiText($cells[$nameCol] ?? '');
            if (!$name || strlen($name) < 2 || is_numeric($name)) continue;

            // Skip non-company entries
            if (str_contains(strtolower($name), 'total') || str_contains($name, '=')) continue;

            $country = $countryCol !== null ? $this->cleanWikiText($cells[$countryCol] ?? '') : null;
            // Clean flag template from country: {{flag|United States}} -> United States
            if ($country) {
                $country = preg_replace('/flag\|/i', '', $country);
            }

            $industry = $industryCol !== null ? $this->cleanWikiText($cells[$industryCol] ?? '') : null;

            $countryCode = $this->mapCountryName($country);
            $category = $this->mapIndustry($industry);

            $this->upsertCompany([
                'name' => trim($name),
                'country' => $countryCode,
                'category' => $category,
                'status' => 'operating',
            ]);
        }
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
        // Remove wiki links: [[Target|Display]] -> Display, [[Target]] -> Target
        $text = preg_replace('/\[\[(?:[^|\]]*\|)?([^\]]+)\]\]/', '$1', $text);
        // Remove templates {{ }}
        $text = preg_replace('/\{\{[^}]*\}\}/', '', $text);
        // Remove HTML tags
        $text = strip_tags($text);
        // Remove refs
        $text = preg_replace('/<ref[^>]*>.*?<\/ref>/s', '', $text);
        $text = preg_replace('/<ref[^>]*\/?>/s', '', $text);
        // Clean whitespace
        $text = trim(preg_replace('/\s+/', ' ', $text));

        return $text;
    }

    private function mapCountryName(?string $country): ?string
    {
        if (!$country) return null;
        $country = strtolower(trim($country));

        $map = [
            'united states' => 'US', 'usa' => 'US', 'u.s.' => 'US', 'us' => 'US',
            'united kingdom' => 'GB', 'uk' => 'GB',
            'china' => 'CN', 'india' => 'IN', 'germany' => 'DE', 'france' => 'FR',
            'canada' => 'CA', 'israel' => 'IL', 'brazil' => 'BR', 'australia' => 'AU',
            'japan' => 'JP', 'south korea' => 'KR', 'singapore' => 'SG',
            'sweden' => 'SE', 'netherlands' => 'NL', 'indonesia' => 'ID',
            'ireland' => 'IE', 'switzerland' => 'CH', 'hong kong' => 'HK',
        ];

        return $map[$country] ?? strtoupper(substr($country, 0, 2));
    }

    private function mapIndustry(?string $industry): ?string
    {
        if (!$industry) return null;
        $industry = strtolower($industry);

        $map = [
            'artificial intelligence' => 'ai_ml',
            'fintech' => 'fintech', 'financial' => 'fintech',
            'health' => 'healthcare', 'biotech' => 'healthcare',
            'e-commerce' => 'consumer', 'consumer' => 'consumer',
            'enterprise' => 'enterprise', 'saas' => 'enterprise', 'software' => 'enterprise',
            'cybersecurity' => 'enterprise', 'cyber security' => 'enterprise',
            'robotics' => 'robotics', 'hardware' => 'robotics',
            'clean' => 'climate', 'energy' => 'climate',
            'defense' => 'defense', 'aerospace' => 'defense',
        ];

        foreach ($map as $keyword => $category) {
            if (str_contains($industry, $keyword)) {
                return $category;
            }
        }

        return null;
    }
}
