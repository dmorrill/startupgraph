<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenSourceProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OssProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = OpenSourceProject::query();

        if ($q = $request->get('q')) {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $q);
            $query->where(function ($qb) use ($escaped) {
                $qb->where('name', 'like', "%{$escaped}%")
                   ->orWhere('description', 'like', "%{$escaped}%");
            });
        }

        if ($request->get('language')) {
            $query->where('primary_language', $request->get('language'));
        }

        $sortField = $request->get('sort', 'stars');
        $allowed = ['name', 'stars', 'forks', 'created_at'];
        $sort = in_array($sortField, $allowed) ? $sortField : 'stars';

        $projects = $query->orderByDesc($sort)
            ->paginate(min($request->integer('per_page', 25), 100));

        return response()->json($projects);
    }

    public function show(OpenSourceProject $ossProject): JsonResponse
    {
        $ossProject->load('companies');

        return response()->json(['data' => $ossProject]);
    }
}
