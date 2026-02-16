<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsMention extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'url',
        'source',
        'published_date',
        'summary',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
