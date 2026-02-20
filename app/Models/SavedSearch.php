<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'query',
        'filters_json',
        'notify_on_new',
        'last_notified_at',
        'last_result_count',
    ];

    protected $casts = [
        'filters_json' => 'array',
        'notify_on_new' => 'boolean',
        'last_notified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Build a display name from query/filters if no name set.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        $parts = [];
        if ($this->query) {
            $parts[] = '"'.$this->query.'"';
        }
        if ($filters = $this->filters_json) {
            if (! empty($filters['category'])) {
                $parts[] = $filters['category'];
            }
            if (! empty($filters['country'])) {
                $parts[] = $filters['country'];
            }
        }

        return $parts ? implode(' · ', $parts) : 'All companies';
    }

    /**
     * Get the URL to re-run this search.
     */
    public function getSearchUrlAttribute(): string
    {
        $params = [];
        if ($this->query) {
            $params['search'] = $this->query;
        }
        if ($filters = $this->filters_json) {
            foreach (['category', 'country', 'funded_recent', 'funded_after', 'funded_before'] as $key) {
                if (! empty($filters[$key])) {
                    $params[$key] = $filters[$key];
                }
            }
        }

        return route('companies.index', $params);
    }
}
