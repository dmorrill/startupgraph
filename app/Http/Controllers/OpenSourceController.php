<?php

namespace App\Http\Controllers;

use App\Models\OpenSourceProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpenSourceController extends Controller
{
    public function index(Request $request): View
    {
        $query = OpenSourceProject::query()->orderByDesc('stars');

        if ($search = $request->get('search')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%")
                    ->orWhere('primary_language', 'like', "%{$escaped}%");
            });
        }

        if ($language = $request->get('language')) {
            $query->where('primary_language', $language);
        }

        if ($request->boolean('self_hostable')) {
            $query->where('self_hostable', true);
        }

        $projects = $query->paginate(50)->withQueryString();
        $languages = OpenSourceProject::whereNotNull('primary_language')
            ->distinct()
            ->pluck('primary_language')
            ->sort();

        return view('open-source.index', compact('projects', 'languages'));
    }
}
