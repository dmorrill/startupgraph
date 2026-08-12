<?php

namespace App\Observers;

use App\Models\HeadcountSnapshot;
use App\Models\Signal;

/**
 * Signal meaningful headcount changes to followers when a new snapshot
 * lands. Small wobbles (<5%) stay off the feed.
 */
class HeadcountSnapshotObserver
{
    public function created(HeadcountSnapshot $snapshot): void
    {
        $company = $snapshot->company;
        if (! $company || ! $snapshot->headcount) {
            return;
        }

        $previous = $company->headcountSnapshots()
            ->where('id', '!=', $snapshot->id)
            ->orderByDesc('recorded_date')
            ->first();

        if (! $previous || ! $previous->headcount) {
            return;
        }

        $changePercent = (($snapshot->headcount - $previous->headcount) / $previous->headcount) * 100;

        if (abs($changePercent) < 5) {
            return;
        }

        $direction = $changePercent > 0 ? 'grew' : 'shrank';
        $formatted = round(abs($changePercent), 1);

        $signals = $company->followers()->pluck('users.id')->map(fn ($userId) => [
            'user_id' => $userId,
            'company_id' => $company->id,
            'type' => Signal::TYPE_HEADCOUNT_CHANGE,
            'title' => "{$company->name} {$direction} {$formatted}% ({$previous->headcount} → {$snapshot->headcount})",
            'payload' => json_encode([
                'previous' => $previous->headcount,
                'current' => $snapshot->headcount,
                'change_percent' => round($changePercent, 1),
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
