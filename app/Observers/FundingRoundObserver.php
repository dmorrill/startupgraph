<?php

namespace App\Observers;

use App\Models\FundingRound;
use App\Models\Signal;

/**
 * Fan a new funding round out as a signal to every user following the
 * company. Runs on pipeline imports and agent writes alike.
 */
class FundingRoundObserver
{
    public function created(FundingRound $round): void
    {
        $company = $round->company;
        if (! $company) {
            return;
        }

        $amount = $round->amount ? '$'.number_format((float) $round->amount) : 'undisclosed amount';
        $type = $round->round_type ?: 'funding round';

        $signals = $company->followers()->pluck('users.id')->map(fn ($userId) => [
            'user_id' => $userId,
            'company_id' => $company->id,
            'type' => Signal::TYPE_FUNDING_ROUND,
            'title' => "{$company->name} raised a {$type} ({$amount})",
            'body' => $round->source_url,
            'payload' => json_encode([
                'round_type' => $round->round_type,
                'amount' => $round->amount,
                'announced_date' => optional($round->announced_date)->toDateString(),
            ]),
            'created_via' => 'pipeline',
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        if ($signals) {
            Signal::insert($signals);
        }
    }
}
