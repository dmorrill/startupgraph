<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FundingRound extends Model
{
    protected $fillable = [
        'company_id',
        'round_type',
        'amount',
        'currency',
        'announced_date',
        'pre_money_valuation',
        'source_url',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'pre_money_valuation' => 'decimal:2',
        'announced_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function investors(): BelongsToMany
    {
        return $this->belongsToMany(Investor::class, 'funding_round_investor')
            ->withPivot('is_lead')
            ->withTimestamps();
    }
}
