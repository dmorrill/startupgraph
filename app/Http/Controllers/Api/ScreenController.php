<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Screen;
use App\Services\CompanyQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScreenController extends Controller
{
    public function __construct(private CompanyQueryService $companyQuery) {}

    public function index(Request $request): JsonResponse
    {
        $screens = $request->user()->screens()->orderBy('updated_at', 'desc')->get();

        return $this->respond($screens->map(fn ($screen) => $this->summarize($screen)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'criteria' => 'required|array',
            'criteria.q' => 'nullable|string',
            'criteria.category' => 'nullable|string',
            'criteria.country' => 'nullable|string',
            'criteria.funded_after' => 'nullable|date',
            'criteria.funded_before' => 'nullable|date',
            'criteria.funded_recent' => ['nullable', Rule::in(['3m', '6m', '1y', '2y'])],
            'criteria.sort' => ['nullable', Rule::in(CompanyQueryService::ALLOWED_SORTS)],
            'criteria.order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $screen = $request->user()->screens()->updateOrCreate(
            ['name' => $validated['name']],
            [
                'description' => $validated['description'] ?? null,
                'criteria' => CompanyQueryService::sanitizeCriteria($validated['criteria']),
                'created_via' => $request->user()?->currentAccessToken()?->name,
            ]
        );

        $this->runScreen($screen);

        return $this->respond($this->summarize($screen), $screen->wasRecentlyCreated ? 201 : 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $screen = $request->user()->screens()->findOrFail($id);

        return $this->respond([
            'id' => $screen->id,
            'name' => $screen->name,
            'description' => $screen->description,
            'criteria' => $screen->criteria,
            'result_count' => $screen->result_count,
            'refreshed_at' => $screen->refreshed_at?->toIso8601String(),
            'results' => $screen->snapshot,
        ]);
    }

    public function refresh(Request $request, int $id): JsonResponse
    {
        $screen = $request->user()->screens()->findOrFail($id);
        $this->runScreen($screen);

        return $this->respond($this->summarize($screen));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->user()->screens()->findOrFail($id)->delete();

        return $this->respond(['deleted' => true]);
    }

    private function runScreen(Screen $screen): void
    {
        $companies = $this->companyQuery->build($screen->criteria ?? [])
            ->limit(100)
            ->get();

        $screen->update([
            'snapshot' => $companies->map(fn ($company) => [
                'name' => $company->name,
                'slug' => $company->slug,
                'category' => $company->category,
                'city' => $company->city,
                'country' => $company->country,
                'current_headcount' => $company->current_headcount,
                'total_raised' => (float) ($company->funding_rounds_sum_amount ?? 0),
                'latest_funding_date' => $company->latest_funding_date,
            ])->values()->all(),
            'result_count' => $companies->count(),
            'refreshed_at' => now(),
        ]);
    }

    private function summarize(Screen $screen): array
    {
        return [
            'id' => $screen->id,
            'name' => $screen->name,
            'description' => $screen->description,
            'criteria' => $screen->criteria,
            'result_count' => $screen->result_count,
            'refreshed_at' => $screen->refreshed_at?->toIso8601String(),
            'created_via' => $screen->created_via,
        ];
    }

    private function respond(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'generated_at' => now()->toIso8601String(),
            ],
        ], $status);
    }
}
