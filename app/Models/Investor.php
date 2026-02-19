<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Investor extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'type',
        'website',
        'description',
        'portfolio_count',
    ];

    public function companies(): \Illuminate\Support\Collection
    {
        return $this->fundingRounds()
            ->with('company')
            ->get()
            ->pluck('company')
            ->unique('id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'vc' => 'Venture Capital',
            'angel' => 'Angel Investor',
            'corporate' => 'Corporate VC',
            'accelerator' => 'Accelerator',
            'pe' => 'Private Equity',
            default => $this->type ?? 'Unknown',
        };
    }

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
