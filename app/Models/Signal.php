<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An event on the user's feed: a funding round on a followed company,
 * a headcount jump, or a custom signal logged by an agent.
 */
class Signal extends Model
{
    use HasFactory;

    public const TYPE_FUNDING_ROUND = 'funding_round';

    public const TYPE_HEADCOUNT_CHANGE = 'headcount_change';

    public const TYPE_CUSTOM = 'custom';

    protected $fillable = [
        'user_id',
        'company_id',
        'type',
        'title',
        'body',
        'payload',
        'created_via',
        'read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
