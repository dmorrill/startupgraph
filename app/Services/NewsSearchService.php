<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsSearchService
{
    private const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    /**
     * Search for news articles mentioning a company.
     *
     * Uses TechCrunch's search functionality to find articles mentioning the company name.
     *
     * @param Company $company
     * @return array{success: bool, articles: array, error: string|null}
     */
    public function searchCompanyNews(Company $company): array
    {
        try {
            // URL encode the company name for the search query
            $searchQuery = urlencode($company->name);
            $searchUrl = "https://techcrunch.com/?s={$searchQuery}";

            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(30)->get($searchUrl);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'articles' => [],
                    'error' => "HTTP {$response->status()}",
                ];
            }

            $html = $response->body();
            $articles = $this->parseSearchResults($html, $company->name);

            return [
                'success' => true,
                'articles' => $articles,
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::warning("News search error for {$company->name}: {$e->getMessage()}");

            return [
                'success' => false,
                'articles' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse TechCrunch search results HTML to extract articles.
     *
     * @param string $html
     * @param string $companyName
     * @return array
     */
    private function parseSearchResults(string $html, string $companyName): array
    {
        $articles = [];

        // Look for article links and titles in TechCrunch's search results HTML
        // Pattern matches article cards with titles, URLs, and dates
        preg_match_all(
            '/<a[^>]*href="(https:\/\/techcrunch\.com\/\d{4}\/\d{2}\/\d{2}\/[^"]+)"[^>]*>([^<]+)<\/a>/i',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $url = $match[1];
            $title = html_entity_decode(trim($match[2]));

            // Skip if the title doesn't actually mention the company
            if (!$this->titleMentionsCompany($title, $companyName)) {
                continue;
            }

            // Extract date from URL (format: /YYYY/MM/DD/)
            $publishedDate = $this->extractDateFromUrl($url);

            $articles[] = [
                'url' => $url,
                'title' => $title,
                'source' => 'TechCrunch',
                'published_date' => $publishedDate,
                'summary' => null, // Would need additional parsing to get excerpts
            ];
        }

        // Deduplicate by URL
        $seen = [];
        $unique = [];
        foreach ($articles as $article) {
            if (!isset($seen[$article['url']])) {
                $seen[$article['url']] = true;
                $unique[] = $article;
            }
        }

        // Limit to 10 most recent articles per company
        return array_slice($unique, 0, 10);
    }

    /**
     * Check if a title mentions the company name.
     *
     * @param string $title
     * @param string $companyName
     * @return bool
     */
    private function titleMentionsCompany(string $title, string $companyName): bool
    {
        // Skip very short company names to avoid false positives
        if (strlen($companyName) < 3) {
            return false;
        }

        // Check if company name appears as a whole word
        $pattern = '/\b' . preg_quote($companyName, '/') . '\b/i';
        return (bool) preg_match($pattern, $title);
    }

    /**
     * Extract publication date from TechCrunch article URL.
     *
     * @param string $url
     * @return string|null Date in Y-m-d format
     */
    private function extractDateFromUrl(string $url): ?string
    {
        if (preg_match('/techcrunch\.com\/(\d{4})\/(\d{2})\/(\d{2})\//', $url, $dateMatch)) {
            return "{$dateMatch[1]}-{$dateMatch[2]}-{$dateMatch[3]}";
        }
        return null;
    }
}
