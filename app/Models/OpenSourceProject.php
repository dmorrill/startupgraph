<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OpenSourceProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'github_url',
        'github_owner',
        'github_repo',
        'description',
        'stars',
        'forks',
        'watchers',
        'contributors_count',
        'primary_language',
        'topics',
        'license',
        'last_commit_at',
        'github_created_at',
        'category',
        'self_hostable',
        'has_commercial_version',
        'commercial_url',
    ];

    protected $casts = [
        'topics' => 'array',
        'stars' => 'integer',
        'forks' => 'integer',
        'watchers' => 'integer',
        'contributors_count' => 'integer',
        'last_commit_at' => 'datetime',
        'github_created_at' => 'datetime',
        'self_hostable' => 'boolean',
        'has_commercial_version' => 'boolean',
    ];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_oss_alternatives', 'oss_project_id', 'company_id')
            ->withPivot('relationship_type', 'notes')
            ->withTimestamps();
    }
}
