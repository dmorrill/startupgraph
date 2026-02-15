<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpenSourceProject;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OssProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = OpenSourceProject::query()->orderByDesc('stars');

        if ($search = $request->get('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                  ->orWhere('github_owner', 'like', "%{$escaped}%")
                  ->orWhere('description', 'like', "%{$escaped}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $projects = $query->paginate(50)->withQueryString();
        $categories = OpenSourceProject::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort();

        return view('admin.oss-projects.index', compact('projects', 'categories'));
    }

    public function show(OpenSourceProject $ossProject): View
    {
        $ossProject->load('companies');
        $companies = Company::orderBy('name')->get(['id', 'name']);

        return view('admin.oss-projects.show', compact('ossProject', 'companies'));
    }

    public function linkCompany(Request $request, OpenSourceProject $ossProject): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'relationship_type' => 'required|in:alternative_to,fork_of,built_on,commercial_version_of',
            'notes' => 'nullable|string|max:500',
        ]);

        $ossProject->companies()->syncWithoutDetaching([
            $validated['company_id'] => [
                'relationship_type' => $validated['relationship_type'],
                'notes' => $validated['notes'] ?? null,
            ],
        ]);

        return back()->with('success', 'Company linked successfully.');
    }

    public function unlinkCompany(OpenSourceProject $ossProject, Company $company): RedirectResponse
    {
        $ossProject->companies()->detach($company->id);

        return back()->with('success', 'Company unlinked.');
    }
}
