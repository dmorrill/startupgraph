<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyImport extends Model
{
    protected $fillable = [
        'source',
        'batch_id',
        'companies_created',
        'companies_updated',
        'companies_skipped',
        'total_processed',
        'status',
        'last_page',
        'last_offset',
        'metadata',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
