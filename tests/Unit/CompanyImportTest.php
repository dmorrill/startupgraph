<?php

namespace Tests\Unit;

use App\Models\CompanyImport;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_import_has_source()
    {
        $import = CompanyImport::factory()->create(['source' => 'wikipedia']);
        $this->assertEquals('wikipedia', $import->source);
    }

    public function test_company_import_tracks_total_processed()
    {
        $import = CompanyImport::factory()->create(['total_processed' => 500]);
        $this->assertEquals(500, $import->total_processed);
    }
}
