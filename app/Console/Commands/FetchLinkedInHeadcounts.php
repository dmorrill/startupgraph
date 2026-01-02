<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\HeadcountSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchLinkedInHeadcounts extends Command
{
    protected $signature = 'headcount:fetch-linkedin
                            {--company= : Specific company slug to fetch}
                            {--limit=10 : Maximum number of companies to process}
                            {--dry-run : Show what would be fetched without saving}';

    protected $description = 'Fetch employee headcounts from LinkedIn company pages';

    public function handle(): int
    {
        $query = Company::whereNotNull('linkedin_url');

        if ($slug = $this->option('company')) {
            $query->where('slug', $slug);
        }

        $companies = $query->limit($this->option('limit'))->get();

        if ($companies->isEmpty()) {
            $this->warn('No companies with LinkedIn URLs found.');
            return self::SUCCESS;
        }

        $this->info("Processing {$companies->count()} companies...");
        $this->newLine();

        $updated = 0;
        $failed = 0;

        foreach ($companies as $company) {
            $this->line("Fetching: {$company->name}");

            $headcount = $this->fetchHeadcount($company->linkedin_url);

            if ($headcount === null) {
                $this->error("  Failed to extract headcount");
                $failed++;
                continue;
            }

            $this->info("  Found: {$headcount} employees");

            if ($this->option('dry-run')) {
                $this->comment("  [Dry run - not saving]");
                continue;
            }

            // Update current headcount
            $company->update(['current_headcount' => $headcount]);

            // Create snapshot if different from last one
            $lastSnapshot = $company->headcountSnapshots()
                ->orderBy('recorded_date', 'desc')
                ->first();

            if (!$lastSnapshot || $lastSnapshot->headcount !== $headcount) {
                HeadcountSnapshot::create([
                    'company_id' => $company->id,
                    'headcount' => $headcount,
                    'recorded_date' => now()->toDateString(),
                    'source' => 'linkedin',
                ]);
                $this->info("  Saved new snapshot");
            } else {
                $this->comment("  Headcount unchanged, skipping snapshot");
            }

            $updated++;

            // Be nice to LinkedIn - add a small delay
            sleep(2);
        }

        $this->newLine();
        $this->info("Done! Updated: {$updated}, Failed: {$failed}");

        return self::SUCCESS;
    }

    private function fetchHeadcount(string $linkedinUrl): ?int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ])->timeout(30)->get($linkedinUrl);

            if (!$response->successful()) {
                $this->error("  HTTP {$response->status()}");
                return null;
            }

            $html = $response->body();

            // Look for JSON-LD schema with numberOfEmployees
            if (preg_match('/"numberOfEmployees"\s*:\s*\{\s*"value"\s*:\s*(\d+)/', $html, $matches)) {
                return (int) $matches[1];
            }

            // Fallback: look for "X employees" pattern
            if (preg_match('/(\d{1,3}(?:,\d{3})*)\s+employees/i', $html, $matches)) {
                return (int) str_replace(',', '', $matches[1]);
            }

            return null;
        } catch (\Exception $e) {
            $this->error("  Error: {$e->getMessage()}");
            return null;
        }
    }
}
