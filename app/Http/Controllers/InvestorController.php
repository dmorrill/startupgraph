<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use Illuminate\Http\Request;

class InvestorController extends Controller
{
    public function index(Request $request)
    {
        $query = Investor::query()
            ->withCount('fundingRounds');

        if ($search = $request->get('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $allowedSorts = ['name', 'type', 'portfolio_count', 'funding_rounds_count'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        $investors = $query->paginate(50)->withQueryString();
        $types = Investor::distinct()->whereNotNull('type')->pluck('type')->sort()->values();

        return view('investors.index', compact('investors', 'types'));
    }

    public function show(Investor $investor)
    {
        $investor->load(['fundingRounds' => function ($q) {
            $q->with('company')->orderByDesc('announced_date');
        }]);

        $companies = $investor->fundingRounds
            ->pluck('company')
            ->unique('id')
            ->sortBy('name')
            ->values();

        $totalInvested = $investor->fundingRounds->sum('amount');

        return view('investors.show', compact('investor', 'companies', 'totalInvested'));
    }
}
