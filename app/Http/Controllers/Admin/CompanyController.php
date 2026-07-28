<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies for admin.
     */
    public function index(Request $request): View
    {
        $query = Company::query()->orderBy('name');

        if ($search = $request->get('search')) {
            $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('name', 'like', "%{$escapedSearch}%")
                    ->orWhere('city', 'like', "%{$escapedSearch}%")
                    ->orWhere('country', 'like', "%{$escapedSearch}%");
            });
        }

        $companies = $query->paginate(50)->withQueryString();

        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): View
    {
        $categories = Company::CATEGORIES;

        return view('admin.companies.create', compact('categories'));
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'founded_date' => ['nullable', 'date'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', Rule::in(array_keys(Company::CATEGORIES))],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
        ]);

        // Generate slug from name
        $slug = Str::slug($validated['name']);

        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $validated['slug'] = $slug;

        $company = Company::create($validated);

        return redirect()
            ->route('admin.companies.edit', $company)
            ->with('success', 'Company created successfully.');
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company): View
    {
        $categories = Company::CATEGORIES;

        return view('admin.companies.edit', compact('company', 'categories'));
    }

    /**
     * Update the specified company in storage.
     */
    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('companies')->ignore($company->id)],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'founded_date' => ['nullable', 'date'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', Rule::in(array_keys(Company::CATEGORIES))],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
        ]);

        $company->update($validated);

        return redirect()
            ->route('admin.companies.edit', $company)
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy(Company $company): RedirectResponse
    {
        $companyName = $company->name;

        DB::transaction(function () use ($company) {
            $company->headcountSnapshots()->delete();
            $company->fundingRounds()->each(function ($round) {
                $round->investors()->detach();
                $round->delete();
            });
            $company->newsMentions()->delete();
            $company->people()->detach();
            $company->scheduledTaskExecutions()->delete();
            $company->delete();
        });

        return redirect()
            ->route('admin.companies.index')
            ->with('success', "Company \"{$companyName}\" deleted successfully.");
    }
}
