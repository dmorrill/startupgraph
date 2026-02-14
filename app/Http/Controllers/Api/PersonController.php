<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Models\Person;
use Illuminate\Http\JsonResponse;

class PersonController extends Controller
{
    public function show(Person $person): JsonResponse
    {
        $person->load('companies');

        return response()->json([
            'data' => new PersonResource($person),
            'meta' => [
                'source' => 'startupgraph',
                'version' => '1.0',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
