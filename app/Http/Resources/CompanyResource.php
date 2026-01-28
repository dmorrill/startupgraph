<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'website' => $this->website,
            'description' => $this->description,
            'category' => $this->category,
            'category_label' => $this->category_label,
            'founded_date' => $this->founded_date?->format('Y-m-d'),
            'location' => [
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
            ],
            'linkedin_url' => $this->linkedin_url,
            'current_headcount' => $this->current_headcount,
            'product_highlights' => $this->product_highlights,
            'total_funding' => $this->whenNotNull($this->funding_rounds_sum_amount, fn () => (float) $this->funding_rounds_sum_amount),
            'total_funding_formatted' => $this->whenNotNull($this->funding_rounds_sum_amount, fn () => $this->formatAmount($this->funding_rounds_sum_amount)),
            'funding_rounds_count' => $this->funding_rounds_count ?? $this->fundingRounds?->count() ?? 0,
            'latest_funding' => $this->whenLoaded('latestFundingRound', fn () =>
                $this->latestFundingRound ? [
                    'round_type' => $this->latestFundingRound->round_type,
                    'amount' => $this->latestFundingRound->amount ? (float) $this->latestFundingRound->amount : null,
                    'amount_formatted' => $this->latestFundingRound->amount ? $this->formatAmount($this->latestFundingRound->amount) : null,
                    'announced_date' => $this->latestFundingRound->announced_date?->format('Y-m-d'),
                ] : null
            ),
            'people_count' => $this->people?->count() ?? 0,
            'funding_rounds' => FundingRoundResource::collection($this->whenLoaded('fundingRounds')),
            'people' => PersonSummaryResource::collection($this->whenLoaded('people')),
            'headcount_snapshots' => HeadcountSnapshotResource::collection($this->whenLoaded('headcountSnapshots')),
            'profile_refreshed_at' => $this->profile_refreshed_at?->toIso8601String(),
        ];
    }

    private function formatAmount(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return '$' . number_format($amount / 1_000_000_000, 1) . 'B';
        }
        if ($amount >= 1_000_000) {
            return '$' . number_format($amount / 1_000_000, 1) . 'M';
        }
        if ($amount >= 1_000) {
            return '$' . number_format($amount / 1_000, 0) . 'K';
        }
        return '$' . number_format($amount, 0);
    }
}
