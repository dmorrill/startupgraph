<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Log;

class CrunchbaseCsvImporter extends BaseBulkImporter
{
    private string $filePath;

    public function source(): string
    {
        return 'crunchbase';
    }

    public function import(array $options = []): void
    {
        $this->filePath = $options['file'] ?? '';

        if (! file_exists($this->filePath)) {
            throw new \RuntimeException("CSV file not found: {$this->filePath}");
        }

        $handle = fopen($this->filePath, 'r');
        if (! $handle) {
            throw new \RuntimeException("Cannot open CSV: {$this->filePath}");
        }

        // Read header
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            throw new \RuntimeException('Empty CSV file');
        }

        $header = array_map('strtolower', array_map('trim', $header));
        $resumeOffset = $options['resume_offset'] ?? 0;
        $line = 0;

        Log::info("Crunchbase CSV import starting: {$this->filePath}");

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            if ($line <= $resumeOffset) {
                continue;
            }

            $data = @array_combine($header, $row);
            if (! $data) {
                continue;
            }

            $this->importRow($data);

            if ($line % 1000 === 0) {
                $this->importLog->update([
                    'last_offset' => (string) $line,
                    'total_processed' => $this->processed,
                    'companies_created' => $this->created,
                ]);
                Log::info("Crunchbase CSV: processed {$line} rows, {$this->created} created");
            }
        }

        fclose($handle);
        Log::info("Crunchbase CSV import complete: {$this->created} created, {$this->updated} updated, {$this->skipped} skipped");
    }

    private function importRow(array $row): void
    {
        $name = $row['name'] ?? ($row['organization_name'] ?? null);
        if (! $name || ! trim($name)) {
            return;
        }

        $status = $this->mapStatus($row['status'] ?? ($row['operating_status'] ?? 'operating'));

        $headcount = null;
        $empRange = $row['employee_count'] ?? ($row['num_employees_enum'] ?? null);
        if ($empRange) {
            $headcount = $this->parseEmployeeRange($empRange);
        }

        $foundedDate = null;
        if (! empty($row['founded_on'])) {
            $foundedDate = $row['founded_on'];
        } elseif (! empty($row['founded_date'])) {
            $foundedDate = $row['founded_date'];
        }

        $category = $this->mapCrunchbaseCategory($row['category_list'] ?? ($row['category_groups_list'] ?? ''));

        $this->upsertCompany([
            'name' => trim($name),
            'description' => $row['short_description'] ?? ($row['description'] ?? null),
            'website' => $row['homepage_url'] ?? ($row['website'] ?? null),
            'founded_date' => $foundedDate,
            'city' => $row['city'] ?? null,
            'state' => $row['region'] ?? ($row['state_code'] ?? null),
            'country' => $row['country'] ?? ($row['country_code'] ?? null),
            'status' => $status,
            'current_headcount' => $headcount,
            'category' => $category,
        ]);
    }

    private function parseEmployeeRange(string $range): ?int
    {
        // Crunchbase formats: "1-10", "11-50", "51-100", "101-250", "251-500", "501-1000", "1001-5000", "5001-10000", "10001+"
        $map = [
            'c_00001_00010' => 5,
            'c_00011_00050' => 30,
            'c_00051_00100' => 75,
            'c_00101_00250' => 175,
            'c_00251_00500' => 375,
            'c_00501_01000' => 750,
            'c_01001_05000' => 3000,
            'c_05001_10000' => 7500,
            'c_10001_max' => 15000,
        ];

        if (isset($map[$range])) {
            return $map[$range];
        }

        // Try parsing "N-M" format
        if (preg_match('/(\d+)\s*[-–]\s*(\d+)/', $range, $m)) {
            return (int) (((int) $m[1] + (int) $m[2]) / 2);
        }
        if (preg_match('/(\d+)\+/', $range, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function mapCrunchbaseCategory(string $categories): ?string
    {
        $categories = strtolower($categories);

        $map = [
            'artificial intelligence' => 'ai_ml',
            'machine learning' => 'ai_ml',
            'financial services' => 'fintech',
            'fintech' => 'fintech',
            'health care' => 'healthcare',
            'biotechnology' => 'healthcare',
            'software' => 'enterprise',
            'enterprise software' => 'enterprise',
            'consumer' => 'consumer',
            'hardware' => 'robotics',
            'robotics' => 'robotics',
            'energy' => 'climate',
            'sustainability' => 'climate',
            'defense' => 'defense',
        ];

        foreach ($map as $keyword => $category) {
            if (str_contains($categories, $keyword)) {
                return $category;
            }
        }

        return null;
    }
}
