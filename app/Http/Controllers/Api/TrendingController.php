<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\HeadcountSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TrendingController extends Controller
{
    /**
     * Return companies with the fastest headcount growth.
     *
     * GET /api/companies/trending?period=3m&limit=20
     */
    public function __invoke(Request $request): JsonResponse
    {
        $period = $request->get('period', '3m');
        $limit = min((int) $request->get('limit', 20), 100);
        $minHeadcount = (int) $request->get('min_headcount', 5);

        $startDate = match ($period) {
            '1m' => Carbon::now()->subMonth(),
            '3m' => Carbon::now()->subMonths(3),
            '6m' => Carbon::now()->subMonths(6),
            '1y' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonths(3),
        };

        // Get companies with snapshots in the period
        $companies = Company::where('current_headcount', '>=', $minHeadcount)
            ->whereHas('headcountSnapshots', fn ($q) => $q->where('recorded_date', '<=', $startDate))
            ->with(['headcountSnapshots' => function ($q) use ($startDate) {
                $q->orderBy('recorded_date');
            }])
            ->get();

        $trending = $companies->map(function ($company) use ($startDate) {
            $snapshots = $company->headcountSnapshots->sortBy('recorded_date');

            // Find the snapshot closest to start date
            $baseline = $snapshots->filter(fn ($s) => $s->recorded_date <= $startDate)->last();
            $latest = $snapshots->last();

            if (!$baseline || !$latest || $baseline->headcount <= 0) {
                return null;
            }

            $growth = round((($latest->headcount - $baseline->headcount) / $baseline->headcount) * 100, 1);
            $absoluteGrowth = $latest->headcount - $baseline->headcount;

            return [
                'slug' => $company->slug,
                'name' => $company->name,
                'category' => $company->category_label,
                'location' => trim(($company->city ?? '') . ', ' . ($company->country ?? ''), ', '),
                'current_headcount' => $company->current_headcount,
                'baseline_headcount' => $baseline->headcount,
                'baseline_date' => $baseline->recorded_date->format('Y-m-d'),
                'growth_percent' => $growth,
                'absolute_growth' => $absoluteGrowth,
            ];
        })
        ->filter()
        ->sortByDesc('growth_percent')
        ->take($limit)
        ->values();

        return response()->json([
            'data' => $trending,
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'period' => $period,
                'min_headcount' => $minHeadcount,
                'results_count' => $trending->count(),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
