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
        try {
            // Validate request parameters
            $validated = $request->validate([
                'q' => ['nullable', 'string', 'max:255'],
                'country' => ['nullable', 'string', 'max:100'],
                'category' => ['nullable', 'string', 'max:100'],
                'funded_after' => ['nullable', 'date_format:Y-m-d'],
                'funded_before' => ['nullable', 'date_format:Y-m-d'],
                'funded_recent' => ['nullable', 'in:3m,6m,1y,2y'],
                'sort' => ['nullable', 'string', 'in:name,founded_date,city,country,category,funding_rounds_sum_amount,latest_funding_date'],
                'order' => ['nullable', 'string', 'in:asc,desc'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);

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
            if ($search = $validated['q'] ?? null) {
                // Escape special characters for LIKE query
                $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
                $query->where(function ($q) use ($escapedSearch) {
                    $q->where('name', 'like', "%{$escapedSearch}%")
                      ->orWhere('description', 'like', "%{$escapedSearch}%")
                      ->orWhere('city', 'like', "%{$escapedSearch}%")
                      ->orWhere('country', 'like', "%{$escapedSearch}%");
                });
            }

            if ($country = $validated['country'] ?? null) {
                $query->where('country', $country);
            }

            if ($category = $validated['category'] ?? null) {
                $query->where('category', $category);
            }

            // Date range filter for last fundraise
            if ($fundedAfter = $validated['funded_after'] ?? null) {
                $query->whereHas('fundingRounds', function ($q) use ($fundedAfter) {
                    $q->where('announced_date', '>=', $fundedAfter);
                });
            }

            if ($fundedBefore = $validated['funded_before'] ?? null) {
                $query->whereHas('fundingRounds', function ($q) use ($fundedBefore) {
                    $q->where('announced_date', '<=', $fundedBefore);
                });
            }

            // Validate date range logic
            if (isset($validated['funded_after']) && isset($validated['funded_before'])) {
                $afterDate = Carbon::parse($validated['funded_after']);
                $beforeDate = Carbon::parse($validated['funded_before']);
                
                if ($afterDate->isAfter($beforeDate)) {
                    return response()->json([
                        'error' => 'Invalid date range: funded_after must be before funded_before',
                        'code' => 'INVALID_DATE_RANGE'
                    ], 400);
                }
            }

            // Preset date filters
            if ($fundedRecent = $validated['funded_recent'] ?? null) {
                $dateThreshold = match ($fundedRecent) {
                    '3m' => Carbon::now()->subMonths(3),
                    '6m' => Carbon::now()->subMonths(6),
                    '1y' => Carbon::now()->subYear(),
                    '2y' => Carbon::now()->subYears(2),
                };
                $query->whereHas('fundingRounds', function ($q) use ($dateThreshold) {
                    $q->where('announced_date', '>=', $dateThreshold);
                });
            }

            $sortField = $validated['sort'] ?? 'name';
            $sortDirection = $validated['order'] ?? 'asc';
            $query->orderBy($sortField, $sortDirection);

            $perPage = $validated['per_page'] ?? 50;
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'code' => 'VALIDATION_ERROR',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in CompanyController@index: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function show(Company $company): JsonResponse
    {
        try {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Company not found',
                'code' => 'COMPANY_NOT_FOUND'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in CompanyController@show: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function funding(Company $company): JsonResponse
    {
        try {
            $company->load('fundingRounds.investors');

            $fundingRounds = $company->fundingRounds->sortByDesc('announced_date')->values();
            $totalAmount = $fundingRounds->sum('amount');

            return response()->json([
                'data' => [
                    'company_slug' => $company->slug,
                    'company_name' => $company->name,
                    'total_funding' => (float) $totalAmount,
                    'total_funding_formatted' => $this->formatAmount($totalAmount),
                    'rounds_count' => $fundingRounds->count(),
                    'funding_rounds' => FundingRoundResource::collection($fundingRounds),
                ],
                'meta' => [
                    'source' => 'startupgraph',
                    'version' => '1.0',
                    'generated_at' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Company not found',
                'code' => 'COMPANY_NOT_FOUND'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in CompanyController@funding: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
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
