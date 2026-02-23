<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $savedSearches = $user->savedSearches()->latest()->take(10)->get();
        $followedCompanies = $user->followedCompanies()->take(12)->get();
        $recentlyViewed = $user->recentlyViewedCompanies()->take(10)->get();
        $isNewUser = $user->created_at->gt(now()->subMinutes(5)) || session('just_registered');

        return view('dashboard', compact('savedSearches', 'followedCompanies', 'recentlyViewed', 'isNewUser'));
    }
}
