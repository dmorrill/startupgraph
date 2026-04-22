<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'description',
        'builder_name',
        'tech_stack',
        'submitter_email',
        'source_url',
        'status',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
