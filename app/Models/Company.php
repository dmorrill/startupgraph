<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'website',
        'description',
        'product_highlights',
        'founded_date',
        'city',
        'state',
        'country',
        'linkedin_url',
        'current_headcount',
        'profile_refreshed_at',
    ];

    protected $casts = [
        'founded_date' => 'date',
        'product_highlights' => 'array',
        'profile_refreshed_at' => 'datetime',
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

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'company_person')
            ->withPivot('role', 'is_current', 'started_at', 'ended_at')
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
