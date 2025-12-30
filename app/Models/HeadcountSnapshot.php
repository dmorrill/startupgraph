<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeadcountSnapshot extends Model
{
    protected $fillable = [
        'company_id',
        'headcount',
        'recorded_date',
        'source',
    ];

    protected $casts = [
        'recorded_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
