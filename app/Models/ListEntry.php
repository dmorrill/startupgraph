<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'company_id',
        'rationale',
        'created_via',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(CompanyList::class, 'list_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
