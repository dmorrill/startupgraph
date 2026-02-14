<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeadcountSnapshotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'headcount' => $this->headcount,
            'recorded_date' => $this->recorded_date?->format('Y-m-d'),
            'source' => $this->source,
        ];
    }
}
