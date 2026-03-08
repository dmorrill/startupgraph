<?php

use App\Services\Discovery\ProductHuntDiscoveryService;
use App\Services\Discovery\GitHubOrgDiscoveryService;
use App\Services\Discovery\CompaniesHouseService;

test('product hunt service throws without token', function () {
    $service = new ProductHuntDiscoveryService(null);
    expect(fn () => $service->discover())->toThrow(\RuntimeException::class);
});

test('companies house service throws without key', function () {
    $service = new CompaniesHouseService(null);
    expect(fn () => $service->search('test'))->toThrow(\RuntimeException::class);
});

test('github org service works without token (lower rate limit)', function () {
    $service = new GitHubOrgDiscoveryService(null);
    // Just verify it doesn't throw — actual API call would need mocking
    expect($service)->toBeInstanceOf(GitHubOrgDiscoveryService::class);
});
