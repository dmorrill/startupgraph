<?php

namespace Tests\Unit\Services\BulkImport;

use App\Models\Company;
use App\Services\BulkImport\WikipediaUnicornImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WikipediaUnicornImporterTest extends TestCase
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

    private function unicornTable(array $rows): string
    {
        $wikitext = "{| class=\"wikitable sortable\"\n";
        $wikitext .= "!Company\n!Valuation\n!Country\n!Industry\n";
        foreach ($rows as $row) {
            $wikitext .= "|-\n";
            $wikitext .= "|{$row[0]}\n|{$row[1]}\n|{$row[2]}\n|{$row[3]}\n";
        }
        $wikitext .= "|-\n|}";
        return $wikitext;
    }

    public function test_source_name(): void
    {
        $importer = new WikipediaUnicornImporter();
        $this->assertEquals('wikipedia', $importer->source());
    }

    public function test_imports_unicorns_from_wiki_table(): void
    {
        $wikitext = $this->unicornTable([
            ['[[Stripe (company)|Stripe]]', '95', 'United States', '[[Fintech]]'],
            ['[[Canva]]', '40', 'Australia', '[[Software]]'],
        ]);

        $this->fakeWikipediaResponse($wikitext);

        $importer = new WikipediaUnicornImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);

        $stripe = Company::where('name', 'Stripe')->first();
        $this->assertNotNull($stripe);
        $this->assertEquals('operating', $stripe->status);
        $this->assertEquals('US', $stripe->country);
        $this->assertEquals('fintech', $stripe->category);

        $canva = Company::where('name', 'Canva')->first();
        $this->assertNotNull($canva);
        $this->assertEquals('AU', $canva->country);
        $this->assertEquals('enterprise', $canva->category);
    }

    public function test_maps_countries_correctly(): void
    {
        $wikitext = $this->unicornTable([
            ['[[UKCompany]]', '5', 'United Kingdom', 'Fintech'],
            ['[[ChinaCo]]', '10', 'China', 'E-commerce'],
        ]);

        $this->fakeWikipediaResponse($wikitext);

        $importer = new WikipediaUnicornImporter();
        $importer->start();

        $uk = Company::where('name', 'UKCompany')->first();
        $this->assertNotNull($uk);
        $this->assertEquals('GB', $uk->country);

        $cn = Company::where('name', 'ChinaCo')->first();
        $this->assertNotNull($cn);
        $this->assertEquals('CN', $cn->country);
    }

    public function test_handles_empty_wikitext(): void
    {
        $this->fakeWikipediaResponse('');

        $importer = new WikipediaUnicornImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);
        $this->assertEquals(0, Company::count());
    }

    public function test_handles_api_failure(): void
    {
        Http::fake([
            'en.wikipedia.org/*' => Http::response(null, 500),
        ]);

        $importer = new WikipediaUnicornImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);
        $this->assertEquals(0, $result->companies_created);
    }

    public function test_maps_industries_to_categories(): void
    {
        $wikitext = $this->unicornTable([
            ['[[AIStartup]]', '5', 'United States', 'Artificial intelligence'],
            ['[[HealthCo]]', '3', 'United States', 'Health technology'],
        ]);

        $this->fakeWikipediaResponse($wikitext);

        $importer = new WikipediaUnicornImporter();
        $importer->start();

        $ai = Company::where('name', 'AIStartup')->first();
        $this->assertNotNull($ai);
        $this->assertEquals('ai_ml', $ai->category);

        $health = Company::where('name', 'HealthCo')->first();
        $this->assertNotNull($health);
        $this->assertEquals('healthcare', $health->category);
    }

    public function test_skips_invalid_entries(): void
    {
        $wikitext = $this->unicornTable([
            ['Total', '500', '', ''],
            ['5', '5', 'US', 'Tech'],
        ]);

        $this->fakeWikipediaResponse($wikitext);

        $importer = new WikipediaUnicornImporter();
        $importer->start();

        $this->assertEquals(0, Company::count());
    }
}
