<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A saved query over the company graph, with a stored snapshot of the
 * last run's results so the iOS app can render it without re-querying.
 */
class Screen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'criteria',
        'snapshot',
        'result_count',
        'refreshed_at',
        'created_via',
    ];

    protected $casts = [
        'criteria' => 'array',
        'snapshot' => 'array',
        'refreshed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
