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
        'headcount_history',
        'is_indie',
        'is_open_source',
        'github_stars',
        'solo_builder',
        'tech_stack',
        'submitted_by',
        'submission_url',
    ];

    protected $casts = [
        'founded_date' => 'date',
        'product_highlights' => 'array',
        'profile_refreshed_at' => 'datetime',
        'headcount_fetched_at' => 'datetime',
        'is_indie' => 'boolean',
        'headcount_history' => 'array',
        'is_open_source' => 'boolean',
        'solo_builder' => 'boolean',
        'github_stars' => 'integer',
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

    public function ossAlternatives(): BelongsToMany
    {
        return $this->belongsToMany(OpenSourceProject::class, 'company_oss_alternatives', 'company_id', 'oss_project_id')
            ->withPivot('relationship_type', 'notes')
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

    /**
     * Scope: companies with at least one funding round.
     */
    public function scopeFunded($query)
    {
        return $query->whereHas('fundingRounds');
    }

    /**
     * Scope: companies founded in the last N years.
     */
    public function scopeFoundedWithin($query, int $years)
    {
        return $query->where('founded_date', '>=', now()->subYears($years));
    }

    /**
     * Scope: companies in a specific category.
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
