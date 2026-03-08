<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyFollowController extends Controller
{
    public function store(Request $request, Company $company)
    {
        $request->user()->followedCompanies()->syncWithoutDetaching([$company->id]);

        return back()->with('status', "Now following {$company->name}");
    }

    public function destroy(Request $request, Company $company)
    {
        $request->user()->followedCompanies()->detach($company->id);

        return back()->with('status', "Unfollowed {$company->name}");
    }
}
