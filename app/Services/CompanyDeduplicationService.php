<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Service for detecting potential duplicate companies.
 *
 * Uses multiple signals: name similarity, website domain matching,
 * LinkedIn URL matching, and location proximity.
 */
class CompanyDeduplicationService
{
    /**
     * Minimum similarity score (0-100) to flag as potential duplicate.
     */
    protected int $threshold;

    public function __construct()
    {
        $this->threshold = (int) config('startupgraph.deduplication.company_threshold', 70);
    }

    /**
     * Find potential duplicates for a given company name and optional fields.
     */
    public function findDuplicates(string $name, ?string $website = null, ?string $linkedinUrl = null): Collection
    {
        $candidates = $this->getCandidates($name);
        $results = collect();

        foreach ($candidates as $company) {
            $score = $this->calculateSimilarityScore($name, $website, $linkedinUrl, $company);
            if ($score >= $this->threshold) {
                $results->push([
                    'company' => $company,
                    'score' => $score,
                    'signals' => $this->getMatchingSignals($name, $website, $linkedinUrl, $company),
                ]);
            }
        }

        return $results->sortByDesc('score')->values();
    }

    /**
     * Scan all companies for potential duplicates.
     */
    public function findAllDuplicates(): Collection
    {
        $companies = Company::orderBy('name')->get();
        $duplicateGroups = collect();
        $seen = [];

        foreach ($companies as $i => $company) {
            if (in_array($company->id, $seen)) {
                continue;
            }

            $group = collect();
            foreach ($companies->slice($i + 1) as $other) {
                if (in_array($other->id, $seen)) {
                    continue;
                }

                $score = $this->calculateSimilarityScore(
                    $company->name,
                    $company->website,
                    $company->linkedin_url,
                    $other
                );

                if ($score >= $this->threshold) {
                    $group->push([
                        'company' => $other,
                        'score' => $score,
                        'signals' => $this->getMatchingSignals(
                            $company->name,
                            $company->website,
                            $company->linkedin_url,
                            $other
                        ),
                    ]);
                    $seen[] = $other->id;
                }
            }

            if ($group->isNotEmpty()) {
                $duplicateGroups->push([
                    'primary' => $company,
                    'duplicates' => $group,
                ]);
                $seen[] = $company->id;
            }
        }

        return $duplicateGroups;
    }

    /**
     * Get candidate companies that might match the given name.
     */
    private function getCandidates(string $name): Collection
    {
        $normalized = $this->normalizeName($name);
        $words = explode(' ', $normalized);
        $firstWord = $words[0] ?? '';

        return Company::where(function ($q) use ($name, $firstWord) {
            $q->where('name', 'like', "%{$firstWord}%")
              ->orWhere('name', 'like', "%{$name}%");
        })->get();
    }

    /**
     * Calculate a similarity score (0-100) between input and existing company.
     */
    private function calculateSimilarityScore(string $name, ?string $website, ?string $linkedinUrl, Company $company): int
    {
        $scores = [];

        // Name similarity (weight: 40)
        $nameSim = $this->nameSimilarity($name, $company->name);
        $scores[] = $nameSim * 40;

        // Domain matching (weight: 30)
        if ($website && $company->website) {
            $domainMatch = $this->domainMatch($website, $company->website);
            $scores[] = $domainMatch * 30;
        }

        // LinkedIn URL matching (weight: 30)
        if ($linkedinUrl && $company->linkedin_url) {
            $linkedinMatch = $this->linkedinMatch($linkedinUrl, $company->linkedin_url);
            $scores[] = $linkedinMatch * 30;
        }

        $totalWeight = 40 + ($website && $company->website ? 30 : 0) + ($linkedinUrl && $company->linkedin_url ? 30 : 0);

        if ($totalWeight === 0) {
            return 0;
        }

        return (int) round(array_sum($scores) / $totalWeight * 100);
    }

    private function nameSimilarity(string $a, string $b): float
    {
        $na = $this->normalizeName($a);
        $nb = $this->normalizeName($b);

        if ($na === $nb) {
            return 1.0;
        }

        similar_text($na, $nb, $percent);
        return $percent / 100;
    }

    private function normalizeName(string $name): string
    {
        $name = strtolower(trim($name));
        // Remove common suffixes
        $name = preg_replace('/\b(inc|llc|ltd|co|corp|corporation|company)\b\.?/i', '', $name);
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);
        return trim(preg_replace('/\s+/', ' ', $name));
    }

    private function domainMatch(string $url1, string $url2): float
    {
        $d1 = $this->extractDomain($url1);
        $d2 = $this->extractDomain($url2);

        return $d1 && $d2 && $d1 === $d2 ? 1.0 : 0.0;
    }

    private function extractDomain(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? $url;
        $host = preg_replace('/^www\./', '', strtolower($host));
        return $host ?: null;
    }

    private function linkedinMatch(string $url1, string $url2): float
    {
        $slug1 = $this->extractLinkedinSlug($url1);
        $slug2 = $this->extractLinkedinSlug($url2);

        return $slug1 && $slug2 && $slug1 === $slug2 ? 1.0 : 0.0;
    }

    private function extractLinkedinSlug(string $url): ?string
    {
        if (preg_match('#linkedin\.com/company/([^/?]+)#i', $url, $m)) {
            return strtolower($m[1]);
        }
        return null;
    }

    private function getMatchingSignals(string $name, ?string $website, ?string $linkedinUrl, Company $company): array
    {
        $signals = [];

        if ($this->nameSimilarity($name, $company->name) > 0.7) {
            $signals[] = 'name_similar';
        }
        if ($website && $company->website && $this->domainMatch($website, $company->website) === 1.0) {
            $signals[] = 'same_domain';
        }
        if ($linkedinUrl && $company->linkedin_url && $this->linkedinMatch($linkedinUrl, $company->linkedin_url) === 1.0) {
            $signals[] = 'same_linkedin';
        }

        return $signals;
    }

    public function setThreshold(int $threshold): self
    {
        $this->threshold = $threshold;
        return $this;
    }
}
