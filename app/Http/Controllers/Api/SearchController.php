<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanySummaryResource;
use App\Http\Resources\PersonSummaryResource;
use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min((int) $request->get('limit', 10), 50);

        if (strlen($query) < 2) {
            return response()->json([
                'data' => [
                    'companies' => [],
                    'people' => [],
                ],
                'meta' => [
                    'source' => 'startupgraph',
                    'version' => '1.0',
                    'generated_at' => now()->toIso8601String(),
                    'query' => $query,
                    'message' => 'Query must be at least 2 characters',
                ],
            ]);
        }

        // Search companies
        $companies = Company::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('city', 'like', "%{$query}%")
                  ->orWhere('country', 'like', "%{$query}%");
            })
            ->withSum('fundingRounds', 'amount')
            ->withCount('fundingRounds')
            ->addSelect(['latest_funding_date' => FundingRound::select('announced_date')
                ->whereColumn('company_id', 'companies.id')
                ->orderBy('announced_date', 'desc')
                ->limit(1)
            ])
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["{$query}%"])
            ->orderBy('name')
            ->limit($limit)
            ->get();

        // Search people
        $people = Person::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('bio', 'like', "%{$query}%");
            })
            ->with(['companies' => fn ($q) => $q->wherePivot('is_current', true)->limit(1)])
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["{$query}%"])
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => [
                'companies' => CompanySummaryResource::collection($companies),
                'people' => $people->map(fn ($person) => [
                    'slug' => $person->slug,
                    'name' => $person->name,
                    'current_company' => $person->companies->first()?->name,
                    'current_role' => $person->companies->first()?->pivot?->role,
                    'linkedin_url' => $person->linkedin_url,
                ]),
            ],
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'generated_at' => now()->toIso8601String(),
                'query' => $query,
                'companies_count' => $companies->count(),
                'people_count' => $people->count(),
            ],
        ]);
    }
}
