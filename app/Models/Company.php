<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'website',
        'description',
        'founded_date',
        'city',
        'state',
        'country',
        'linkedin_url',
        'current_headcount',
    ];

    protected $casts = [
        'founded_date' => 'date',
    ];

    public function headcountSnapshots(): HasMany
    {
        return $this->hasMany(HeadcountSnapshot::class);
    }

    public function fundingRounds(): HasMany
    {
        return $this->hasMany(FundingRound::class);
    }

    public function latestFundingRound(): HasOne
    {
        return $this->hasOne(FundingRound::class)->latestOfMany('announced_date');
    }

    public function newsMentions(): HasMany
    {
        return $this->hasMany(NewsMention::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
