<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Import companies from Wikipedia category pages via the MediaWiki API.
 *
 * Targets 3000+ companies across multiple categories:
 * - Technology companies established in 2020–2025
 * - Y Combinator companies
 * - Companies listed on NASDAQ
 * - Software companies of the United States
 * - Indian IT companies
 * - Unicorn companies
 *
 * Run: php artisan db:seed --class=WikipediaCompaniesSeeder
 */
class WikipediaCompaniesSeeder extends Seeder
{
    private const API_BASE = 'https://en.wikipedia.org/w/api.php';
    private const USER_AGENT = 'StartupGraph/1.0 (https://startupgraph.com; bot@startupgraph.com)';
    private const RATE_LIMIT_US = 1000000; // 1 second in microseconds

    /** Categories to skip entirely (defunct, dissolved, etc.) */
    private const SKIP_CATEGORIES = [
        'Defunct',
        'defunct',
        'Dissolved',
        'dissolved',
        'Former',
        'former',
    ];

    /** Categories to crawl. Some have subcategories we'll recurse into (depth 1). */
    private const CATEGORIES = [
        // Tech companies by founding year
        ['cat' => 'Technology_companies_established_in_2020', 'depth' => 1],
        ['cat' => 'Technology_companies_established_in_2021', 'depth' => 1],
        ['cat' => 'Technology_companies_established_in_2022', 'depth' => 1],
        ['cat' => 'Technology_companies_established_in_2023', 'depth' => 1],
        ['cat' => 'Technology_companies_established_in_2024', 'depth' => 1],
        ['cat' => 'Technology_companies_established_in_2025', 'depth' => 1],

        // Major lists
        ['cat' => 'Y_Combinator_companies', 'depth' => 1],
        ['cat' => 'Companies_listed_on_the_Nasdaq', 'depth' => 0],
        ['cat' => 'Software_companies_of_the_United_States', 'depth' => 1],
        ['cat' => 'Information_technology_companies_of_India', 'depth' => 1],

        // Unicorns
        ['cat' => 'Unicorn_(finance)_companies', 'depth' => 0],
        ['cat' => 'Companies_with_a_unicorn_valuation', 'depth' => 0],

        // Additional tech categories for volume
        ['cat' => 'American_technology_companies', 'depth' => 0],
        ['cat' => 'Technology_companies_based_in_the_San_Francisco_Bay_Area', 'depth' => 0],
        ['cat' => 'Cloud_computing_providers', 'depth' => 0],
        ['cat' => 'Software_as_a_service', 'depth' => 0],
        ['cat' => 'Financial_technology_companies', 'depth' => 0],
        ['cat' => 'Cryptocurrency_companies', 'depth' => 0],
        ['cat' => 'Biotechnology_companies_of_the_United_States', 'depth' => 0],
        ['cat' => 'Electric_vehicle_manufacturers', 'depth' => 0],
        ['cat' => 'Robotics_companies', 'depth' => 0],
        ['cat' => 'Cybersecurity_companies', 'depth' => 0],
        ['cat' => 'Internet_of_things_companies', 'depth' => 0],
        ['cat' => 'Artificial_intelligence_companies', 'depth' => 0],
        ['cat' => 'Technology_companies_of_the_United_Kingdom', 'depth' => 0],
        ['cat' => 'Technology_companies_of_Germany', 'depth' => 0],
        ['cat' => 'Technology_companies_of_France', 'depth' => 0],
        ['cat' => 'Technology_companies_of_Israel', 'depth' => 0],
        ['cat' => 'Technology_companies_based_in_New_York_City', 'depth' => 0],
        ['cat' => 'Companies_listed_on_the_New_York_Stock_Exchange', 'depth' => 0],
        ['cat' => 'Venture_capital-funded_companies', 'depth' => 0],
    ];

    private int $created = 0;
    private int $skipped = 0;
    private int $apiCalls = 0;

    /** Track titles we've already processed to avoid redundant API calls */
    private array $processedTitles = [];

