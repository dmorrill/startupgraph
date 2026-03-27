<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\CompanyImport;
use App\Services\BulkImport\BaseBulkImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseBulkImporterTest extends TestCase
{
    use RefreshDatabase;

    private function createTestImporter(): BaseBulkImporter
    {
        return new class extends BaseBulkImporter {
            public function source(): string
            {
                return 'test-source';
            }

            public function import(array $options = []): void
            {
                // Mock implementation for testing
                $this->upsertCompany([
                    'name' => 'Test Company',
                    'website' => 'https://test.com',
                    'description' => 'A test company',
                    'city' => 'San Francisco',
                    'country' => 'USA'
                ]);
            }

            public function publicUpsertCompany(array $data): void
            {
                $this->upsertCompany($data);
            }

            public function getPublicStats(): array
            {
                return $this->getStats();
            }
        };
    }

    public function test_start_creates_import_log_and_tracks_completion(): void
    {
        $importer = $this->createTestImporter();
        
        $result = $importer->start();
        
        $this->assertInstanceOf(CompanyImport::class, $result);
        $this->assertEquals('test-source', $result->source);
        $this->assertEquals('completed', $result->status);
        $this->assertNotNull($result->started_at);
        $this->assertNotNull($result->completed_at);
        $this->assertEquals(1, $result->companies_created);
        $this->assertEquals(0, $result->companies_updated);
        $this->assertEquals(0, $result->companies_skipped);
        $this->assertEquals(1, $result->total_processed);
    }

    public function test_upsert_company_creates_new_company(): void
    {
        $importer = $this->createTestImporter();
        
        $importer->publicUpsertCompany([
            'name' => 'New Startup',
            'website' => 'https://newstartup.com',
            'description' => 'An innovative startup',
            'founded_date' => '2023-01-01',
            'city' => 'Austin',
            'state' => 'TX',
            'country' => 'USA',
            'category' => 'ai_ml',
            'current_headcount' => 25
        ]);
        
        $company = Company::where('name', 'New Startup')->first();
        $this->assertNotNull($company);
        $this->assertEquals('https://newstartup.com', $company->website);
        $this->assertEquals('An innovative startup', $company->description);
        $this->assertEquals('Austin', $company->city);
        $this->assertEquals('TX', $company->state);
        $this->assertEquals('USA', $company->country);
        $this->assertEquals('ai_ml', $company->category);
        $this->assertEquals(25, $company->current_headcount);
        $this->assertEquals('test-source', $company->import_source);
        
        $stats = $importer->getPublicStats();
        $this->assertEquals(1, $stats['created']);
        $this->assertEquals(0, $stats['updated']);
        $this->assertEquals(0, $stats['skipped']);
    }

    public function test_upsert_company_updates_existing_company_with_missing_fields(): void
    {
        // Create existing company with minimal data
        $existing = Company::factory()->create([
            'name' => 'Existing Company',
            'website' => 'https://existing.com',
            'description' => null, // Missing description
            'city' => null,        // Missing city
            'country' => 'USA'     // Has country
        ]);
        
        $importer = $this->createTestImporter();
        
        $importer->publicUpsertCompany([
            'name' => 'Existing Company',
            'website' => 'https://existing.com',
            'description' => 'Updated description', // Will be added
            'city' => 'Seattle',                    // Will be added
            'country' => 'Canada'                   // Will NOT overwrite existing
        ]);
        
        $existing->refresh();
        $this->assertEquals('Updated description', $existing->description);
        $this->assertEquals('Seattle', $existing->city);
        $this->assertEquals('USA', $existing->country); // Unchanged
        
        $stats = $importer->getPublicStats();
        $this->assertEquals(0, $stats['created']);
        $this->assertEquals(1, $stats['updated']);
        $this->assertEquals(0, $stats['skipped']);
    }

    public function test_upsert_company_skips_duplicate_with_no_new_data(): void
    {
        // Create existing company with complete data INCLUDING import_source
        $existing = Company::factory()->create([
            'name' => 'Complete Company',
            'website' => 'https://complete.com',
            'description' => 'Already has description',
            'city' => 'Denver',
            'country' => 'USA',
            'import_source' => 'existing-source'  // Already has import source
        ]);
        
        $importer = $this->createTestImporter();
        
        $importer->publicUpsertCompany([
            'name' => 'Complete Company',
            'website' => 'https://complete.com',
            'description' => 'Different description', // Won't overwrite
            'city' => 'Austin'                        // Won't overwrite
        ]);
        
        $existing->refresh();
        $this->assertEquals('Already has description', $existing->description);
        $this->assertEquals('Denver', $existing->city);
        $this->assertEquals('existing-source', $existing->import_source); // Unchanged
        
        $stats = $importer->getPublicStats();
        $this->assertEquals(0, $stats['created']);
        $this->assertEquals(0, $stats['updated']);
        $this->assertEquals(1, $stats['skipped']);
    }

    public function test_upsert_company_skips_empty_name(): void
    {
        $importer = $this->createTestImporter();
        
        $importer->publicUpsertCompany([
            'name' => '',  // Empty name
            'website' => 'https://test.com',
        ]);
        
        $this->assertEquals(0, Company::count());
        
        $stats = $importer->getPublicStats();
        $this->assertEquals(0, $stats['created']);
        $this->assertEquals(0, $stats['updated']);
        $this->assertEquals(1, $stats['skipped']);
    }

    public function test_upsert_company_finds_existing_by_domain(): void
    {
        // Create existing company with similar domain
        $existing = Company::factory()->create([
            'name' => 'Domain Company',
            'website' => 'https://www.example.com/about',
            'description' => null
        ]);
        
        $importer = $this->createTestImporter();
        
        $importer->publicUpsertCompany([
            'name' => 'Domain Company Updated', // Different name
            'website' => 'https://example.com',  // Same domain, different format
            'description' => 'Added description'
        ]);
        
        $this->assertEquals(1, Company::count()); // Still just one company
        $existing->refresh();
        $this->assertEquals('Added description', $existing->description);
        
        $stats = $importer->getPublicStats();
        $this->assertEquals(0, $stats['created']);
        $this->assertEquals(1, $stats['updated']);
    }

    public function test_upsert_company_handles_slug_collisions(): void
    {
        // Create existing company with a specific slug
        Company::factory()->create(['name' => 'Test Company One', 'slug' => 'test-company']);
        
        $importer = $this->createTestImporter();
        
        // Try to create a company that would have the same slug
        $importer->publicUpsertCompany([
            'name' => 'Test Company Two', // Different name but same slug pattern
            'website' => 'https://different.com'
        ]);
        
        $this->assertEquals(2, Company::count());
        
        // Find the new company
        $newCompany = Company::where('website', 'https://different.com')->first();
        $this->assertNotNull($newCompany);
        $this->assertEquals('Test Company Two', $newCompany->name);
        
        // Should have a unique slug (not the same as the existing one)
        $existingCompany = Company::where('name', 'Test Company One')->first();
        $this->assertNotEquals($existingCompany->slug, $newCompany->slug);
        
        // The new slug should start with the base pattern but have a unique suffix
        $this->assertStringStartsWith('test-company', $newCompany->slug);
        $this->assertNotEquals('test-company', $newCompany->slug);
    }

    public function test_start_handles_import_exceptions(): void
    {
        $failingImporter = new class extends BaseBulkImporter {
            public function source(): string
            {
                return 'failing-source';
            }

            public function import(array $options = []): void
            {
                throw new \Exception('Import failed');
            }
        };
        
        $this->expectException(\Exception::class);
        
        try {
            $failingImporter->start();
        } catch (\Exception $e) {
            // Check that the import log was updated with failure
            $importLog = CompanyImport::where('source', 'failing-source')->first();
            $this->assertNotNull($importLog);
            $this->assertEquals('failed', $importLog->status);
            $this->assertEquals('Import failed', $importLog->error_message);
            $this->assertNotNull($importLog->completed_at);
            
            throw $e; // Re-throw for the expectException
        }
    }

    public function test_extract_domain_handles_various_url_formats(): void
    {
        $importer = $this->createTestImporter();
        
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($importer);
        $method = $reflection->getMethod('extractDomain');
        $method->setAccessible(true);
        
        $this->assertEquals('example.com', $method->invoke($importer, 'https://www.example.com'));
        $this->assertEquals('example.com', $method->invoke($importer, 'http://example.com/path'));
        $this->assertEquals('subdomain.example.com', $method->invoke($importer, 'https://subdomain.example.com'));
        $this->assertNull($method->invoke($importer, null));
        $this->assertNull($method->invoke($importer, 'not-a-url'));
        $this->assertNull($method->invoke($importer, ''));
    }

    public function test_map_status_covers_all_cases(): void
    {
        $importer = $this->createTestImporter();
        
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($importer);
        $method = $reflection->getMethod('mapStatus');
        $method->setAccessible(true);
        
        // Test all operating status variations
        $this->assertEquals('operating', $method->invoke($importer, 'active'));
        $this->assertEquals('operating', $method->invoke($importer, 'ACTIVE'));
        $this->assertEquals('operating', $method->invoke($importer, '  active  '));
        $this->assertEquals('operating', $method->invoke($importer, 'operating'));
        $this->assertEquals('operating', $method->invoke($importer, 'alive'));
        
        // Test all closed status variations
        $this->assertEquals('closed', $method->invoke($importer, 'inactive'));
        $this->assertEquals('closed', $method->invoke($importer, 'closed'));
        $this->assertEquals('closed', $method->invoke($importer, 'dead'));
        
        // Test acquired status
        $this->assertEquals('acquired', $method->invoke($importer, 'acquired'));
        
        // Test IPO status variations
        $this->assertEquals('ipo', $method->invoke($importer, 'ipo'));
        $this->assertEquals('ipo', $method->invoke($importer, 'public'));
        
        // Test default fallback
        $this->assertEquals('operating', $method->invoke($importer, 'unknown-status'));
        $this->assertEquals('operating', $method->invoke($importer, ''));
    }
}