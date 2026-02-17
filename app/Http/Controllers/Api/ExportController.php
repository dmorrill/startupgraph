<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FundingRound;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Export companies as CSV.
     *
     * GET /api/companies/export.csv
     * Supports same filters as the companies index endpoint.
     */
    public function csv(Request $request): StreamedResponse
    {
        $query = Company::query()
            ->withSum('fundingRounds', 'amount')
            ->withCount('fundingRounds')
            ->with(['latestFundingRound', 'tags'])
            ->addSelect(['latest_funding_date' => FundingRound::select('announced_date')
                ->whereColumn('company_id', 'companies.id')
                ->orderBy('announced_date', 'desc')
                ->limit(1)
            ]);

        // Apply same filters as index
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

        if ($fundingStage = $request->get('funding_stage')) {
            $stages = array_map('trim', explode(',', $fundingStage));
            $query->whereHas('latestFundingRound', fn ($q) => $q->whereIn('round_type', $stages));
        }

        $query->orderBy('name');

        $headers = [
            'name', 'slug', 'website', 'description', 'category', 'tags',
            'founded_date', 'city', 'state', 'country',
            'current_headcount', 'total_funding', 'funding_rounds_count',
            'latest_round_type', 'latest_round_date', 'linkedin_url',
            'status', 'is_indie', 'is_open_source',
        ];

        return response()->streamDownload(function () use ($query, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            $query->chunk(200, function ($companies) use ($handle) {
                foreach ($companies as $company) {
                    fputcsv($handle, [
                        $company->name,
                        $company->slug,
                        $company->website,
                        $company->description,
                        $company->category_label,
                        $company->tags->pluck('name')->join('; '),
                        $company->founded_date?->format('Y-m-d'),
                        $company->city,
                        $company->state,
                        $company->country,
                        $company->current_headcount,
                        $company->funding_rounds_sum_amount,
                        $company->funding_rounds_count,
                        $company->latestFundingRound?->round_type,
                        $company->latestFundingRound?->announced_date?->format('Y-m-d'),
                        $company->linkedin_url,
                        $company->status,
                        $company->is_indie ? 'Yes' : 'No',
                        $company->is_open_source ? 'Yes' : 'No',
                    ]);
                }
            });

            fclose($handle);
        }, 'startupgraph-companies-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
