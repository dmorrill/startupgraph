<?php

namespace App\Http\Controllers;

use App\Models\CompanySubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function create(): View
    {
        return view('submissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'builder_name' => ['nullable', 'string', 'max:255'],
            'tech_stack' => ['nullable', 'string', 'max:1000'],
            'submitter_email' => ['nullable', 'email', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:255'],
        ]);

        CompanySubmission::create($validated);

        return redirect()->route('submissions.create')
            ->with('success', 'Thanks! Your project has been submitted for review.');
    }
}
