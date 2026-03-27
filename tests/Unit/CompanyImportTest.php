<?php

namespace Tests\Unit;

use App\Models\CompanyImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_import_has_source(): void
    {
        $import = CompanyImport::factory()->create(['source' => 'wikipedia']);
        $this->assertEquals('wikipedia', $import->source);
    }

    public function test_company_import_tracks_counts(): void
    {
        $import = CompanyImport::factory()->create([
            'companies_created' => 100,
            'companies_updated' => 50,
            'companies_skipped' => 10
        ]);
        $this->assertEquals(100, $import->companies_created);
        $this->assertEquals(50, $import->companies_updated);
        $this->assertEquals(10, $import->companies_skipped);
    }
}
