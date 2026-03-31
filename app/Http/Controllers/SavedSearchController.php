<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedSearchController extends Controller
{
    public function index()
    {
        try {
            $savedSearches = Auth::user()->savedSearches()
                ->orderByDesc('updated_at')
                ->get();

            return view('saved-searches.index', compact('savedSearches'));
        } catch (\Exception $e) {
            \Log::error('Error in SavedSearchController@index: ' . $e->getMessage());
            return back()->with('error', 'Unable to load saved searches. Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'search' => 'nullable|string|max:500',
                'notify_on_new' => 'boolean',
                'category' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'funded_recent' => 'nullable|in:3m,6m,1y,2y',
                'funded_after' => 'nullable|date_format:Y-m-d',
                'funded_before' => 'nullable|date_format:Y-m-d',
            ]);

            $filters = [];
            foreach (['category', 'country', 'funded_recent', 'funded_after', 'funded_before'] as $key) {
                if (!empty($validated[$key])) {
                    $filters[$key] = $validated[$key];
                }
            }

            $savedSearch = Auth::user()->savedSearches()->create([
                'name' => $validated['name'] ?? null,
                'query' => $validated['search'] ?? null,
                'filters_json' => !empty($filters) ? $filters : null,
                'notify_on_new' => $validated['notify_on_new'] ?? false,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'id' => $savedSearch->id, 
                    'message' => 'Search saved',
                    'data' => $savedSearch
                ], 201);
            }

            return back()->with('success', 'Search saved to your watchlist!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'details' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Error in SavedSearchController@store: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Unable to save search. Please try again.'
                ], 500);
            }
            
            return back()->with('error', 'Unable to save search. Please try again.');
        }
    }

    public function update(Request $request, SavedSearch $savedSearch)
    {
        $this->authorize('update', $savedSearch);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'notify_on_new' => 'boolean',
        ]);

        $savedSearch->update($request->only(['name', 'notify_on_new']));

        return back()->with('success', 'Saved search updated.');
    }

    public function destroy(SavedSearch $savedSearch)
    {
        $this->authorize('delete', $savedSearch);

        $savedSearch->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Deleted']);
        }

        return back()->with('success', 'Saved search removed.');
    }
}
