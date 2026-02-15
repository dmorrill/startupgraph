<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $query = CompanySubmission::query()->orderByDesc('created_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        } else {
            $query->where('status', 'pending');
        }

        $submissions = $query->paginate(50)->withQueryString();

        return view('admin.submissions.index', compact('submissions'));
    }

    public function approve(CompanySubmission $submission): RedirectResponse
    {
        $slug = Str::slug($submission->name);
        $original = $slug;
        $counter = 1;
        while (Company::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        Company::create([
            'name' => $submission->name,
            'slug' => $slug,
            'website' => $submission->url,
            'description' => $submission->description,
            'is_indie' => true,
            'tech_stack' => $submission->tech_stack,
            'submitted_by' => $submission->builder_name,
            'submission_url' => $submission->source_url,
        ]);

        $submission->update(['status' => 'approved']);

        return redirect()->route('admin.submissions.index')
            ->with('success', "'{$submission->name}' approved and added to companies.");
    }

    public function reject(CompanySubmission $submission): RedirectResponse
    {
        $submission->update(['status' => 'rejected']);

        return redirect()->route('admin.submissions.index')
            ->with('success', "'{$submission->name}' rejected.");
    }
}
