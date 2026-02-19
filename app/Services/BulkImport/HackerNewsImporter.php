<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HackerNewsImporter extends BaseBulkImporter
{
    private const ALGOLIA_API = 'https://hn.algolia.com/api/v1';

    public function source(): string
    {
        return 'hackernews';
    }

    public function import(array $options = []): void
    {
        Log::info("Hacker News (Show HN / Launch HN) import starting");

        $maxPages = $options['max_pages'] ?? 100;

        // Search for Show HN and Launch HN posts
        $queries = [
            'Show HN',
            'Launch HN',
        ];

        foreach ($queries as $query) {
            $this->importQuery($query, $maxPages);
        }

        Log::info("HN import complete: {$this->created} created, {$this->updated} updated, {$this->skipped} skipped");
    }

    private function importQuery(string $query, int $maxPages): void
    {
        $page = 0;

        while ($page < $maxPages) {
            $response = Http::timeout(15)->get(self::ALGOLIA_API . '/search', [
                'query' => $query,
                'tags' => 'show_hn',
                'hitsPerPage' => 100,
                'page' => $page,
                'numericFilters' => 'points>10', // Only posts with some traction
            ]);

            if (!$response->successful()) {
                Log::warning("HN Algolia API returned HTTP {$response->status()} on page {$page}");
                break;
            }

            $data = $response->json();
            $hits = $data['hits'] ?? [];

            if (empty($hits)) break;

            foreach ($hits as $hit) {
                $this->processHit($hit);
            }

            $nbPages = $data['nbPages'] ?? 0;
            if ($page >= $nbPages - 1) break;

            $page++;
            $this->rateLimitSleep(0.3);
        }
    }

    private function processHit(array $hit): void
    {
        $title = $hit['title'] ?? '';
        $url = $hit['url'] ?? null;
        $points = $hit['points'] ?? 0;

        // Extract company/product name from "Show HN: ProductName - description" or "Launch HN: ..."
        $name = null;
        if (preg_match('/^(?:Show|Launch)\s+HN:\s*([^–\-—:]+)/i', $title, $m)) {
            $name = trim($m[1]);
        }

        if (!$name || strlen($name) < 2 || strlen($name) > 100) {
            $this->skipped++;
            $this->processed++;
            return;
        }

        // Clean up name — remove trailing punctuation, version numbers
        $name = preg_replace('/\s*[–\-—].*$/', '', $name);
        $name = preg_replace('/\s*v?\d+\.\d+.*$/i', '', $name);
        $name = trim($name, ' .,!?()[]');

        if (strlen($name) < 2) {
            $this->skipped++;
            $this->processed++;
            return;
        }

        // Skip common non-company patterns
        $skipPatterns = [
            '/^I (built|made|created)/i',
            '/^My /i',
            '/^A (new|simple|free)/i',
            '/^The /i',
            '/^How /i',
            '/^We /i',
            '/^Ask HN/i',
        ];
        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $name)) {
                $this->skipped++;
                $this->processed++;
                return;
            }
        }

        // Extract description from the rest of the title
        $description = null;
        if (preg_match('/^(?:Show|Launch)\s+HN:\s*[^–\-—:]+[–\-—:]\s*(.+)/i', $title, $m)) {
            $description = trim($m[1]);
        }

        $website = null;
        if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
            // Skip github/gitlab links as website — these are repos, not product sites
            $host = parse_url($url, PHP_URL_HOST);
            if ($host && !preg_match('/github\.com|gitlab\.com|bitbucket\.org/i', $host)) {
                $website = $url;
            }
        }

        $this->upsertCompany([
            'name' => $name,
            'description' => $description ? mb_substr($description, 0, 500) : null,
            'website' => $website,
            'category' => $this->guessCategory($title . ' ' . ($description ?? '')),
        ]);
    }

    private function guessCategory(string $text): ?string
    {
        $t = strtolower($text);

        if (preg_match('/\b(ai|machine learning|llm|gpt|neural|deep learning|nlp)\b/', $t)) return 'ai_ml';
        if (preg_match('/\b(fintech|payment|banking|invoice|accounting)\b/', $t)) return 'fintech';
        if (preg_match('/\b(health|medical|biotech|clinical|patient)\b/', $t)) return 'healthcare';
        if (preg_match('/\b(developer|devtool|sdk|api|cli|terminal|ide|code)\b/', $t)) return 'developer_tools';
        if (preg_match('/\b(saas|crm|erp|b2b|enterprise)\b/', $t)) return 'saas';
        if (preg_match('/\b(ecommerce|e-commerce|shop|marketplace|retail)\b/', $t)) return 'ecommerce';
        if (preg_match('/\b(security|cyber|encryption|privacy|auth)\b/', $t)) return 'security';
        if (preg_match('/\b(education|learn|course|tutorial|school)\b/', $t)) return 'education';
        if (preg_match('/\b(crypto|blockchain|web3|defi|nft)\b/', $t)) return 'crypto';
        if (preg_match('/\b(game|gaming|play)\b/', $t)) return 'gaming';

        return null;
    }
}
