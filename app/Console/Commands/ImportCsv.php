<?php

namespace App\Console\Commands;

use App\Services\BulkImport\CrunchbaseCsvImporter;
use Illuminate\Console\Command;

class ImportCsv extends Command
{
    protected $signature = 'companies:import-csv
        {path : Path to CSV file}
        {--source=crunchbase : Source identifier for the CSV data}';

    protected $description = 'Import companies from a CSV file';

    public function handle(): int
    {
        $path = $this->argument('path');
        $source = $this->option('source');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $this->info("Importing companies from CSV: {$path} (source: {$source})");

        $importer = new CrunchbaseCsvImporter();
        $importLog = $importer->start(['file' => $path]);
        $stats = $importer->getStats();

        $this->info("✅ CSV import complete:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Created', $stats['created']],
                ['Updated', $stats['updated']],
                ['Skipped', $stats['skipped']],
                ['Total Processed', $stats['processed']],
            ]
        );

        return 0;
    }
}
