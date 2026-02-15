<?php

namespace App\Console\Commands;

use App\Jobs\FetchNewsMentionsJob;
use App\Models\Company;
use Illuminate\Console\Command;

class FetchNews extends Command
{
    protected $signature = 'news:fetch
                            {--company= : Fetch news for a specific company (by slug or name)}
                            {--limit= : Limit the number of companies to process}
                            {--delay=5 : Delay in seconds between job dispatches for rate limiting}';

    protected $description = 'Fetch news mentions for tracked companies from TechCrunch';

    public function handle(): int
    {
        $companyOption = $this->option('company');
        $limit = $this->option('limit');
        $delay = (int) $this->option('delay');

        if ($companyOption) {
            return $this->fetchForSingleCompany($companyOption);
        }

        return $this->fetchForAllCompanies($limit, $delay);
    }

    /**
     * Fetch news for a single company.
     */
    private function fetchForSingleCompany(string $identifier): int
    {
        // Escape LIKE special characters to prevent SQL injection
        $escapedIdentifier = str_replace(['%', '_'], ['\\%', '\\_'], $identifier);

        // Try to find by slug first, then by name
        $company = Company::where('slug', $identifier)
            ->orWhere('name', 'LIKE', "%{$escapedIdentifier}%")
            ->first();

        if (!$company) {
            $this->error("Company not found: {$identifier}");
            return self::FAILURE;
        }

        $this->info("Fetching news for: {$company->name}");
        FetchNewsMentionsJob::dispatch($company);
        $this->info("Job dispatched for {$company->name}");
        $this->comment("Run 'php artisan queue:work' to process the job.");

        return self::SUCCESS;
    }

    /**
     * Fetch news for all companies with rate limiting.
     *
     * Uses chunked queries to avoid loading all companies into memory.
     * Note: Laravel's chunk() ignores limit(), so we enforce the limit manually.
     */
    private function fetchForAllCompanies(?string $limit, int $delay): int
    {
        $maxCompanies = $limit ? (int) $limit : null;
        $total = $maxCompanies
            ? min($maxCompanies, Company::count())
            : Company::count();

        if ($total === 0) {
            $this->info('No companies found.');
            return self::SUCCESS;
        }

        $this->info("Dispatching news fetch jobs for {$total} companies...");
        $this->info("Rate limiting: {$delay} seconds between dispatches");
        $this->newLine();

        $index = 0;

        Company::chunk(100, function ($companies) use ($delay, $maxCompanies, &$index) {
            foreach ($companies as $company) {
                if ($maxCompanies !== null && $index >= $maxCompanies) {
                    return false; // Stop chunking
                }

                $delaySeconds = $index * $delay;
                FetchNewsMentionsJob::dispatch($company)->delay(now()->addSeconds($delaySeconds));

                $this->line("  Dispatched: {$company->name}" . ($delaySeconds > 0 ? " (delay: {$delaySeconds}s)" : ''));
                $index++;
            }

            // Stop chunking if we've hit the limit
            if ($maxCompanies !== null && $index >= $maxCompanies) {
                return false;
            }
        });

        $this->newLine();
        $this->info("Done! {$index} jobs dispatched to queue.");
        $this->comment("Run 'php artisan queue:work' to process the jobs.");

        return self::SUCCESS;
    }
}
