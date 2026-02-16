<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySubmission extends Model
{
    protected $fillable = [
        'name',
        'url',
        'description',
        'builder_name',
        'tech_stack',
        'submitter_email',
        'source_url',
        'status',
    ];
}
