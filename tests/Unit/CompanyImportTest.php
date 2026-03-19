<?php

namespace Tests\Unit;

use App\Models\CompanyImport;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_import_can_be_created(): void
    {
        $import = new CompanyImport([
            'source' => 'wikipedia',
            'batch_id' => 'test_batch',
            'companies_created' => 10,
            'status' => 'completed'
        ]);
        
        $this->assertEquals('wikipedia', $import->source);
        $this->assertEquals('test_batch', $import->batch_id);
        $this->assertEquals(10, $import->companies_created);
    }

    public function test_company_import_fillable_attributes(): void
    {
        $import = new CompanyImport();
        $expected = [
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
        
        $this->assertEquals($expected, $import->getFillable());
    }
}
