<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Investor extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'website',
        'description',
    ];

    public function fundingRounds(): BelongsToMany
    {
        return $this->belongsToMany(FundingRound::class, 'funding_round_investor')
            ->withPivot('is_lead')
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
