<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    /**
     * Compare up to 4 companies side by side.
     *
     * GET /api/companies/compare?slugs=company-a,company-b,company-c
     */
    public function __invoke(Request $request): JsonResponse
    {
        $slugs = array_filter(array_slice(
            explode(',', $request->get('slugs', '')),
            0,
            4
        ));

        if (empty($slugs)) {
            return response()->json([
                'error' => 'Provide at least one company slug via the `slugs` query parameter (comma-separated, max 4).',
            ], 422);
        }

        $companies = Company::whereIn('slug', $slugs)
            ->with(['fundingRounds.investors', 'headcountSnapshots', 'people', 'latestFundingRound', 'tags'])
            ->withSum('fundingRounds', 'amount')
            ->withCount(['fundingRounds', 'people'])
            ->get()
            ->sortBy(fn ($c) => array_search($c->slug, $slugs))
            ->values();

        if ($companies->isEmpty()) {
            return response()->json([
                'error' => 'No companies found for the provided slugs.',
            ], 404);
        }

        // Build comparison summary
        $comparison = $companies->map(function ($company) {
            $snapshots = $company->headcountSnapshots->sortBy('recorded_date');
            $headcountGrowth = null;
            if ($snapshots->count() >= 2) {
                $first = $snapshots->first()->headcount;
                $last = $snapshots->last()->headcount;
                if ($first > 0) {
                    $headcountGrowth = round((($last - $first) / $first) * 100, 1);
                }
            }

            return [
                'slug' => $company->slug,
                'name' => $company->name,
                'founded_date' => $company->founded_date?->format('Y-m-d'),
                'category' => $company->category_label,
                'tags' => $company->tags->pluck('name'),
                'location' => trim(($company->city ?? '') . ', ' . ($company->country ?? ''), ', '),
                'current_headcount' => $company->current_headcount,
                'headcount_growth_percent' => $headcountGrowth,
                'total_funding' => $company->funding_rounds_sum_amount ? (float) $company->funding_rounds_sum_amount : null,
                'funding_rounds_count' => $company->funding_rounds_count,
                'latest_round' => $company->latestFundingRound ? [
                    'type' => $company->latestFundingRound->round_type,
                    'amount' => $company->latestFundingRound->amount ? (float) $company->latestFundingRound->amount : null,
                    'date' => $company->latestFundingRound->announced_date?->format('Y-m-d'),
                ] : null,
                'people_count' => $company->people_count,
            ];
        });

        return response()->json([
            'data' => [
                'companies' => $comparison,
                'full_profiles' => CompanyResource::collection($companies),
            ],
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'compared_count' => $companies->count(),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
