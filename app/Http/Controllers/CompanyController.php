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

        $allowedSorts = ['name', 'founded_date', 'city', 'country', 'funding_rounds_sum_amount', 'latest_funding_date'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        $companies = $query->paginate(50)->withQueryString();
        $countries = Company::distinct()->pluck('country')->filter()->sort()->values();

        return view('companies.index', compact('companies', 'countries'));
    }

    public function show(Company $company)
    {
        $company->load(['fundingRounds.investors', 'headcountSnapshots', 'newsMentions']);

        return view('companies.show', compact('company'));
    }
}
