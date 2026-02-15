<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;
    public const CATEGORIES = [
        'ai_ml' => 'AI/ML',
        'fintech' => 'Fintech',
        'enterprise' => 'Enterprise',
        'healthcare' => 'Healthcare',
        'robotics' => 'Robotics',
        'space' => 'Space',
        'climate' => 'Climate/Energy',
        'consumer' => 'Consumer',
        'developer_tools' => 'Developer Tools',
        'defense' => 'Defense',
    ];

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
        'category',
        'linkedin_url',
        'current_headcount',
        'profile_refreshed_at',
        'headcount_fetched_at',
        'headcount_fetch_day',
    ];

    protected $casts = [
        'founded_date' => 'date',
        'product_highlights' => 'array',
        'profile_refreshed_at' => 'datetime',
        'headcount_fetched_at' => 'datetime',
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

    public function scheduledTaskExecutions(): HasMany
    {
        return $this->hasMany(ScheduledTaskExecution::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getCategoryLabelAttribute(): ?string
    {
        return $this->category ? (self::CATEGORIES[$this->category] ?? $this->category) : null;
    }
}
