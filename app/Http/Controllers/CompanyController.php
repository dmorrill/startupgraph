<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

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

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSorts = ['name', 'founded_date', 'city', 'country'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        $companies = $query->paginate(24)->withQueryString();
        $countries = Company::distinct()->pluck('country')->filter()->sort()->values();

        return view('companies.index', compact('companies', 'countries'));
    }

    public function show(Company $company)
    {
        $company->load(['fundingRounds.investors', 'headcountSnapshots', 'newsMentions']);

        return view('companies.show', compact('company'));
    }
}
