<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WikipediaCategoryImporter extends BaseBulkImporter
{
    private const WIKIPEDIA_API = 'https://en.wikipedia.org/w/api.php';

    private const CATEGORIES = [
        // Technology companies by year (expanded range)
        'Category:Technology_companies_established_in_2015' => 'operating',
        'Category:Technology_companies_established_in_2016' => 'operating',
        'Category:Technology_companies_established_in_2017' => 'operating',
        'Category:Technology_companies_established_in_2018' => 'operating',
        'Category:Technology_companies_established_in_2019' => 'operating',
        'Category:Technology_companies_established_in_2020' => 'operating',
        'Category:Technology_companies_established_in_2021' => 'operating',
        'Category:Technology_companies_established_in_2022' => 'operating',
        'Category:Technology_companies_established_in_2023' => 'operating',
        'Category:Technology_companies_established_in_2024' => 'operating',
        'Category:Technology_companies_established_in_2025' => 'operating',
        
        // American companies by year (expanded range)
        'Category:American_companies_established_in_2015' => 'operating',
        'Category:American_companies_established_in_2016' => 'operating',
        'Category:American_companies_established_in_2017' => 'operating',
        'Category:American_companies_established_in_2018' => 'operating',
        'Category:American_companies_established_in_2019' => 'operating',
        'Category:American_companies_established_in_2020' => 'operating',
        'Category:American_companies_established_in_2021' => 'operating',
        'Category:American_companies_established_in_2022' => 'operating',
        'Category:American_companies_established_in_2023' => 'operating',
        'Category:American_companies_established_in_2024' => 'operating',
        'Category:American_companies_established_in_2025' => 'operating',
        
        // Companies by major startup hubs/countries
        'Category:Companies_based_in_San_Francisco' => 'operating',
        'Category:Companies_based_in_Silicon_Valley' => 'operating',
        'Category:Companies_based_in_Seattle' => 'operating',
        'Category:Companies_based_in_New_York_City' => 'operating',
        'Category:Companies_based_in_Boston' => 'operating',
        'Category:Companies_based_in_Austin,_Texas' => 'operating',
        'Category:Companies_based_in_Los_Angeles' => 'operating',
        
        // International startup hubs
        'Category:Companies_based_in_London' => 'operating',
        'Category:Companies_based_in_Berlin' => 'operating',
        'Category:Companies_based_in_Tel_Aviv' => 'operating',
        'Category:Companies_based_in_Singapore' => 'operating',
        'Category:Companies_based_in_Toronto' => 'operating',
        'Category:Companies_based_in_Sydney' => 'operating',
        
        // Industry-specific categories
        'Category:Software_companies_of_the_United_States' => 'operating',
        'Category:Internet_companies_of_the_United_States' => 'operating',
        'Category:Financial_technology_companies' => 'operating',
        'Category:Artificial_intelligence_companies' => 'operating',
        'Category:Robotics_companies' => 'operating',
        'Category:Biotechnology_companies_of_the_United_States' => 'operating',
        'Category:Cybersecurity_companies' => 'operating',
        'Category:SaaS_companies' => 'operating',
        'Category:E-commerce_companies_of_the_United_States' => 'operating',
        'Category:Social_networking_companies' => 'operating',
        'Category:Video_game_companies_of_the_United_States' => 'operating',
        'Category:Mobile_app_companies' => 'operating',
        'Category:Cloud_computing_companies' => 'operating',
        'Category:Data_management_companies' => 'operating',
        
        // Startup/venture capital categories
        'Category:Y_Combinator_companies' => 'operating',
        'Category:Companies_funded_by_Sequoia_Capital' => 'operating',
        'Category:Companies_funded_by_Andreessen_Horowitz' => 'operating',
        'Category:Venture_capital-backed_companies' => 'operating',
        
        // Business model categories
        'Category:Subscription_software' => 'operating',
        'Category:Platform_companies' => 'operating',
        'Category:Marketplace_companies' => 'operating',
        
        // International companies by country (recent years)
        'Category:Companies_established_in_2020' => 'operating',
        'Category:Companies_established_in_2021' => 'operating',
        'Category:Companies_established_in_2022' => 'operating',
        'Category:Companies_established_in_2023' => 'operating',
        'Category:Companies_established_in_2024' => 'operating',
        
        // Specific international tech companies
        'Category:Technology_companies_of_Canada' => 'operating',
        'Category:Technology_companies_of_the_United_Kingdom' => 'operating',
        'Category:Technology_companies_of_Germany' => 'operating',
        'Category:Technology_companies_of_Israel' => 'operating',
        'Category:Technology_companies_of_Singapore' => 'operating',
        'Category:Technology_companies_of_Australia' => 'operating',
        'Category:Software_companies_of_Canada' => 'operating',
        'Category:Software_companies_of_the_United_Kingdom' => 'operating',
        'Category:Internet_companies_of_the_United_Kingdom' => 'operating',
        'Category:Internet_companies_of_Canada' => 'operating',
    ];

    public function source(): string
    {
        return 'wikipedia-categories';
    }

    public function import(array $options = []): void
    {
        Log::info("Wikipedia category companies import starting");

        foreach (self::CATEGORIES as $category => $status) {
            Log::info("Importing from {$category}");
            $this->importCategory($category, $status);
        }

        Log::info("Wikipedia category companies import complete", $this->getStats());
    }

    private function importCategory(string $category, string $status): void
    {
        $continue = null;

        // Extract metadata from category name
        $metadata = $this->extractCategoryMetadata($category);
        
        $year = $metadata['year'];
        $country = $metadata['country'];
        $city = $metadata['city'];
        $categoryType = $metadata['category'];

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

            if (!$response->successful()) {
                Log::warning("Wikipedia: Failed to fetch category {$category}");
                break;
            }

            $data = $response->json();
            $members = $data['query']['categorymembers'] ?? [];
            $continue = $data['continue']['cmcontinue'] ?? null;

            // Batch fetch extracts
            $titles = array_map(fn($m) => $m['title'], $members);
            $chunks = array_chunk($titles, 50);

            foreach ($chunks as $chunk) {
                $this->fetchAndImportExtracts($chunk, $status, $metadata);
                $this->rateLimitSleep(1.0);
            }

        } while ($continue);
    }

    private function extractCategoryMetadata(string $category): array
    {
        $metadata = [
            'year' => null,
            'country' => null,
            'city' => null,
            'category' => null,
        ];

        // Extract year
        if (preg_match('/(\d{4})/', $category, $matches)) {
            $metadata['year'] = $matches[1];
        }

        // Map locations to countries and cities
        $locationMap = [
            // US cities
            'San_Francisco' => ['country' => 'US', 'city' => 'San Francisco'],
            'Silicon_Valley' => ['country' => 'US', 'city' => 'Silicon Valley'],
            'Seattle' => ['country' => 'US', 'city' => 'Seattle'],
            'New_York_City' => ['country' => 'US', 'city' => 'New York'],
            'Boston' => ['country' => 'US', 'city' => 'Boston'],
            'Austin,_Texas' => ['country' => 'US', 'city' => 'Austin'],
            'Los_Angeles' => ['country' => 'US', 'city' => 'Los Angeles'],
            // International cities
            'London' => ['country' => 'GB', 'city' => 'London'],
            'Berlin' => ['country' => 'DE', 'city' => 'Berlin'],
            'Tel_Aviv' => ['country' => 'IL', 'city' => 'Tel Aviv'],
            'Singapore' => ['country' => 'SG', 'city' => 'Singapore'],
            'Toronto' => ['country' => 'CA', 'city' => 'Toronto'],
            'Sydney' => ['country' => 'AU', 'city' => 'Sydney'],
        ];

        // Check for location-based categories
        foreach ($locationMap as $location => $info) {
            if (str_contains($category, $location)) {
                $metadata['country'] = $info['country'];
                $metadata['city'] = $info['city'];
                break;
            }
        }

        // Map country names to codes
        $countryMap = [
            'American' => 'US',
            'United_States' => 'US',
            'Canada' => 'CA',
            'United_Kingdom' => 'GB',
            'Germany' => 'DE',
            'Israel' => 'IL',
            'Singapore' => 'SG',
            'Australia' => 'AU',
        ];

        // Check for country-based categories (if no city was found)
        if (!$metadata['country']) {
            foreach ($countryMap as $countryName => $countryCode) {
                if (str_contains($category, $countryName)) {
                    $metadata['country'] = $countryCode;
                    break;
                }
            }
        }

        // Extract business category/industry
        $industryMap = [
            'Software' => 'software',
            'Internet' => 'internet',
            'Financial_technology' => 'fintech',
            'Artificial_intelligence' => 'ai_ml',
            'Robotics' => 'robotics',
            'Biotechnology' => 'biotechnology',
            'Cybersecurity' => 'cybersecurity',
            'SaaS' => 'saas',
            'E-commerce' => 'ecommerce',
            'Social_networking' => 'social',
            'Video_game' => 'gaming',
            'Mobile_app' => 'mobile',
            'Cloud_computing' => 'cloud',
            'Data_management' => 'data',
            'Technology' => 'technology',
        ];

        foreach ($industryMap as $industry => $categorySlug) {
            if (str_contains($category, $industry)) {
                $metadata['category'] = $categorySlug;
                break;
            }
        }

        return $metadata;
    }

    private function isValidCompany(string $title, string $extract): bool
    {
        // Skip disambiguation and list pages
        if (str_contains(strtolower($extract), 'may refer to') ||
            str_contains(strtolower($title), 'list of')) {
            return false;
        }

        // Skip redirects and general terms
        $invalidPrefixes = ['list of', 'category:', 'template:', 'portal:', 'file:'];
        foreach ($invalidPrefixes as $prefix) {
            if (str_starts_with(strtolower($title), $prefix)) {
                return false;
            }
        }

        // Skip very generic terms
        $genericTerms = [
            'companies', 'corporation', 'industry', 'business', 'enterprise',
            'organization', 'association', 'foundation', 'institute', 'society',
            'technology', 'software', 'internet', 'web', 'digital', 'online',
            'startup', 'company', 'inc.', 'llc', 'ltd', 'corp'
        ];
        
        $lowerTitle = strtolower($title);
        // If title is ONLY generic terms (not containing them), skip
        foreach ($genericTerms as $term) {
            if ($lowerTitle === $term || $lowerTitle === $term . 's') {
                return false;
            }
        }

        // Must have reasonable length
        if (strlen($title) < 2 || strlen($title) > 100) {
            return false;
        }

        // Skip pure numeric titles
        if (is_numeric($title)) {
            return false;
        }

        // Skip titles that are mostly punctuation or special characters
        if (preg_match('/^[^a-zA-Z0-9\s]*$/', $title)) {
            return false;
        }

        // For company validation, look for company indicators in extract
        if ($extract) {
            $extract = strtolower($extract);
            $companyIndicators = [
                'company', 'corporation', 'startup', 'founded', 'established',
                'headquarters', 'ceo', 'business', 'services', 'products',
                'software', 'technology', 'platform', 'application', 'website',
                'customers', 'users', 'revenue', 'funding', 'investor'
            ];
            
            $hasCompanyIndicator = false;
            foreach ($companyIndicators as $indicator) {
                if (str_contains($extract, $indicator)) {
                    $hasCompanyIndicator = true;
                    break;
                }
            }

            // If we have an extract but no company indicators, be more cautious
            if (!$hasCompanyIndicator && strlen($extract) > 50) {
                return false;
            }
        }

        return true;
    }

    private function fetchAndImportExtracts(array $titles, string $status, array $metadata): void
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
            foreach ($titles as $title) {
                $this->upsertCompany([
                    'name' => $title,
                    'status' => $status,
                    'founded_date' => $metadata['year'] ? "{$metadata['year']}-01-01" : null,
                    'country' => $metadata['country'],
                    'city' => $metadata['city'],
                    'category' => $metadata['category'],
                ]);
            }
            return;
        }

        $pages = $response->json()['query']['pages'] ?? [];

        foreach ($pages as $page) {
            $title = $page['title'] ?? '';
            $extract = $page['extract'] ?? '';

            if (!$title || str_starts_with($title, 'Category:')) continue;

            // Enhanced data validation
            if (!$this->isValidCompany($title, $extract)) continue;

            $description = $extract ? substr(trim($extract), 0, 500) : null;

            $this->upsertCompany([
                'name' => $title,
                'status' => $status,
                'description' => $description,
                'founded_date' => $metadata['year'] ? "{$metadata['year']}-01-01" : null,
                'country' => $metadata['country'],
                'city' => $metadata['city'],
                'category' => $metadata['category'],
            ]);
        }
    }
}
