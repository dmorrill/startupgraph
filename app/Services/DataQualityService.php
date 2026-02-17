<?php

namespace App\Services;

use App\Models\Company;

/**
 * Calculate a data quality/completeness score for a company profile.
 * Score ranges from 0-100.
 */
class DataQualityService
{
    /**
     * Fields and their weights for quality scoring.
     */
    private array $fields = [
        'name' => 5,
        'slug' => 2,
        'website' => 8,
        'logo_url' => 3,
        'description' => 10,
        'product_highlights' => 8,
        'founded_date' => 7,
        'city' => 5,
        'country' => 5,
        'category' => 5,
        'linkedin_url' => 6,
        'twitter_url' => 3,
        'github_url' => 3,
        'current_headcount' => 8,
        'has_funding_rounds' => 10,
        'has_headcount_snapshots' => 7,
        'has_people' => 7,
    ];

    /**
     * Calculate quality score for a company.
     */
    public function score(Company $company): array
    {
        $totalWeight = array_sum($this->fields);
        $earned = 0;
        $details = [];

        foreach ($this->fields as $field => $weight) {
            $present = $this->isFieldPresent($company, $field);
            if ($present) {
                $earned += $weight;
            }
            $details[$field] = [
                'present' => $present,
                'weight' => $weight,
            ];
        }

        $score = (int) round(($earned / $totalWeight) * 100);

        return [
            'score' => $score,
            'grade' => $this->scoreToGrade($score),
            'earned_weight' => $earned,
            'total_weight' => $totalWeight,
            'fields' => $details,
            'missing' => collect($details)->filter(fn ($d) => !$d['present'])->keys()->values()->toArray(),
        ];
    }

    /**
     * Calculate scores for all companies.
     */
    public function scoreAll(): array
    {
        $companies = Company::with(['fundingRounds', 'headcountSnapshots', 'people'])->get();
        $scores = [];

        foreach ($companies as $company) {
            $result = $this->score($company);
            $scores[] = [
                'slug' => $company->slug,
                'name' => $company->name,
                'score' => $result['score'],
                'grade' => $result['grade'],
                'missing' => $result['missing'],
            ];
        }

        usort($scores, fn ($a, $b) => $a['score'] <=> $b['score']);

        $allScores = array_column($scores, 'score');

        return [
            'companies' => $scores,
            'summary' => [
                'total' => count($scores),
                'average_score' => count($scores) > 0 ? round(array_sum($allScores) / count($allScores), 1) : 0,
                'median_score' => $this->median($allScores),
                'grade_distribution' => array_count_values(array_column($scores, 'grade')),
            ],
        ];
    }

    private function isFieldPresent(Company $company, string $field): bool
    {
        return match ($field) {
            'product_highlights' => !empty($company->product_highlights) && is_array($company->product_highlights) && count($company->product_highlights) > 0,
            'has_funding_rounds' => $company->relationLoaded('fundingRounds') ? $company->fundingRounds->isNotEmpty() : $company->fundingRounds()->exists(),
            'has_headcount_snapshots' => $company->relationLoaded('headcountSnapshots') ? $company->headcountSnapshots->isNotEmpty() : $company->headcountSnapshots()->exists(),
            'has_people' => $company->relationLoaded('people') ? $company->people->isNotEmpty() : $company->people()->exists(),
            default => !empty($company->{$field}),
        };
    }

    private function scoreToGrade(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 65 => 'C',
            $score >= 50 => 'D',
            default => 'F',
        };
    }

    private function median(array $values): float
    {
        if (empty($values)) {
            return 0;
        }
        sort($values);
        $count = count($values);
        $mid = (int) floor($count / 2);
        return $count % 2 === 0
            ? ($values[$mid - 1] + $values[$mid]) / 2
            : $values[$mid];
    }
}
