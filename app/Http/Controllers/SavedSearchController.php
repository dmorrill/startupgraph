<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedSearchController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $savedSearches = Auth::user()->savedSearches()
            ->orderByDesc('updated_at')
            ->get();

        return view('saved-searches.index', compact('savedSearches'));
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'notify_on_new' => 'boolean',
        ]);

        $filters = [];
        foreach (['category', 'country', 'funded_recent', 'funded_after', 'funded_before'] as $key) {
            if ($request->filled($key)) {
                $filters[$key] = $request->get($key);
            }
        }

        $savedSearch = Auth::user()->savedSearches()->create([
            'name' => $request->get('name'),
            'query' => $request->get('search'),
            'filters_json' => !empty($filters) ? $filters : null,
            'notify_on_new' => $request->boolean('notify_on_new', false),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['id' => $savedSearch->id, 'message' => 'Search saved'], 201);
        }

        return back()->with('success', 'Search saved to your watchlist!');
    }

    public function update(Request $request, SavedSearch $savedSearch): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $savedSearch);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'notify_on_new' => 'boolean',
        ]);

        $savedSearch->update($request->only(['name', 'notify_on_new']));

        return back()->with('success', 'Saved search updated.');
    }

    public function destroy(SavedSearch $savedSearch): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $savedSearch);

        $savedSearch->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Deleted']);
        }

        return back()->with('success', 'Saved search removed.');
    }
}
