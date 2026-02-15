<?php

namespace App\Console\Commands;

use App\Services\GitHubDiscoveryService;
use Illuminate\Console\Command;

class DiscoverOssProjects extends Command
{
    protected $signature = 'app:discover-oss-projects';
    protected $description = 'Discover open-source projects from GitHub topics and awesome-selfhosted';

    public function handle(GitHubDiscoveryService $service): int
    {
        $this->info('Starting GitHub OSS discovery...');

        $stats = $service->discover();

        $this->info("Discovery complete: {$stats['created']} created, {$stats['updated']} updated, {$stats['errors']} errors");

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
