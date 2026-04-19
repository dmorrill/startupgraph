<?php

namespace App\Services\BulkImport;

use App\Models\Company;
use App\Models\CompanyImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class BaseBulkImporter
{
    protected CompanyImport $importLog;

    protected int $created = 0;

    protected int $updated = 0;

    protected int $skipped = 0;

    protected int $processed = 0;

    abstract public function source(): string;

    abstract public function import(array $options = []): void;

    public function start(array $options = []): CompanyImport
    {
        $this->importLog = CompanyImport::create([
            'source' => $this->source(),
            'batch_id' => Str::uuid()->toString(),
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $this->import($options);
            $this->importLog->update([
                'status' => 'completed',
                'companies_created' => $this->created,
                'companies_updated' => $this->updated,
                'companies_skipped' => $this->skipped,
                'total_processed' => $this->processed,
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->importLog->update([
                'status' => 'failed',
                'companies_created' => $this->created,
                'companies_updated' => $this->updated,
                'companies_skipped' => $this->skipped,
                'total_processed' => $this->processed,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            Log::error("Bulk import [{$this->source()}] failed: {$e->getMessage()}");
            throw $e;
        }

        return $this->importLog;
    }

    protected function upsertCompany(array $data): void
    {
        $this->processed++;

        $domain = $this->extractDomain($data['website'] ?? null);
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->skipped++;

            return;
        }

        // Find existing by domain first, then by name
        $existing = null;
        if ($domain) {
            $existing = Company::where('website', 'LIKE', "%{$domain}%")->first();
        }
        if (! $existing) {
            $existing = Company::where('name', $name)->first();
        }

        $slug = $data['slug'] ?? Str::slug($name);
        // Ensure unique slug
        if (! $existing && Company::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.Str::random(4);
        }

        $attributes = array_filter([
            'name' => $name,
            'slug' => $slug,
            'website' => $data['website'] ?? null,
            'description' => $data['description'] ?? null,
            'founded_date' => $data['founded_date'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
            'category' => $data['category'] ?? null,
            'current_headcount' => $data['current_headcount'] ?? null,
            'status' => $data['status'] ?? 'operating',
            'closed_at' => $data['closed_at'] ?? null,
            'acquired_by' => $data['acquired_by'] ?? null,
            'import_source' => $this->source(),
        ], fn ($v) => $v !== null);

        if ($existing) {
            // Only update null fields — don't overwrite existing data
            $updates = [];
            foreach ($attributes as $key => $value) {
                if ($key === 'slug' || $key === 'name') {
                    continue;
                }
                if (empty($existing->$key) && ! empty($value)) {
                    $updates[$key] = $value;
                }
            }
            if (! empty($updates)) {
                $existing->update($updates);
                $this->updated++;
            } else {
                $this->skipped++;
            }
        } else {
            Company::create($attributes);
            $this->created++;
        }
    }

    protected function extractDomain(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return null;
        }

        return preg_replace('/^www\./', '', strtolower($host));
    }

    protected function mapStatus(string $status): string
    {
        $map = [
            'active' => 'operating',
            'operating' => 'operating',
            'alive' => 'operating',
            'inactive' => 'closed',
            'closed' => 'closed',
            'dead' => 'closed',
            'acquired' => 'acquired',
            'ipo' => 'ipo',
            'public' => 'ipo',
        ];

        return $map[strtolower(trim($status))] ?? 'operating';
    }

    protected function rateLimitSleep(float $seconds = 1.0): void
    {
        usleep((int) ($seconds * 1_000_000));
    }

    public function getStats(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'processed' => $this->processed,
        ];
    }
}
