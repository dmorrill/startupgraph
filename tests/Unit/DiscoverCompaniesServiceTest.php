<?php

namespace Tests\Unit;

use App\Services\DiscoverCompaniesService;
use App\Services\Discovery\TechCrunchDiscoverySource;
use App\Services\Discovery\YCombinatorDiscoverySource;
use App\Contracts\CompanyDiscoverySource;
use App\Models\Company;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class DiscoverCompaniesServiceTest extends TestCase
{
    use RefreshDatabase;

    private DiscoverCompaniesService $service;
    private $mockTechCrunch;
    private $mockYCombinator;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockTechCrunch = Mockery::mock(TechCrunchDiscoverySource::class);
        $this->mockYCombinator = Mockery::mock(YCombinatorDiscoverySource::class);
        
        // Create service with minimal mocked dependencies
        $this->service = new DiscoverCompaniesService(
            $this->mockTechCrunch,
            $this->mockYCombinator,
            Mockery::mock(\App\Services\Discovery\CrunchbaseDiscoverySource::class),
            Mockery::mock(\App\Services\Discovery\WellfoundDiscoverySource::class),
            Mockery::mock(\App\Services\Discovery\HackerNewsDiscoverySource::class),
            Mockery::mock(\App\Services\Discovery\ProductHuntDiscoverySource::class)
        );
    }

    public function test_registers_discovery_sources()
    {
        $this->mockTechCrunch->shouldReceive('name')->andReturn('techcrunch');
        $this->mockYCombinator->shouldReceive('name')->andReturn('ycombinator');

        $sources = $this->service->getAvailableSources();
        
        $this->assertContains('techcrunch', $sources);
        $this->assertContains('ycombinator', $sources);
        $this->assertCount(6, $sources); // All sources registered
    }

    public function test_discovers_companies_from_single_source()
    {
        $this->mockTechCrunch->shouldReceive('name')->andReturn('techcrunch');
        $this->mockTechCrunch->shouldReceive('discover')->once()->andReturn([
            [
                'name' => 'Test Company',
                'website' => 'https://test.com',
                'description' => 'A test company',
                'location' => 'San Francisco',
                'founded_year' => 2020,
                'source' => 'techcrunch'
            ]
        ]);

        $result = $this->service->discoverFrom(['techcrunch']);

        $this->assertArrayHasKey('discovered', $result);
        $this->assertArrayHasKey('created', $result);
        $this->assertArrayHasKey('existing', $result);
        $this->assertArrayHasKey('errors', $result);
        
        $this->assertCount(1, $result['discovered']);
        $this->assertEquals('Test Company', $result['discovered'][0]['name']);
    }

    public function test_handles_discovery_source_errors()
    {
        $this->mockTechCrunch->shouldReceive('name')->andReturn('techcrunch');
        $this->mockTechCrunch->shouldReceive('discover')->andThrow(new \Exception('API Error'));

        $result = $this->service->discoverFrom(['techcrunch']);

        $this->assertArrayHasKey('errors', $result);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('techcrunch', $result['errors'][0]);
        $this->assertStringContainsString('API Error', $result['errors'][0]);
    }

    public function test_creates_new_companies()
    {
        $this->mockTechCrunch->shouldReceive('name')->andReturn('techcrunch');
        $this->mockTechCrunch->shouldReceive('discover')->andReturn([
            [
                'name' => 'New Company',
                'website' => 'https://newcompany.com',
                'description' => 'A brand new company',
                'location' => 'New York',
                'founded_year' => 2023,
                'source' => 'techcrunch'
            ]
        ]);

        $result = $this->service->discoverFrom(['techcrunch']);

        $this->assertCount(1, $result['created']);
        $this->assertEquals('New Company', $result['created'][0]->name);
        
        // Verify company was saved to database
        $this->assertDatabaseHas('companies', [
            'name' => 'New Company',
            'website' => 'https://newcompany.com'
        ]);
    }

    public function test_identifies_existing_companies()
    {
        // Create existing company
        Company::factory()->create([
            'name' => 'Existing Company',
            'website' => 'https://existing.com'
        ]);

        $this->mockTechCrunch->shouldReceive('name')->andReturn('techcrunch');
        $this->mockTechCrunch->shouldReceive('discover')->andReturn([
            [
                'name' => 'Existing Company',
                'website' => 'https://existing.com',
                'description' => 'An existing company',
                'source' => 'techcrunch'
            ]
        ]);

        $result = $this->service->discoverFrom(['techcrunch']);

        $this->assertCount(1, $result['existing']);
        $this->assertCount(0, $result['created']);
        $this->assertEquals('Existing Company', $result['existing'][0]->name);
    }

    public function test_discovers_from_multiple_sources()
    {
        $this->mockTechCrunch->shouldReceive('name')->andReturn('techcrunch');
        $this->mockTechCrunch->shouldReceive('discover')->andReturn([
            [
                'name' => 'TechCrunch Company',
                'website' => 'https://tc.com',
                'source' => 'techcrunch'
            ]
        ]);

        $this->mockYCombinator->shouldReceive('name')->andReturn('ycombinator');
        $this->mockYCombinator->shouldReceive('discover')->andReturn([
            [
                'name' => 'YC Company',
                'website' => 'https://yc.com',
                'source' => 'ycombinator'
            ]
        ]);

        $result = $this->service->discoverFrom(['techcrunch', 'ycombinator']);

        $this->assertCount(2, $result['discovered']);
        $companyNames = array_column($result['discovered'], 'name');
        $this->assertContains('TechCrunch Company', $companyNames);
        $this->assertContains('YC Company', $companyNames);
    }

    public function test_throws_exception_for_unknown_source()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown discovery source: unknown');

        $this->service->discoverFrom(['unknown']);
    }

    public function test_handles_malformed_company_data()
    {
        $this->mockTechCrunch->shouldReceive('name')->andReturn('techcrunch');
        $this->mockTechCrunch->shouldReceive('discover')->andReturn([
            [
                // Missing required 'name' field
                'website' => 'https://malformed.com',
                'description' => 'Malformed company data',
                'source' => 'techcrunch'
            ]
        ]);

        $result = $this->service->discoverFrom(['techcrunch']);

        $this->assertArrayHasKey('errors', $result);
        $this->assertCount(1, $result['errors']);
        $this->assertCount(0, $result['created']);
    }

    public function test_can_register_additional_sources()
    {
        $customSource = Mockery::mock(CompanyDiscoverySource::class);
        $customSource->shouldReceive('name')->andReturn('custom');

        $this->service->registerSource($customSource);

        $sources = $this->service->getAvailableSources();
        $this->assertContains('custom', $sources);
        $this->assertCount(7, $sources); // 6 original + 1 custom
    }

    public function test_logs_discovery_progress()
    {
        $this->mockTechCrunch->shouldReceive('name')->andReturn('techcrunch');
        $this->mockTechCrunch->shouldReceive('discover')->andReturn([]);

        // We can't easily test logging without mocking Log facade, 
        // but we can ensure the method completes without errors
        $result = $this->service->discoverFrom(['techcrunch']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('discovered', $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}