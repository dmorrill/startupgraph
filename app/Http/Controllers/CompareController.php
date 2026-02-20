<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index(Request $request)
    {
        $slugs = array_filter(array_slice(
            explode(',', $request->get('companies', '')),
            0,
            4
        ));

        $companies = collect();
        if (! empty($slugs)) {
            $companies = Company::whereIn('slug', $slugs)
                ->with(['fundingRounds.investors', 'headcountSnapshots'])
                ->withSum('fundingRounds', 'amount')
                ->withCount('fundingRounds')
                ->get()
                ->sortBy(function ($c) use ($slugs) {
                    return array_search($c->slug, $slugs);
                })
                ->values();
        }

        $allCompanies = Company::orderBy('name')
            ->select('id', 'name', 'slug')
            ->get();

        $chartData = $companies->map(function ($c) {
            return [
                'name' => $c->name,
                'slug' => $c->slug,
                'headcount' => $c->headcountSnapshots->sortBy('recorded_date')->values()->map(function ($s) {
                    return [
                        'date' => $s->recorded_date->format('Y-m-d'),
                        'headcount' => $s->headcount,
                    ];
                }),
                'funding' => $c->fundingRounds->where('amount', '>', 0)->sortBy('announced_date')->values()->map(function ($r) {
                    return [
                        'date' => $r->announced_date ? $r->announced_date->format('Y-m-d') : null,
                        'amount' => $r->amount,
                        'round_type' => $r->round_type,
                    ];
                }),
            ];
        })->values();

        return view('companies.compare', compact('companies', 'allCompanies', 'slugs', 'chartData'));
    }
}
