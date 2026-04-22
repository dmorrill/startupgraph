<?php

namespace Tests\Feature;

use App\Services\Discovery\CompaniesHouseService;
use App\Services\Discovery\GitHubOrgDiscoveryService;
use App\Services\Discovery\ProductHuntDiscoveryService;
use Tests\TestCase;
use RuntimeException;

class DiscoveryServiceTest extends TestCase
{
    public function test_product_hunt_service_throws_without_token(): void
    {
        $this->expectException(RuntimeException::class);
        
        $service = new ProductHuntDiscoveryService(null);
        $service->discover();
    }

    public function test_companies_house_service_throws_without_key(): void
    {
        $this->expectException(RuntimeException::class);
        
        $service = new CompaniesHouseService(null);
        $service->search('test');
    }

    public function test_github_org_service_works_without_token_lower_rate_limit(): void
    {
        $service = new GitHubOrgDiscoveryService(null);
        // Just verify it doesn't throw — actual API call would need mocking
        $this->assertInstanceOf(GitHubOrgDiscoveryService::class, $service);
    }
}
