<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'bio' => $this->bio,
            'linkedin_url' => $this->linkedin_url,
            'twitter_url' => $this->twitter_url,
            'photo_url' => $this->photo_url,
            'companies' => $this->whenLoaded('companies', fn () => $this->companies->map(fn ($company) => [
                'slug' => $company->slug,
                'name' => $company->name,
                'role' => $company->pivot->role,
                'is_current' => (bool) $company->pivot->is_current,
                'started_at' => $company->pivot->started_at,
                'ended_at' => $company->pivot->ended_at,
            ])
            ),
        ];
    }
}
