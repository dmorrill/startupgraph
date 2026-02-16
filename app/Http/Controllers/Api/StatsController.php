<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FundingRound;
use App\Models\OpenSourceProject;
use App\Models\Person;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'companies_count' => Company::count(),
                'people_count' => Person::count(),
                'funding_rounds_count' => FundingRound::count(),
                'total_funding_tracked' => (float) FundingRound::sum('amount'),
                'total_funding_formatted' => $this->formatAmount(FundingRound::sum('amount')),
                'oss_projects_count' => OpenSourceProject::count(),
                'categories' => Company::CATEGORIES,
                'countries' => Company::distinct()->pluck('country')->filter()->sort()->values(),
            ],
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'generated_at' => now()->toIso8601String(),
                'description' => 'StartupGraph is an open database of startup traction data, designed to be queried by AI agents.',
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
