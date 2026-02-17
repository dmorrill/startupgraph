<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'description'];

    public const TYPES = [
        'category' => 'Category',
        'industry' => 'Industry',
        'business_model' => 'Business Model',
    ];

    public const DEFAULT_TAGS = [
        'category' => [
            'SaaS', 'Marketplace', 'Fintech', 'Healthcare', 'AI/ML',
            'Developer Tools', 'Consumer', 'Enterprise', 'Climate/Energy',
            'Robotics', 'Space', 'Defense', 'Biotech', 'Crypto/Web3',
            'EdTech', 'Gaming', 'Hardware', 'Security', 'Social',
        ],
        'business_model' => [
            'B2B', 'B2C', 'B2B2C', 'D2C', 'Platform', 'API-first',
            'Open Source', 'Freemium', 'Subscription',
        ],
    ];

    protected static function booted(): void
    {
        static::creating(function (Tag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_tag')
            ->withTimestamps();
    }
}
