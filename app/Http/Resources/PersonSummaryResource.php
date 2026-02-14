<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'role' => $this->pivot?->role,
            'is_current' => $this->pivot ? (bool) $this->pivot->is_current : null,
            'linkedin_url' => $this->linkedin_url,
        ];
    }
}