    public function run(): void
    {
        $this->command->info('Starting Wikipedia companies import...');
        $this->command->info('Categories to crawl: ' . count(self::CATEGORIES));

        // Collect all article titles first, then batch-fetch details
        $allTitles = [];

        foreach (self::CATEGORIES as $catConfig) {
            $cat = $catConfig['cat'];
            $depth = $catConfig['depth'];

            $this->command->info("\n📂 Category: {$cat}");
            $titles = $this->getCategoryMembers("Category:{$cat}", $depth);
            $count = count($titles);
            $this->command->info("   Found {$count} articles");
            $allTitles = array_merge($allTitles, $titles);
        }

        // Deduplicate titles
        $allTitles = array_unique($allTitles);
        $totalTitles = count($allTitles);
        $this->command->info("\n📊 Total unique articles to process: {$totalTitles}");

        // Process in batches of 20 (Wikipedia API limit for prop queries)
        $batches = array_chunk($allTitles, 20);
        $processed = 0;

        foreach ($batches as $batch) {
            $this->processBatch($batch);
            $processed += count($batch);

            if ($processed % 200 === 0) {
                $this->command->info("   Progress: {$processed}/{$totalTitles} — Created: {$this->created}, Skipped: {$this->skipped}");
            }
        }

        $this->command->info("\n✅ Done!");
        $this->command->info("   Created: {$this->created}");
        $this->command->info("   Skipped: {$this->skipped}");
        $this->command->info("   API calls: {$this->apiCalls}");
    }

    /**
     * Get all article titles from a category, optionally recursing into subcategories.
     */
    private function getCategoryMembers(string $category, int $depth = 0): array
    {
        $titles = [];
        $cmcontinue = null;

        // Skip defunct categories
        foreach (self::SKIP_CATEGORIES as $skip) {
            if (str_contains($category, $skip)) {
                return [];
            }
        }

        do {
            $params = [
                'action' => 'query',
                'list' => 'categorymembers',
                'cmtitle' => $category,
                'cmlimit' => '500',
                'cmtype' => 'page|subcat',
                'format' => 'json',
            ];
            if ($cmcontinue) {
                $params['cmcontinue'] = $cmcontinue;
            }

            $data = $this->apiRequest($params);
            if (!$data) break;

            $members = $data['query']['categorymembers'] ?? [];
            foreach ($members as $member) {
                $ns = $member['ns'] ?? 0;
                $title = $member['title'] ?? '';

                if ($ns === 0) {
                    // Article — filter out non-company pages
                    if (!$this->looksLikeListPage($title)) {
                        $titles[] = $title;
                    }
                } elseif ($ns === 14 && $depth > 0) {
                    // Subcategory — recurse if not defunct
                    $skipThis = false;
                    foreach (self::SKIP_CATEGORIES as $skip) {
                        if (str_contains($title, $skip)) {
                            $skipThis = true;
                            break;
                        }
                    }
                    if (!$skipThis) {
                        $subTitles = $this->getCategoryMembers($title, $depth - 1);
                        $titles = array_merge($titles, $subTitles);
                    }
                }
            }

            $cmcontinue = $data['continue']['cmcontinue'] ?? null;
        } while ($cmcontinue);

        return $titles;
    }

    /**
     * Filter out list/index pages that aren't individual companies.
     */
    private function looksLikeListPage(string $title): bool
    {
        $lower = strtolower($title);
        return str_starts_with($lower, 'list of')
            || str_starts_with($lower, 'lists of')
            || str_starts_with($lower, 'comparison of')
            || str_starts_with($lower, 'history of')
            || str_starts_with($lower, 'outline of')
            || str_starts_with($lower, 'index of')
            || str_starts_with($lower, 'timeline of')
            || str_contains($lower, '(disambiguation)');
    }

