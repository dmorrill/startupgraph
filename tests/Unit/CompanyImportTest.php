<?php

use App\Models\CompanyImport;

test('company import has source', function () {
    $import = CompanyImport::create(['source' => 'wikipedia', 'status' => 'pending']);
    expect($import->source)->toBe('wikipedia');
});

test('company import tracks processed count', function () {
    $import = CompanyImport::create(['source' => 'crunchbase', 'status' => 'pending', 'total_processed' => 500]);
    expect($import->total_processed)->toBe(500);
});
