<?php

namespace App\Console\Commands;

use App\Services\BulkImport\CompaniesHouseCsvImporter;
use App\Services\BulkImport\CrunchbaseCsvImporter;
use App\Services\BulkImport\EdgarBulkImporter;
use App\Services\BulkImport\GitHubOrgImporter;
use App\Services\BulkImport\GitHubTrendingImporter;
use App\Services\BulkImport\OpenCorporatesBulkImporter;
use App\Services\BulkImport\ProductHuntBulkImporter;
use App\Services\BulkImport\WikipediaAcquisitionsImporter;
use App\Services\BulkImport\WikipediaCategoryImporter;
use App\Services\BulkImport\WikipediaDefunctImporter;
use App\Services\BulkImport\WikipediaUnicornImporter;
use App\Services\BulkImport\YCBulkImporter;
use Illuminate\Console\Command;

class BulkImportCompanies extends Command
{
    protected $signature = 'companies:bulk-import
        {--source= : Import source (yc, crunchbase, producthunt, edgar, github, opencorporates, companies-house, wikipedia, wikipedia-acquisitions, wikipedia-defunct, wikipedia-categories, github-orgs)}
        {--file= : CSV file path (required for crunchbase, optional for companies-house)}
        {--all : Run all sources}
        {--resume : Resume from last checkpoint}
        {--max-pages=500 : Maximum pages to fetch (API sources)}
        {--min-year=2015 : Minimum incorporation year (companies-house)}
        {--tech-only : Only import tech/startup companies (companies-house, default: true)}';

    protected $description = 'Bulk import companies from various data sources';

    private array $importers = [
        'yc' => YCBulkImporter::class,
        'crunchbase' => CrunchbaseCsvImporter::class,
        'producthunt' => ProductHuntBulkImporter::class,
        'edgar' => EdgarBulkImporter::class,
        'github' => GitHubTrendingImporter::class,
        'opencorporates' => OpenCorporatesBulkImporter::class,
        'companies-house' => CompaniesHouseCsvImporter::class,
        'wikipedia' => WikipediaUnicornImporter::class,
        'wikipedia-acquisitions' => WikipediaAcquisitionsImporter::class,
        'wikipedia-defunct' => WikipediaDefunctImporter::class,
        'wikipedia-categories' => WikipediaCategoryImporter::class,
        'github-orgs' => GitHubOrgImporter::class,
    ];

    public function handle(): int
    {
        $source = $this->option('source');
        $all = $this->option('all');

        if (!$source && !$all) {
            $this->error('Specify --source=<name> or --all');
            $this->line('Available sources: ' . implode(', ', array_keys($this->importers)));
            return 1;
        }

        $sources = $all ? array_keys($this->importers) : [$source];

        foreach ($sources as $src) {
            if (!isset($this->importers[$src])) {
                $this->error("Unknown source: {$src}");
                continue;
            }

            $this->info("Starting import from: {$src}");

            try {
                $importer = new ($this->importers[$src]);
                $options = $this->buildOptions($src);
                $importLog = $importer->start($options);
                $stats = $importer->getStats();

                $this->info("✅ {$src} import complete:");
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Created', $stats['created']],
                        ['Updated', $stats['updated']],
                        ['Skipped', $stats['skipped']],
                        ['Total Processed', $stats['processed']],
                    ]
                );
            } catch (\Exception $e) {
                $this->error("❌ {$src} import failed: {$e->getMessage()}");
            }
        }

        return 0;
    }

    private function buildOptions(string $source): array
    {
        $options = [
            'max_pages' => (int) $this->option('max-pages'),
        ];

        if ($source === 'crunchbase') {
            $file = $this->option('file');
            if (!$file) {
                throw new \RuntimeException('--file is required for crunchbase source');
            }
            $options['file'] = $file;
        }

        if ($source === 'companies-house') {
            $file = $this->option('file');
            if ($file) {
                $options['file'] = $file;
            }
            $options['min_year'] = (int) $this->option('min-year');
            $options['tech_only'] = $this->option('tech-only') !== false;
        }

        if ($this->option('resume')) {
            $lastImport = \App\Models\CompanyImport::where('source', $source)
                ->latest()
                ->first();

            if ($lastImport) {
                $options['resume_page'] = $lastImport->last_page ?? 0;
                $options['resume_offset'] = (int) ($lastImport->last_offset ?? 0);
                $options['resume_cursor'] = $lastImport->last_offset;
                $options['resume_from'] = (int) ($lastImport->last_offset ?? 0);
                $this->info("Resuming from last checkpoint");
            }
        }

        return $options;
    }
}
