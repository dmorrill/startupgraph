<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\FundingRound;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CompanyController extends Controller
{
    public function index(Request $request)
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

        if ($search = $request->get('search')) {
            $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('name', 'like', "%{$escapedSearch}%")
                  ->orWhere('description', 'like', "%{$escapedSearch}%")
                  ->orWhere('city', 'like', "%{$escapedSearch}%")
                  ->orWhere('country', 'like', "%{$escapedSearch}%");
            });
        }

        if ($country = $request->get('country')) {
            $query->where('country', $country);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
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
        $sortDirection = $request->get('direction', 'asc');

        $allowedSorts = ['name', 'founded_date', 'city', 'country', 'category', 'funding_rounds_sum_amount', 'latest_funding_date'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        $companies = $query->paginate(50)->withQueryString();
        $countries = Company::distinct()->pluck('country')->filter()->sort()->values();
        $categories = Company::CATEGORIES;

        return view('companies.index', compact('companies', 'countries', 'categories'));
    }

    public function exportCsv(Request $request)
    {
        $companies = $this->getFilteredQuery($request)->limit(1000)->get();

        $callback = function () use ($companies) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Website', 'Description', 'Category', 'City', 'State', 'Country', 'Founded', 'Employees', 'Total Funding', 'Funding Rounds']);

            foreach ($companies as $company) {
                fputcsv($handle, [
                    $company->name,
                    $company->website,
                    $company->description,
                    $company->category_label,
                    $company->city,
                    $company->state,
                    $company->country,
                    $company->founded_date?->format('Y-m-d'),
                    $company->current_headcount,
                    $company->funding_rounds_sum_amount,
                    $company->funding_rounds_count,
                ]);
            }
            fclose($handle);
        };

        $filename = 'startupgraph-export-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportJson(Request $request)
    {
        $companies = $this->getFilteredQuery($request)
            ->with(['fundingRounds', 'headcountSnapshots'])
            ->limit(1000)
            ->get()
            ->map(function ($company) {
                return [
                    'name' => $company->name,
                    'slug' => $company->slug,
                    'website' => $company->website,
                    'description' => $company->description,
                    'category' => $company->category_label,
                    'city' => $company->city,
                    'state' => $company->state,
                    'country' => $company->country,
                    'founded_date' => $company->founded_date?->format('Y-m-d'),
                    'current_headcount' => $company->current_headcount,
                    'total_funding' => $company->funding_rounds_sum_amount,
                    'funding_rounds' => $company->fundingRounds->map(fn ($r) => [
                        'round_type' => $r->round_type,
                        'amount' => $r->amount,
                        'announced_date' => $r->announced_date?->format('Y-m-d'),
                    ]),
                    'headcount_history' => $company->headcountSnapshots->sortBy('recorded_date')->values()->map(fn ($s) => [
                        'date' => $s->recorded_date->format('Y-m-d'),
                        'headcount' => $s->headcount,
                    ]),
                ];
            });

        $filename = 'startupgraph-export-' . now()->format('Y-m-d') . '.json';

        return response()->streamDownload(function () use ($companies) {
            echo json_encode(['companies' => $companies], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    private function getFilteredQuery(Request $request)
    {
        $query = Company::query()
            ->withSum('fundingRounds', 'amount')
            ->withCount('fundingRounds');

        if ($search = $request->get('search')) {
            $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('name', 'like', "%{$escapedSearch}%")
                  ->orWhere('description', 'like', "%{$escapedSearch}%")
                  ->orWhere('city', 'like', "%{$escapedSearch}%")
                  ->orWhere('country', 'like', "%{$escapedSearch}%");
            });
        }

        if ($country = $request->get('country')) {
            $query->where('country', $country);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        return $query->orderBy('name');
    }

    public function show(Company $company)
    {
        $company->load(['fundingRounds.investors', 'headcountSnapshots', 'newsMentions', 'people']);

        // Track view for logged-in users (upsert to avoid duplicates)
        if ($user = request()->user()) {
            \DB::table('company_views')->updateOrInsert(
                ['user_id' => $user->id, 'company_id' => $company->id],
                ['viewed_at' => now()]
            );
        }

        $isFollowing = $user ? $user->isFollowing($company) : false;

        return view('companies.show', compact('company', 'isFollowing'));
    }
}
