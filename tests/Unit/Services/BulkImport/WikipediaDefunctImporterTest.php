<?php

namespace Tests\Unit\Services\BulkImport;

use App\Models\Company;
use App\Services\BulkImport\WikipediaDefunctImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WikipediaDefunctImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_name(): void
    {
        $importer = new WikipediaDefunctImporter();
        $this->assertEquals('wikipedia-defunct', $importer->source());
    }

    public function test_imports_defunct_companies(): void
    {
        $catCallCount = 0;
        Http::fake(function ($request) use (&$catCallCount) {
            $params = $request->data();

            if (isset($params['list']) && $params['list'] === 'categorymembers') {
                $catCallCount++;
                if ($catCallCount === 1) {
                    return Http::response([
                        'query' => [
                            'categorymembers' => [
                                ['title' => 'Solyndra', 'pageid' => 1],
                                ['title' => 'Theranos', 'pageid' => 2],
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
                                'title' => 'Solyndra',
                                'extract' => 'Solyndra was an American solar panel manufacturer.',
                            ],
                            '2' => [
                                'title' => 'Theranos',
                                'extract' => 'Theranos was a health technology company.',
                            ],
                        ],
                    ],
                ]);
            }

            return Http::response(['query' => ['categorymembers' => []]]);
        });

        $importer = new WikipediaDefunctImporter();
        $result = $importer->start();

        $this->assertEquals('completed', $result->status);

        $solyndra = Company::where('name', 'Solyndra')->first();
        $this->assertNotNull($solyndra);
        $this->assertEquals('closed', $solyndra->status);
        $this->assertEquals('US', $solyndra->country);

        $theranos = Company::where('name', 'Theranos')->first();
        $this->assertNotNull($theranos);
        $this->assertEquals('closed', $theranos->status);
    }

    public function test_falls_back_to_names_on_extract_failure(): void
    {
        $catCallCount = 0;
        Http::fake(function ($request) use (&$catCallCount) {
            $params = $request->data();

            if (isset($params['list']) && $params['list'] === 'categorymembers') {
                $catCallCount++;
                if ($catCallCount === 1) {
                    return Http::response([
                        'query' => [
                            'categorymembers' => [
                                ['title' => 'FailCorp', 'pageid' => 1],
                            ],
                        ],
                    ]);
                }
                return Http::response(['query' => ['categorymembers' => []]]);
            }

            // Extract calls fail
            if (isset($params['prop']) && $params['prop'] === 'extracts') {
                return Http::response(null, 500);
            }

            return Http::response(['query' => ['categorymembers' => []]]);
        });

        $importer = new WikipediaDefunctImporter();
        $importer->start();

        $company = Company::where('name', 'FailCorp')->first();
        $this->assertNotNull($company);
        $this->assertEquals('closed', $company->status);
        $this->assertEquals('US', $company->country);
    }

    public function test_skips_disambiguation_and_list_pages(): void
    {
        $catCallCount = 0;
        Http::fake(function ($request) use (&$catCallCount) {
            $params = $request->data();

            if (isset($params['list']) && $params['list'] === 'categorymembers') {
                $catCallCount++;
                if ($catCallCount === 1) {
                    return Http::response([
                        'query' => [
                            'categorymembers' => [
                                ['title' => 'List of defunct companies', 'pageid' => 1],
                                ['title' => 'Ambiguous Corp', 'pageid' => 2],
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
                                'title' => 'List of defunct companies',
                                'extract' => 'This is a list of defunct companies.',
                            ],
                            '2' => [
                                'title' => 'Ambiguous Corp',
                                'extract' => 'Ambiguous Corp may refer to several entities.',
                            ],
                        ],
                    ],
                ]);
            }

            return Http::response(['query' => ['categorymembers' => []]]);
        });

        $importer = new WikipediaDefunctImporter();
        $importer->start();

        $this->assertEquals(0, Company::count());
    }

    public function test_creates_import_log(): void
    {
        Http::fake(function () {
            return Http::response(['query' => ['categorymembers' => []]]);
        });

        $importer = new WikipediaDefunctImporter();
        $importer->start();

        $this->assertDatabaseHas('company_imports', [
            'source' => 'wikipedia-defunct',
            'status' => 'completed',
        ]);
    }
}
