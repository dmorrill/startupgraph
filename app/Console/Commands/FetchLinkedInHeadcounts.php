<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\HeadcountSnapshot;
use App\Services\LinkedInService;
use Illuminate\Console\Command;

class FetchLinkedInHeadcounts extends Command
{
    protected $signature = 'headcount:fetch-linkedin
                            {--company= : Specific company slug to fetch}
                            {--limit=10 : Maximum number of companies to process}
                            {--dry-run : Show what would be fetched without saving}';

    protected $description = 'Fetch employee headcounts from LinkedIn company pages';

    public function handle(LinkedInService $linkedInService): int
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

            $result = $linkedInService->fetchHeadcount($company->linkedin_url);

            if (!$result['success']) {
                $this->error("  Failed: {$result['error']}");
                $failed++;
                continue;
            }

            $headcount = $result['headcount'];
            $this->info("  Found: {$headcount} employees");

            if ($this->option('dry-run')) {
                $this->comment("  [Dry run - not saving]");
                continue;
            }

            // Update current headcount and fetch timestamp
            $company->update([
                'current_headcount' => $headcount,
                'headcount_fetched_at' => now(),
            ]);

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
}
