<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A user-curated collection of companies (named CompanyList because
 * "List" is a reserved word in PHP). Table: lists.
 */
class CompanyList extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'created_via',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ListEntry::class, 'list_id');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'list_entries', 'list_id', 'company_id')
            ->withPivot(['rationale', 'created_via'])
            ->withTimestamps();
    }
}