    /**
     * Process a batch of up to 20 article titles.
     */
    private function processBatch(array $titles): void
    {
        // Filter already-processed titles
        $titles = array_filter($titles, fn($t) => !isset($this->processedTitles[$t]));
        if (empty($titles)) return;

        foreach ($titles as $t) {
            $this->processedTitles[$t] = true;
        }

        // Fetch extracts (intro text)
        $params = [
            'action' => 'query',
            'titles' => implode('|', $titles),
            'prop' => 'extracts|revisions',
            'exintro' => '1',
            'explaintext' => '1',
            'exlimit' => count($titles),
            'rvprop' => 'content',
            'rvslots' => 'main',
            'rvsection' => '0',
            'format' => 'json',
        ];

        $data = $this->apiRequest($params);
        if (!$data) return;

        $pages = $data['query']['pages'] ?? [];

        foreach ($pages as $pageId => $page) {
            if ($pageId < 0) continue; // Missing page

            $title = $page['title'] ?? '';
            $extract = $page['extract'] ?? '';
            $wikitext = $page['revisions'][0]['slots']['main']['*'] ?? '';

            // Skip if extract suggests it's not a company
            if ($this->isNotCompany($extract, $title)) {
                $this->skipped++;
                continue;
            }

            $this->createCompany($title, $extract, $wikitext);
        }
    }

    /**
     * Heuristic: does this look like a company article?
     */
    private function isNotCompany(string $extract, string $title): bool
    {
        if (empty($extract) || strlen($extract) < 50) return true;

        $lower = strtolower($extract);
        // Skip people, places, concepts, etc.
        $nonCompanyIndicators = [
            ' is an american politician',
            ' is a politician',
            ' was a politician',
            ' is a city ',
            ' is a village ',
            ' is a town ',
            ' is a municipality',
            ' is a county',
            ' is a river',
            ' is a mountain',
            ' is a genus',
            ' is a species',
            ' is a programming language',
            ' is a television series',
            ' is a film',
            ' is a song',
            ' is a novel',
            ' is a book',
            ' born ',
        ];

        foreach ($nonCompanyIndicators as $indicator) {
            if (str_contains($lower, $indicator)) return true;
        }

        return false;
    }

    /**
     * Parse infobox data from wikitext and create a Company record.
     */
    private function createCompany(string $title, string $extract, string $wikitext): void
    {
        // Clean the company name (remove disambiguation)
        $name = preg_replace('/\s*\(company\)$/i', '', $title);
        $name = preg_replace('/\s*\(software\)$/i', '', $name);
        $name = preg_replace('/\s*\(website\)$/i', '', $name);
        $name = preg_replace('/\s*\(service\)$/i', '', $name);
        $name = preg_replace('/\s*\(platform\)$/i', '', $name);
        $name = preg_replace('/\s*\(app\)$/i', '', $name);

        $slug = Str::slug($name);

        // Check if already exists
        if (Company::where('slug', $slug)->exists()) {
            $this->skipped++;
            return;
        }

        // Also check by name (case-insensitive)
        if (Company::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists()) {
            $this->skipped++;
            return;
        }

        // Parse infobox
        $infobox = $this->parseInfobox($wikitext);

        // Build description (first 500 chars of extract)
        $description = Str::limit(trim($extract), 500);

        // Parse founded date
        $foundedDate = $this->parseFoundedDate($infobox['founded'] ?? '');

        // Parse website
        $website = $this->parseWebsite($infobox['website'] ?? $infobox['url'] ?? '');

        // Parse location
        $location = $this->parseLocation($infobox);

        // Determine status
        $status = 'operating';
        $closedAt = null;
        $acquiredBy = null;

        if (!empty($infobox['defunct'])) {
            $status = 'closed';
        }
        if (!empty($infobox['fate'])) {
            $fate = strtolower($infobox['fate']);
            if (str_contains($fate, 'acquired') || str_contains($fate, 'merged')) {
                $status = 'acquired';
                $acquiredBy = $this->cleanWikitext($infobox['fate']);
            } elseif (str_contains($fate, 'defunct') || str_contains($fate, 'closed') || str_contains($fate, 'dissolved')) {
                $status = 'closed';
            }
        }

        // Ensure unique slug
        $baseSlug = $slug;
        $i = 2;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        // Determine category from extract
        $category = $this->inferCategory($extract, $infobox);

        Company::create([
            'name' => $name,
            'slug' => $slug,
            'website' => $website,
            'description' => $description,
            'founded_date' => $foundedDate,
            'city' => $location['city'],
            'state' => $location['state'],
            'country' => $location['country'],
            'category' => $category,
            'status' => $status,
            'closed_at' => $closedAt,
            'acquired_by' => $acquiredBy,
            'import_source' => 'wikipedia',
        ]);

        $this->created++;
    }

