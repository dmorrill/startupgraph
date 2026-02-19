<?php

namespace Tests\Unit\Services\BulkImport;

use App\Models\Company;
use App\Services\BulkImport\WikipediaAcquisitionsImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WikipediaAcquisitionsImporterTest extends TestCase
{
    use RefreshDatabase;

    private function fakeWikipediaResponse(string $wikitext): void
    {
        Http::fake([
            'en.wikipedia.org/*' => Http::response([
                'parse' => [
                    'wikitext' => ['*' => $wikitext],
                ],
            ]),
        ]);
    }

    public function test_source_name(): void
    {
        $importer = new WikipediaAcquisitionsImporter();
        $this->assertEquals('wikipedia-acquisitions', $importer->source());
    }

    public function test_imports_acquisitions_from_wiki_table(): void
    {
        $wikitext = <<<'WIKI'
{| class="wikitable"
! Company
! Date
! Price
! Description
|-
| [[YouTube]]
| October 9, 2006
| US$1.65 billion
| Video sharing platform
|-
| [[Waze]]
| June 11, 2013
| US$1.1 billion
| GPS navigation software
|-
|}
WIKI;

        $this->fakeWikipediaResponse($wikitext);

        $importer = new WikipediaAcquisitionsImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);

        $youtube = Company::where('name', 'YouTube')->first();
        $this->assertNotNull($youtube);
        $this->assertEquals('acquired', $youtube->status);

        $waze = Company::where('name', 'Waze')->first();
        $this->assertNotNull($waze);
        $this->assertEquals('acquired', $waze->status);
        $this->assertStringContains('US$1.1 billion', $waze->description);
    }

    public function test_skips_empty_names_and_totals(): void
    {
        $wikitext = <<<'WIKI'
{| class="wikitable"
! Company
! Date
|-
| Total
| 2023
|-
| =Section=
| 2023
|-
|}
WIKI;

        $this->fakeWikipediaResponse($wikitext);

        $importer = new WikipediaAcquisitionsImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);
        $this->assertEquals(0, Company::count());
    }

    public function test_handles_failed_api_response(): void
    {
        Http::fake([
            'en.wikipedia.org/*' => Http::response(null, 500),
        ]);

        $importer = new WikipediaAcquisitionsImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);
        $this->assertEquals(0, $result->companies_created);
    }

    public function test_creates_import_log(): void
    {
        $this->fakeWikipediaResponse('');

        $importer = new WikipediaAcquisitionsImporter();
        $result = $importer->start();

        $this->assertDatabaseHas('company_imports', [
            'source' => 'wikipedia-acquisitions',
            'status' => 'completed',
        ]);
    }

    /**
     * Helper to assert string contains substring (PHPUnit 10 compat).
     */
    private function assertStringContains(string $needle, ?string $haystack): void
    {
        $this->assertNotNull($haystack);
        $this->assertStringContainsString($needle, $haystack);
    }
}
