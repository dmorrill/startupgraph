<?php

namespace Tests\Unit\Services\Discovery;

use App\Contracts\CompanyDiscoverySource;
use App\Models\Company;
use App\Services\DiscoverCompaniesService;
use App\Services\Discovery\CrunchbaseDiscoverySource;
use App\Services\Discovery\HackerNewsDiscoverySource;
use App\Services\Discovery\ProductHuntDiscoverySource;
use App\Services\Discovery\TechCrunchDiscoverySource;
use App\Services\Discovery\WellfoundDiscoverySource;
use App\Services\Discovery\YCombinatorDiscoverySource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoverCompaniesServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(array $sourceMocks = []): DiscoverCompaniesService
    {
        $defaults = [
            TechCrunchDiscoverySource::class,
            YCombinatorDiscoverySource::class,
            CrunchbaseDiscoverySource::class,
            WellfoundDiscoverySource::class,
            HackerNewsDiscoverySource::class,
            ProductHuntDiscoverySource::class,
        ];

        $args = [];
        foreach ($defaults as $class) {
            if (isset($sourceMocks[$class])) {
                $args[] = $sourceMocks[$class];
            } else {
                $mock = $this->createMock($class);
                $mock->method('name')->willReturn(strtolower(class_basename($class)));
                $mock->method('discover')->willReturn([]);
                $args[] = $mock;
            }
        }

        return new DiscoverCompaniesService(...$args);
    }

    public function test_get_available_sources(): void
    {
        $service = $this->makeService();

        $sources = $service->getAvailableSources();

        $this->assertCount(6, $sources);
    }

    public function test_run_single_source_dry_run(): void
    {
        $mock = $this->createMock(YCombinatorDiscoverySource::class);
        $mock->method('name')->willReturn('yc');
        $mock->method('discover')->willReturn([
            ['name' => 'TestCo', 'website' => 'https://testco.com', 'description' => 'A test'],
        ]);

        $service = $this->makeService([YCombinatorDiscoverySource::class => $mock]);
        $results = $service->run('yc', 7, true);

        $this->assertCount(1, $results['discovered']);
        $this->assertCount(1, $results['created']);
        $this->assertTrue($results['created'][0]['dry_run']);
        $this->assertEmpty($results['errors']);
    }

    public function test_run_creates_company(): void
    {
        $mock = $this->createMock(HackerNewsDiscoverySource::class);
        $mock->method('name')->willReturn('hackernews');
        $mock->method('discover')->willReturn([
            ['name' => 'NewStartup', 'website' => 'https://newstartup.com', 'description' => 'Fresh'],
        ]);

        $service = $this->makeService([HackerNewsDiscoverySource::class => $mock]);
        $results = $service->run('hackernews', 7, false);

        $this->assertCount(1, $results['created']);
        $this->assertDatabaseHas('companies', ['name' => 'NewStartup']);
    }

    public function test_run_detects_existing_company_by_name(): void
    {
        Company::factory()->create(['name' => 'ExistingCo']);

        $mock = $this->createMock(CrunchbaseDiscoverySource::class);
        $mock->method('name')->willReturn('crunchbase');
        $mock->method('discover')->willReturn([
            ['name' => 'ExistingCo', 'website' => 'https://existingco.com'],
        ]);

        $service = $this->makeService([CrunchbaseDiscoverySource::class => $mock]);
        $results = $service->run('crunchbase', 7, false);

        $this->assertCount(1, $results['existing']);
        $this->assertEmpty($results['created']);
    }

    public function test_run_unknown_source_returns_error(): void
    {
        $service = $this->makeService();
        $results = $service->run('nonexistent');

        $this->assertCount(1, $results['errors']);
        $this->assertStringContains('Unknown source', $results['errors'][0]);
    }

    public function test_run_creates_funding_round_when_info_present(): void
    {
        $mock = $this->createMock(TechCrunchDiscoverySource::class);
        $mock->method('name')->willReturn('techcrunch');
        $mock->method('discover')->willReturn([
            [
                'name' => 'FundedCo',
                'website' => 'https://fundedco.com',
                'funding_amount' => 5000000,
                'funding_round' => 'seed',
                'source_url' => 'https://tc.com/article',
            ],
        ]);

        $service = $this->makeService([TechCrunchDiscoverySource::class => $mock]);
        $results = $service->run('techcrunch', 7, false);

        $this->assertCount(1, $results['created']);
        $company = Company::where('name', 'FundedCo')->first();
        $this->assertNotNull($company);
        $this->assertCount(1, $company->fundingRounds);
        $this->assertEquals('seed', $company->fundingRounds->first()->round_type);
        $this->assertEquals(5000000, $company->fundingRounds->first()->amount);
    }

    public function test_run_skips_candidates_without_name(): void
    {
        $mock = $this->createMock(WellfoundDiscoverySource::class);
        $mock->method('name')->willReturn('wellfound');
        $mock->method('discover')->willReturn([
            ['description' => 'No name provided'],
            ['name' => 'ValidCo'],
        ]);

        $service = $this->makeService([WellfoundDiscoverySource::class => $mock]);
        $results = $service->run('wellfound', 7, true);

        $this->assertCount(1, $results['created']);
    }

    /**
     * Helper for older PHPUnit compat.
     */
    private static function assertStringContains(string $needle, string $haystack): void
    {
        static::assertStringContainsString($needle, $haystack);
    }
}
