<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->notes()->with('company:id,name,slug')->latest();

        if ($slug = $request->get('company')) {
            $company = Company::where('slug', $slug)->firstOrFail();
            $query->where('company_id', $company->id);
        }

        $notes = $query->limit(100)->get();

        return $this->respond($notes->map(fn ($note) => $this->serialize($note)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company' => 'required|string',
            'title' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $company = Company::where('slug', $validated['company'])->firstOrFail();

        $note = $request->user()->notes()->create([
            'company_id' => $company->id,
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
            'created_via' => $request->user()?->currentAccessToken()?->name,
        ]);

        return $this->respond($this->serialize($note->load('company:id,name,slug')), 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $request->user()->notes()->findOrFail($id)->delete();

        return $this->respond(['deleted' => true]);
    }

    private function serialize(Note $note): array
    {
        return [
            'id' => $note->id,
            'company' => $note->company?->slug,
            'company_name' => $note->company?->name,
            'title' => $note->title,
            'body' => $note->body,
            'created_via' => $note->created_via,
            'created_at' => $note->created_at?->toIso8601String(),
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
