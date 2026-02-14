<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundingRoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'round_type' => $this->round_type,
            'amount' => $this->amount ? (float) $this->amount : null,
            'amount_formatted' => $this->amount ? $this->formatAmount($this->amount) : null,
            'currency' => $this->currency,
            'announced_date' => $this->announced_date?->format('Y-m-d'),
            'pre_money_valuation' => $this->pre_money_valuation ? (float) $this->pre_money_valuation : null,
            'source_url' => $this->source_url,
            'investors' => $this->whenLoaded('investors', fn () =>
                $this->investors->map(fn ($investor) => [
                    'name' => $investor->name,
                    'slug' => $investor->slug,
                    'type' => $investor->type,
                    'is_lead' => (bool) $investor->pivot->is_lead,
                ])
            ),
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
