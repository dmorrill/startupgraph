<?php

namespace Tests\Unit\Services;

use App\Contracts\CompanyDiscoverySource;
use App\Services\DiscoverCompaniesService;
use App\Services\Discovery\CrunchbaseDiscoverySource;
use App\Services\Discovery\HackerNewsDiscoverySource;
use App\Services\Discovery\ProductHuntDiscoverySource;
use App\Services\Discovery\TechCrunchDiscoverySource;
use App\Services\Discovery\WellfoundDiscoverySource;
use App\Services\Discovery\YCombinatorDiscoverySource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\TestCase;

class DiscoverCompaniesServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DiscoverCompaniesService $service;

    protected CompanyDiscoverySource $mockSource;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSource = Mockery::mock(CompanyDiscoverySource::class);
        $this->mockSource->shouldReceive('name')->andReturn('test-source');

        $this->service = new DiscoverCompaniesService(
            Mockery::mock(TechCrunchDiscoverySource::class),
            Mockery::mock(YCombinatorDiscoverySource::class),
            Mockery::mock(CrunchbaseDiscoverySource::class),
            Mockery::mock(WellfoundDiscoverySource::class),
            Mockery::mock(HackerNewsDiscoverySource::class),
            Mockery::mock(ProductHuntDiscoverySource::class),
        );

        $this->service->registerSource($this->mockSource);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_register_discovery_source(): void
    {
        $sources = $this->service->getAvailableSources();

        $this->assertContains('test-source', $sources);
    }

    public function test_discover_handles_source_errors_gracefully(): void
    {
        $this->mockSource->shouldReceive('discover')
            ->andThrow(new \Exception('API error'));

        $result = $this->service->run('test-source', 7, true);

        $this->assertArrayHasKey('errors', $result);
        $this->assertStringContains('API error', $result['errors'][0]);
    }

    public function test_discover_returns_empty_for_unknown_source(): void
    {
        $result = $this->service->run('unknown-source', 7, true);

        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(['Unknown source: unknown-source'], $result['errors']);
    }
}
