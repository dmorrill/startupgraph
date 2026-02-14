<?php

namespace App\Console\Commands;

use App\Services\DiscoverCompaniesService;
use Illuminate\Console\Command;

class DiscoverCompanies extends Command
{
    protected $signature = 'companies:discover
                            {--source=all : Discovery source (techcrunch, yc, or all)}
                            {--days=7 : How far back to look}
                            {--dry-run : Show what would be added without saving}';

    protected $description = 'Discover and add new companies from external sources (TechCrunch, Y Combinator, etc.)';

    public function handle(DiscoverCompaniesService $service): int
    {
        $source = $this->option('source');
        $days = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');

        $available = $service->getAvailableSources();

        if ($source !== 'all' && !in_array($source, $available)) {
            $this->error("Unknown source '{$source}'. Available: " . implode(', ', $available));
            return self::FAILURE;
        }

        $this->info('🔍 Discovering companies...');
        $this->info("   Source: {$source} | Days: {$days}" . ($dryRun ? ' | DRY RUN' : ''));
        $this->newLine();

        $results = $service->run($source, $days, $dryRun);

        // Show discovered
        $this->info("📊 Discovered: " . count($results['discovered']) . " candidates");

        // Show existing
        if (!empty($results['existing'])) {
            $this->line("   Already known: " . count($results['existing']));
            foreach ($results['existing'] as $item) {
                $this->line("   ├─ {$item['name']} ({$item['source']})");
            }
        }

        // Show created/would-create
        if (!empty($results['created'])) {
            $label = $dryRun ? 'Would add' : 'Added';
            $this->info("   ✅ {$label}: " . count($results['created']));
            foreach ($results['created'] as $item) {
                $name = $item['name'];
                $extra = [];
                if (isset($item['funding_amount'])) {
                    $extra[] = '$' . number_format($item['funding_amount']);
                }
                if (isset($item['funding_round'])) {
                    $extra[] = $item['funding_round'];
                }
                if (isset($item['batch'])) {
                    $extra[] = $item['batch'];
                }
                $suffix = $extra ? ' (' . implode(', ', $extra) . ')' : '';
                $this->line("   ├─ {$name}{$suffix} [{$item['source']}]");
            }
        } else {
            $this->line("   No new companies found.");
        }

        // Show errors
        if (!empty($results['errors'])) {
            $this->newLine();
            $this->warn("⚠️  Errors:");
            foreach ($results['errors'] as $error) {
                $this->warn("   {$error}");
            }
        }

        $this->newLine();
        $this->info('Done!');

        return self::SUCCESS;
    }
}
