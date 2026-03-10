<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanySummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'category' => $this->category,
            'category_label' => $this->category_label,
            'location' => [
                'city' => $this->city,
                'country' => $this->country,
            ],
            'current_headcount' => $this->current_headcount,
            'total_funding' => $this->funding_rounds_sum_amount ? (float) $this->funding_rounds_sum_amount : null,
            'total_funding_formatted' => $this->funding_rounds_sum_amount ? $this->formatAmount($this->funding_rounds_sum_amount) : null,
            'latest_funding_date' => $this->latest_funding_date,
            'funding_rounds_count' => $this->funding_rounds_count ?? 0,
        ];
    }

    private function formatAmount(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return '$'.number_format($amount / 1_000_000_000, 1).'B';
        }
        if ($amount >= 1_000_000) {
            return '$'.number_format($amount / 1_000_000, 1).'M';
        }
        if ($amount >= 1_000) {
            return '$'.number_format($amount / 1_000, 0).'K';
        }

        return '$'.number_format($amount, 0);
    }
}
