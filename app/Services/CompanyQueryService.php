<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FundingRound;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Builds company queries from a criteria array. One implementation shared
 * by the REST API, screens, and MCP tools — the parity rule in practice:
 * anything a human can filter on, an agent's saved screen can too.
 *
 * Supported criteria keys: q, category, country, funded_after,
 * funded_before, funded_recent (3m|6m|1y|2y), sort, order.
 */
class CompanyQueryService
{
    public const ALLOWED_SORTS = [
        'name',
        'founded_date',
        'city',
        'country',
        'category',
        'funding_rounds_sum_amount',
        'latest_funding_date',
    ];

    public const CRITERIA_KEYS = [
        'q', 'category', 'country', 'funded_after', 'funded_before',
        'funded_recent', 'sort', 'order',
    ];

    /**
     * Keep only known criteria keys with scalar values — screens store
     * this JSON and clients decode it strictly as string maps.
     */
    public static function sanitizeCriteria(array $criteria): array
    {
        return collect($criteria)
            ->only(self::CRITERIA_KEYS)
            ->filter(fn ($value) => is_scalar($value) && $value !== '')
            ->map(fn ($value) => (string) $value)
            ->all();
    }

    public function build(array $criteria): Builder
    {
        $query = Company::query()
            ->withSum('fundingRounds', 'amount')
            ->withCount('fundingRounds')
            ->with('latestFundingRound')
            ->addSelect(['latest_funding_date' => FundingRound::select('announced_date')
                ->whereColumn('company_id', 'companies.id')
                ->orderBy('announced_date', 'desc')
                ->limit(1),
            ]);

        if ($search = ($criteria['q'] ?? null)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($country = ($criteria['country'] ?? null)) {
            $query->where('country', $country);
        }

        if ($category = ($criteria['category'] ?? null)) {
            $query->where('category', $category);
        }

        if ($fundedAfter = ($criteria['funded_after'] ?? null)) {
            $query->whereHas('fundingRounds', function ($q) use ($fundedAfter) {
                $q->where('announced_date', '>=', $fundedAfter);
            });
        }

        if ($fundedBefore = ($criteria['funded_before'] ?? null)) {
            $query->whereHas('fundingRounds', function ($q) use ($fundedBefore) {
                $q->where('announced_date', '<=', $fundedBefore);
            });
        }

        if ($fundedRecent = ($criteria['funded_recent'] ?? null)) {
            $dateThreshold = match ($fundedRecent) {
                '3m' => Carbon::now()->subMonths(3),
                '6m' => Carbon::now()->subMonths(6),
                '1y' => Carbon::now()->subYear(),
                '2y' => Carbon::now()->subYears(2),
                default => null,
            };
            if ($dateThreshold) {
                $query->whereHas('fundingRounds', function ($q) use ($dateThreshold) {
                    $q->where('announced_date', '>=', $dateThreshold);
                });
            }
        }

        $sortField = $criteria['sort'] ?? 'name';
        $sortDirection = ($criteria['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sortField, self::ALLOWED_SORTS)) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query;
    }
}
