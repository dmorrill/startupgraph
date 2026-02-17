<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\CompanySummaryResource;
use App\Http\Resources\FundingRoundResource;
use App\Http\Resources\HeadcountSnapshotResource;
use App\Http\Resources\PersonSummaryResource;
use App\Models\Company;
use App\Models\FundingRound;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CompanyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Company::query()
            ->withSum('fundingRounds', 'amount')
            ->withCount('fundingRounds')
            ->with('latestFundingRound')
            ->addSelect(['latest_funding_date' => FundingRound::select('announced_date')
                ->whereColumn('company_id', 'companies.id')
                ->orderBy('announced_date', 'desc')
                ->limit(1)
            ]);

        // Search by name, description, city, country
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($country = $request->get('country')) {
            $query->where('country', $country);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        // Filter by funding stage (latest round type)
        if ($fundingStage = $request->get('funding_stage')) {
            $stages = array_map('trim', explode(',', $fundingStage));
            $query->whereHas('latestFundingRound', function ($q) use ($stages) {
                $q->whereIn('round_type', $stages);
            });
        }

        // Filter by minimum/maximum total funding
        if ($minFunding = $request->get('min_funding')) {
            $query->having('funding_rounds_sum_amount', '>=', (float) $minFunding);
        }
        if ($maxFunding = $request->get('max_funding')) {
            $query->having('funding_rounds_sum_amount', '<=', (float) $maxFunding);
        }

        // Date range filter for last fundraise
        if ($fundedAfter = $request->get('funded_after')) {
            $query->whereHas('fundingRounds', function ($q) use ($fundedAfter) {
                $q->where('announced_date', '>=', $fundedAfter);
            });
        }

        if ($fundedBefore = $request->get('funded_before')) {
            $query->whereHas('fundingRounds', function ($q) use ($fundedBefore) {
                $q->where('announced_date', '<=', $fundedBefore);
            });
        }

        // Preset date filters
        if ($fundedRecent = $request->get('funded_recent')) {
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

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('order', 'asc');

        $allowedSorts = ['name', 'founded_date', 'city', 'country', 'category', 'funding_rounds_sum_amount', 'latest_funding_date'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        $perPage = min((int) $request->get('per_page', 50), 100);
        $companies = $query->paginate($perPage);

        return response()->json([
            'data' => CompanySummaryResource::collection($companies),
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'generated_at' => now()->toIso8601String(),
            ],
            'pagination' => [
                'total' => $companies->total(),
                'per_page' => $companies->perPage(),
                'current_page' => $companies->currentPage(),
                'last_page' => $companies->lastPage(),
            ],
        ]);
    }

    public function show(Company $company): JsonResponse
    {
        $company->load([
            'fundingRounds.investors',
            'latestFundingRound',
            'headcountSnapshots',
            'people',
        ]);

        $company->loadSum('fundingRounds', 'amount');
        $company->loadCount('fundingRounds');

        return response()->json([
            'data' => new CompanyResource($company),
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function funding(Company $company): JsonResponse
    {
        $company->load('fundingRounds.investors');

        $fundingRounds = $company->fundingRounds->sortByDesc('announced_date')->values();

        return response()->json([
            'data' => [
                'company_slug' => $company->slug,
                'company_name' => $company->name,
                'total_funding' => (float) $fundingRounds->sum('amount'),
                'total_funding_formatted' => $this->formatAmount($fundingRounds->sum('amount')),
                'rounds_count' => $fundingRounds->count(),
                'funding_rounds' => FundingRoundResource::collection($fundingRounds),
            ],
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function people(Company $company): JsonResponse
    {
        $company->load('people');

        $currentPeople = $company->people->where('pivot.is_current', true)->values();
        $formerPeople = $company->people->where('pivot.is_current', false)->values();

        return response()->json([
            'data' => [
                'company_slug' => $company->slug,
                'company_name' => $company->name,
                'total_count' => $company->people->count(),
                'current_count' => $currentPeople->count(),
                'current' => PersonSummaryResource::collection($currentPeople),
                'former' => PersonSummaryResource::collection($formerPeople),
            ],
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function headcount(Company $company): JsonResponse
    {
        $company->load('headcountSnapshots');

        $snapshots = $company->headcountSnapshots->sortBy('recorded_date')->values();

        $growth = null;
        if ($snapshots->count() >= 2) {
            $first = $snapshots->first()->headcount;
            $last = $snapshots->last()->headcount;
            if ($first > 0) {
                $growth = round((($last - $first) / $first) * 100, 1);
            }
        }

        return response()->json([
            'data' => [
                'company_slug' => $company->slug,
                'company_name' => $company->name,
                'current_headcount' => $company->current_headcount,
                'snapshots_count' => $snapshots->count(),
                'growth_percent' => $growth,
                'snapshots' => HeadcountSnapshotResource::collection($snapshots),
            ],
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    private function formatAmount(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return '$' . number_format($amount / 1_000_000_000, 1) . 'B';
        }
        if ($amount >= 1_000_000) {
            return '$' . number_format($amount / 1_000_000, 1) . 'M';
        }
        if ($amount >= 1_000) {
            return '$' . number_format($amount / 1_000, 0) . 'K';
        }
        return '$' . number_format($amount, 0);
    }
}
