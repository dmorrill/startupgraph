<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Log;

class CompaniesHouseCsvImporter extends BaseBulkImporter
{
    private const DOWNLOAD_URL = 'https://download.companieshouse.gov.uk/BasicCompanyDataAsOneFile-%s.zip';

    // SIC codes that indicate tech/startup companies
    private const TECH_SIC_PREFIXES = [
        '58' => 'enterprise',      // Publishing (software publishing)
        '59' => 'consumer',        // Motion picture / video
        '60' => 'consumer',        // Broadcasting
        '61' => 'enterprise',      // Telecommunications
        '62' => 'enterprise',      // Computer programming, consultancy
        '63' => 'enterprise',      // Information service activities
        '64' => 'fintech',         // Financial service activities
        '65' => 'fintech',         // Insurance
        '66' => 'fintech',         // Auxiliary financial services
        '70' => 'enterprise',      // Head offices / management consultancy
        '71' => 'enterprise',      // Architecture / engineering / technical
        '72' => 'ai_ml',           // Scientific research and development
        '74' => 'enterprise',      // Other professional, scientific, technical
        '85' => 'enterprise',      // Education
        '86' => 'healthcare',      // Human health activities
        '21' => 'healthcare',      // Pharma
        '26' => 'robotics',        // Computer/electronic/optical products
    ];

    public function source(): string
    {
        return 'companies-house';
    }

    public function import(array $options = []): void
    {
        $filePath = $options['file'] ?? null;
        $techOnly = $options['tech_only'] ?? true;
        $minIncorporationYear = $options['min_year'] ?? 2015;
        $resumeOffset = $options['resume_from'] ?? 0;

        if (! $filePath) {
            $filePath = $this->downloadCsv();
        }

        if (! file_exists($filePath)) {
            throw new \RuntimeException("CSV file not found: {$filePath}");
        }

        Log::info('Companies House CSV import starting', [
            'file' => $filePath,
            'tech_only' => $techOnly,
            'min_year' => $minIncorporationYear,
        ]);

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            throw new \RuntimeException("Cannot open CSV: {$filePath}");
        }

