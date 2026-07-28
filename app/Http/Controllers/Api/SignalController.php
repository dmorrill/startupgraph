<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Signal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->signals()->with('company:id,name,slug')->latest();

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $signals = $query->limit((int) min($request->get('limit', 50), 100))->get();

        return $this->respond($signals->map(fn ($signal) => $this->serialize($signal)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'company' => 'nullable|string',
            'payload' => 'nullable|array',
        ]);

        $companyId = null;
        if (! empty($validated['company'])) {
            $companyId = Company::where('slug', $validated['company'])->firstOrFail()->id;
        }

        $signal = $request->user()->signals()->create([
            'company_id' => $companyId,
            'type' => Signal::TYPE_CUSTOM,
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'payload' => $validated['payload'] ?? null,
            'created_via' => $request->user()?->currentAccessToken()?->name,
        ]);

        return $this->respond($this->serialize($signal->load('company:id,name,slug')), 201);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $signal = $request->user()->signals()->findOrFail($id);
        $signal->update(['read_at' => now()]);

        return $this->respond($this->serialize($signal));
    }

    private function serialize(Signal $signal): array
    {
        return [
            'id' => $signal->id,
            'type' => $signal->type,
            'title' => $signal->title,
            'body' => $signal->body,
            'company' => $signal->company?->slug,
            'company_name' => $signal->company?->name,
            'payload' => $signal->payload,
            'created_via' => $signal->created_via,
            'read_at' => $signal->read_at?->toIso8601String(),
            'created_at' => $signal->created_at?->toIso8601String(),
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
