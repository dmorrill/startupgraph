<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lists = $request->user()->lists()
            ->withCount('entries')
            ->orderBy('updated_at', 'desc')
            ->get();

        return $this->respond($lists->map(fn ($list) => $this->summarize($list)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $list = $request->user()->lists()->firstOrCreate(
            ['name' => $validated['name']],
            [
                'description' => $validated['description'] ?? null,
                'created_via' => $this->tokenName($request),
            ]
        );

        $list->loadCount('entries');

        return $this->respond($this->summarize($list), $list->wasRecentlyCreated ? 201 : 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $list = $request->user()->lists()->with('entries.company')->findOrFail($id);

        return $this->respond([
            'id' => $list->id,
            'name' => $list->name,
            'description' => $list->description,
            'created_via' => $list->created_via,
            'entries' => $list->entries->map(fn ($entry) => [
                'company' => [
                    'name' => $entry->company->name,
                    'slug' => $entry->company->slug,
                    'category' => $entry->company->category,
                    'city' => $entry->company->city,
                    'country' => $entry->company->country,
                ],
                'rationale' => $entry->rationale,
                'added_at' => $entry->created_at?->toIso8601String(),
                'created_via' => $entry->created_via,
            ]),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->user()->lists()->findOrFail($id)->delete();

        return $this->respond(['deleted' => true]);
    }

    public function addCompany(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'company' => 'required|string',
            'rationale' => 'nullable|string',
        ]);

        $list = $request->user()->lists()->findOrFail($id);
        $company = Company::where('slug', $validated['company'])->firstOrFail();

        $entry = $list->entries()->firstOrCreate(
            ['company_id' => $company->id],
            [
                'rationale' => $validated['rationale'] ?? null,
                'created_via' => $this->tokenName($request),
            ]
        );

        $list->touch();

        return $this->respond([
            'list_id' => $list->id,
            'company' => $company->slug,
            'rationale' => $entry->rationale,
        ], $entry->wasRecentlyCreated ? 201 : 200);
    }

    public function removeCompany(Request $request, int $id, string $slug): JsonResponse
    {
        $list = $request->user()->lists()->findOrFail($id);
        $company = Company::where('slug', $slug)->firstOrFail();

        $list->entries()->where('company_id', $company->id)->delete();

        return $this->respond(['deleted' => true]);
    }

    private function summarize(CompanyList $list): array
    {
        return [
            'id' => $list->id,
            'name' => $list->name,
            'description' => $list->description,
            'companies_count' => $list->entries_count ?? $list->entries()->count(),
            'created_via' => $list->created_via,
            'updated_at' => $list->updated_at?->toIso8601String(),
        ];
    }

    private function tokenName(Request $request): ?string
    {
        return $request->user()?->currentAccessToken()?->name;
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