        // Read and normalize header
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            throw new \RuntimeException('Empty CSV file');
        }

        // Companies House headers use spaces and dots
        $header = array_map(function ($h) {
            return strtolower(trim(str_replace(' ', '', $h)));
        }, $header);

        $line = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            if ($line <= $resumeOffset) {
                continue;
            }

            if (count($row) !== count($header)) {
                continue;
            }

            $data = @array_combine($header, $row);
            if (! $data) {
                continue;
            }

            // Filter by incorporation date
            $incDate = $data['incorporationdate'] ?? '';
            if ($incDate && $minIncorporationYear) {
                $year = (int) substr($incDate, 6, 4); // DD/MM/YYYY format
                if (! $year) {
                    $year = (int) substr($incDate, 0, 4); // YYYY-MM-DD format
                }
                if ($year > 0 && $year < $minIncorporationYear) {
                    continue;
                }
            }

            // Filter by SIC code if tech_only
            if ($techOnly) {
                $sicCodes = $this->extractSicCodes($data);
                $category = $this->categorizeBySic($sicCodes);
                if (! $category) {
                    continue;
                }
            }

            $this->importRow($data, $category ?? null);

            if ($line % 10000 === 0) {
                $this->importLog->update([
                    'last_offset' => (string) $line,
                    'total_processed' => $this->processed,
                    'companies_created' => $this->created,
                ]);
                Log::info("Companies House CSV: scanned {$line} rows, {$this->created} created");
            }
        }

        fclose($handle);
        Log::info('Companies House CSV import complete', $this->getStats());
    }

    private function importRow(array $data, ?string $category): void
    {
        $name = $data['companyname'] ?? '';
        if (! $name || ! trim($name)) {
            return;
        }

        // Clean ALL CAPS names
        if ($name === strtoupper($name) && strlen($name) > 3) {
            $name = ucwords(strtolower($name));
        }

        $status = $this->mapCompaniesHouseStatus($data['companystatus'] ?? '');

        // Parse incorporation date (DD/MM/YYYY or YYYY-MM-DD)
        $foundedDate = null;
        $incDate = $data['incorporationdate'] ?? '';
        if ($incDate) {
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $incDate, $m)) {
                $foundedDate = "{$m[3]}-{$m[2]}-{$m[1]}";
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $incDate)) {
                $foundedDate = $incDate;
            }
        }

        $city = $data['regaddress.posttown'] ?? null;
        $county = $data['regaddress.county'] ?? null;

        $this->upsertCompany([
            'name' => trim($name),
            'founded_date' => $foundedDate,
            'city' => $city,
            'state' => $county,
            'country' => 'GB',
            'status' => $status,
            'category' => $category,
        ]);
    }

    private function extractSicCodes(array $data): array
    {
        $codes = [];
        for ($i = 1; $i <= 4; $i++) {
            $key = "siccode.sictext_{$i}";
            $value = $data[$key] ?? '';
            if ($value && trim($value)) {
                // Extract numeric SIC code from "62012 - Business and domestic software development"
                if (preg_match('/^(\d+)/', trim($value), $m)) {
                    $codes[] = $m[1];
                }
            }
        }

        return $codes;
    }

    private function categorizeBySic(array $sicCodes): ?string
    {
        foreach ($sicCodes as $code) {
            $prefix = substr($code, 0, 2);
            if (isset(self::TECH_SIC_PREFIXES[$prefix])) {
                return self::TECH_SIC_PREFIXES[$prefix];
            }
        }

        return null;
    }

    private function mapCompaniesHouseStatus(string $status): string
    {
        $status = strtolower(trim($status));

        $map = [
            'active' => 'operating',
            'active - proposal to strike off' => 'operating',
            'dissolved' => 'closed',
            'liquidation' => 'closed',
            'administration' => 'closed',
            'voluntary arrangement' => 'closed',
            'converted/closed' => 'closed',
            'insolvency proceedings' => 'closed',
            'receivership' => 'closed',
            'live' => 'operating',
        ];

        return $map[$status] ?? 'operating';
    }

    private function downloadCsv(): string
    {
        // Try current month, then previous month
        $dates = [
            now()->format('Y-m-01'),
            now()->subMonth()->format('Y-m-01'),
        ];

        $storageDir = storage_path('app/imports/companies-house');
        if (! is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        foreach ($dates as $date) {
            $url = sprintf(self::DOWNLOAD_URL, $date);
            $zipPath = "{$storageDir}/BasicCompanyData-{$date}.zip";
            $csvPath = "{$storageDir}/BasicCompanyData-{$date}.csv";

            // If CSV already extracted, use it
            if (file_exists($csvPath)) {
                Log::info("Using existing Companies House CSV: {$csvPath}");

                return $csvPath;
            }

            // Check for any existing CSV
            $existingCsvs = glob("{$storageDir}/*.csv");
            if (! empty($existingCsvs)) {
                $latest = end($existingCsvs);
                Log::info("Using existing Companies House CSV: {$latest}");

                return $latest;
            }

            Log::info("Downloading Companies House data from: {$url}");
            Log::info('This is ~500MB and will take a while...');

            // Download with curl for progress and resume support
            $exitCode = 0;
            $output = [];
            exec('curl -L -f -o '.escapeshellarg($zipPath).' '.escapeshellarg($url).' 2>&1', $output, $exitCode);

            if ($exitCode !== 0) {
                Log::warning("Failed to download from {$url}");

                continue;
            }

            // Extract ZIP
            $zip = new \ZipArchive;
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($storageDir);
                $zip->close();
                unlink($zipPath);

                // Find the extracted CSV
                $csvFiles = glob("{$storageDir}/*.csv");
                if (! empty($csvFiles)) {
                    return $csvFiles[0];
                }
            }
        }

        throw new \RuntimeException(
            'Could not download Companies House data. '.
            'Please download manually from https://download.companieshouse.gov.uk/en_output.html '.
            'and pass --file=/path/to/csv'
        );
    }
}
