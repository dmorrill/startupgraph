<?php

namespace Tests\Unit\Services\BulkImport;

use App\Models\Company;
use App\Services\BulkImport\WikipediaCategoryImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WikipediaCategoryImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_name(): void
    {
        $importer = new WikipediaCategoryImporter();
        $this->assertEquals('wikipedia-categories', $importer->source());
    }

    public function test_imports_companies_from_category(): void
    {
        // The importer iterates 12 categories. Each category makes a category-members call,
        // then an extracts call. We fake all calls via a wildcard returning appropriate data.
        // First call gets members, second gets extracts, rest return empty.
        $callCount = 0;
        Http::fake(function ($request) use (&$callCount) {
            $callCount++;
            $params = $request->data();

            // Category members request
            if (isset($params['list']) && $params['list'] === 'categorymembers') {
                // Only return data for the first category
                if ($callCount <= 1) {
                    return Http::response([
                        'query' => [
                            'categorymembers' => [
                                ['title' => 'TestCompany', 'pageid' => 1],
                            ],
                        ],
                    ]);
                }
                return Http::response([
                    'query' => ['categorymembers' => []],
                ]);
            }

            // Extracts request
            if (isset($params['prop']) && $params['prop'] === 'extracts') {
                return Http::response([
                    'query' => [
                        'pages' => [
                            '1' => [
                                'title' => 'TestCompany',
                                'extract' => 'TestCompany is a technology company.',
                            ],
                        ],
                    ],
                ]);
            }

            return Http::response([
                'query' => ['categorymembers' => []],
            ]);
        });

        $importer = new WikipediaCategoryImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);

        $company = Company::where('name', 'TestCompany')->first();
        $this->assertNotNull($company);
        $this->assertEquals('operating', $company->status);
        $this->assertNotNull($company->description);
    }

    public function test_handles_api_failure_gracefully(): void
    {
        Http::fake([
            'en.wikipedia.org/*' => Http::response(null, 500),
        ]);

        $importer = new WikipediaCategoryImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);
        $this->assertEquals(0, $result->companies_created);
    }

    public function test_skips_disambiguation_pages(): void
    {
        Http::fake(function ($request) {
            $params = $request->data();

            if (isset($params['list']) && $params['list'] === 'categorymembers') {
                // Return one member for first category only (check if cmcontinue is absent)
                static $firstCat = true;
                if ($firstCat && !isset($params['cmcontinue'])) {
                    $firstCat = false;
                    return Http::response([
                        'query' => [
                            'categorymembers' => [
                                ['title' => 'Ambiguous', 'pageid' => 1],
                            ],
                        ],
                    ]);
                }
                return Http::response(['query' => ['categorymembers' => []]]);
            }

            if (isset($params['prop']) && $params['prop'] === 'extracts') {
                return Http::response([
                    'query' => [
                        'pages' => [
                            '1' => [
                                'title' => 'Ambiguous',
                                'extract' => 'Ambiguous may refer to several companies.',
                            ],
                        ],
                    ],
                ]);
            }

            return Http::response(['query' => ['categorymembers' => []]]);
        });

        $importer = new WikipediaCategoryImporter();
        $importer->start();

        $this->assertEquals(0, Company::count());
    }

    public function test_creates_import_log(): void
    {
        Http::fake(function () {
            return Http::response(['query' => ['categorymembers' => []]]);
        });

        $importer = new WikipediaCategoryImporter();
        $importer->start();

        $this->assertDatabaseHas('company_imports', [
            'source' => 'wikipedia-categories',
            'status' => 'completed',
        ]);
    }
}