    /**
     * Parse a Wikipedia infobox from wikitext into key-value pairs.
     */
    private function parseInfobox(string $wikitext): array
    {
        $infobox = [];

        // Match the infobox template
        if (!preg_match('/\{\{Infobox[^}]*?\n(.*?)\n\}\}/si', $wikitext, $match)) {
            return $infobox;
        }

        $content = $match[1];

        // Parse | key = value lines
        preg_match_all('/\|\s*(\w+)\s*=\s*(.*?)(?=\n\||\n\}\}|$)/s', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $key = strtolower(trim($m[1]));
            $value = trim($m[2]);
            if ($value !== '') {
                $infobox[$key] = $value;
            }
        }

        return $infobox;
    }

    /**
     * Extract a date from the "founded" infobox field.
     */
    private function parseFoundedDate(string $founded): ?string
    {
        if (empty($founded)) return null;

        $clean = $this->cleanWikitext($founded);

        // Try {{Start date|YYYY|MM|DD}}
        if (preg_match('/(\d{4})\|(\d{1,2})\|(\d{1,2})/', $founded, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        // Try {{Start date|YYYY|MM}}
        if (preg_match('/(\d{4})\|(\d{1,2})/', $founded, $m)) {
            return sprintf('%04d-%02d-01', $m[1], $m[2]);
        }

        // Try plain year
        if (preg_match('/(\d{4})/', $clean, $m)) {
            return $m[1] . '-01-01';
        }

        return null;
    }

    /**
     * Extract a URL from the "website" infobox field.
     */
    private function parseWebsite(string $website): ?string
    {
        if (empty($website)) return null;

        // Match {{URL|example.com}} or {{url|example.com}}
        if (preg_match('/\{\{URL\|([^}|]+)/i', $website, $m)) {
            $url = trim($m[1]);
            if (!str_starts_with($url, 'http')) {
                $url = 'https://' . $url;
            }
            return $url;
        }

        // Match [http... ] wiki external link
        if (preg_match('/\[(https?:\/\/[^\s\]]+)/', $website, $m)) {
            return $m[1];
        }

        // Plain URL
        if (preg_match('/(https?:\/\/[^\s]+)/', $website, $m)) {
            return $m[1];
        }

        // Bare domain
        $clean = $this->cleanWikitext($website);
        if (preg_match('/^[\w.-]+\.\w{2,}/', $clean)) {
            return 'https://' . $clean;
        }

        return null;
    }

    /**
     * Parse location from infobox fields.
     */
    private function parseLocation(array $infobox): array
    {
        $result = ['city' => null, 'state' => null, 'country' => null];

        // Try various location fields
        $locationFields = ['hq_location', 'headquarters', 'location', 'hq_location_city', 'location_city'];
        $raw = '';
        foreach ($locationFields as $field) {
            if (!empty($infobox[$field])) {
                $raw = $infobox[$field];
                break;
            }
        }

        if (empty($raw)) return $result;

        $clean = $this->cleanWikitext($raw);

        // Try to extract country from hq_location_country or location_country
        $countryFields = ['hq_location_country', 'location_country', 'country'];
        foreach ($countryFields as $field) {
            if (!empty($infobox[$field])) {
                $result['country'] = $this->cleanWikitext($infobox[$field]);
                break;
            }
        }

        // Common patterns: "City, State, Country" or "City, Country"
        $parts = array_map('trim', explode(',', $clean));

        if (count($parts) >= 1) {
            $result['city'] = $parts[0];
        }
        if (count($parts) >= 3) {
            $result['state'] = $parts[1];
            if (!$result['country']) {
                $result['country'] = $parts[2];
            }
        } elseif (count($parts) === 2) {
            // Could be "City, Country" or "City, State"
            $second = $parts[1];
            if ($this->isUSState($second)) {
                $result['state'] = $second;
                if (!$result['country']) $result['country'] = 'USA';
            } else {
                if (!$result['country']) $result['country'] = $second;
            }
        }

        // Normalize country names
        if ($result['country']) {
            $result['country'] = $this->normalizeCountry($result['country']);
        }

        return $result;
    }

    /**
     * Check if a string looks like a US state name or abbreviation.
     */
    private function isUSState(string $str): bool
    {
        $states = [
            'AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA',
            'KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ',
            'NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT',
            'VA','WA','WV','WI','WY','DC',
            'Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut',
            'Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa',
            'Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts','Michigan',
            'Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada',
            'New Hampshire','New Jersey','New Mexico','New York','North Carolina',
            'North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island',
            'South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont',
            'Virginia','Washington','West Virginia','Wisconsin','Wyoming',
        ];
        return in_array(trim($str), $states, true);
    }

    /**
     * Normalize common country name variations.
     */
    private function normalizeCountry(string $country): string
    {
        $map = [
            'United States' => 'USA',
            'United States of America' => 'USA',
            'U.S.' => 'USA',
            'US' => 'USA',
            'U.S.A.' => 'USA',
            'United Kingdom' => 'UK',
            'Great Britain' => 'UK',
            'England' => 'UK',
            'People\'s Republic of China' => 'China',
            'Republic of India' => 'India',
            'South Korea' => 'South Korea',
            'Republic of Korea' => 'South Korea',
        ];

        return $map[$country] ?? $country;
    }

    /**
     * Strip wiki markup from text.
     */
    private function cleanWikitext(string $text): string
    {
        // Remove [[ ]] links, keeping display text
        $text = preg_replace('/\[\[(?:[^|\]]*\|)?([^\]]*)\]\]/', '$1', $text);
        // Remove {{ }} templates
        $text = preg_replace('/\{\{[^}]*\}\}/', '', $text);
        // Remove HTML tags
        $text = strip_tags($text);
        // Remove ref tags and content
        $text = preg_replace('/<ref[^>]*>.*?<\/ref>/s', '', $text);
        $text = preg_replace('/<ref[^>]*\/?>/', '', $text);
        // Clean whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Infer a category from the extract text and infobox.
     */
    private function inferCategory(string $extract, array $infobox): ?string
    {
        $text = strtolower($extract . ' ' . ($infobox['industry'] ?? '') . ' ' . ($infobox['type'] ?? ''));

        $patterns = [
            'ai_ml' => ['artificial intelligence', 'machine learning', 'deep learning', 'neural network', 'large language model', ' ai ', 'generative ai'],
            'fintech' => ['financial technology', 'fintech', 'payment', 'banking', 'neobank', 'cryptocurrency', 'blockchain', 'trading platform'],
            'healthcare' => ['healthcare', 'health care', 'biotech', 'biotechnology', 'pharmaceutical', 'medical', 'telemedicine', 'drug discovery'],
            'climate' => ['clean energy', 'renewable energy', 'solar', 'climate', 'sustainability', 'electric vehicle', 'ev ', 'carbon'],
            'enterprise' => ['enterprise software', 'saas', 'cloud computing', 'business software', 'erp', 'crm', 'human resources'],
            'developer_tools' => ['developer tool', 'devops', 'developer platform', 'code editor', 'api platform', 'open.source software', 'version control'],
            'robotics' => ['robotics', 'autonomous', 'self-driving', 'drone', 'robot'],
            'space' => ['space', 'satellite', 'aerospace', 'rocket', 'orbital'],
            'defense' => ['defense', 'defence', 'military', 'intelligence'],
            'consumer' => ['social media', 'consumer', 'e-commerce', 'marketplace', 'food delivery', 'ride-hailing', 'streaming'],
        ];

        foreach ($patterns as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $category;
                }
            }
        }

        return null;
    }

    /**
     * Make a rate-limited request to the Wikipedia API.
     */
    private function apiRequest(array $params): ?array
    {
        $url = self::API_BASE . '?' . http_build_query($params);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: ' . self::USER_AGENT . "\r\n",
                'timeout' => 30,
            ],
        ]);

        usleep(self::RATE_LIMIT_US);
        $this->apiCalls++;

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            $this->command->warn("   ⚠ API request failed for: " . substr($url, 0, 120));
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->warn("   ⚠ Invalid JSON response");
            return null;
        }

        return $data;
    }
}
