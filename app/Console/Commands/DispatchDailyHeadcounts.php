<?php

namespace App\Console\Commands;

use App\Jobs\FetchCompanyHeadcountJob;
use App\Models\Company;
use Illuminate\Console\Command;

class DispatchDailyHeadcounts extends Command
{
    protected $signature = 'schedule:headcounts
                            {--force : Fetch all companies regardless of assigned day}
                            {--assign-only : Only assign fetch days, do not dispatch jobs}
                            {--day= : Override the day of month (1-25) to process}';

    protected $description = 'Dispatch headcount fetch jobs for today\'s scheduled companies';

    public function handle(): int
    {
        // Determine which day to process
        $dayOfMonth = $this->option('day')
            ? (int) $this->option('day')
            : min(now()->day, 25); // Cap at day 25

        // Auto-assign unassigned companies
        $assigned = $this->assignFetchDays();
        if ($assigned > 0) {
            $this->info("Assigned fetch days to {$assigned} companies.");
        }

        if ($this->option('assign-only')) {
            $this->showDistribution();
            return self::SUCCESS;
        }

        // Get companies to process
        $query = Company::whereNotNull('linkedin_url');

        if (!$this->option('force')) {
            $query->where('headcount_fetch_day', $dayOfMonth);
        }

        $companies = $query->get();

        if ($companies->isEmpty()) {
            $this->info("No companies scheduled for day {$dayOfMonth}.");
            return self::SUCCESS;
        }

        $this->info("Dispatching headcount jobs for {$companies->count()} companies (day {$dayOfMonth})...");

        foreach ($companies as $company) {
            FetchCompanyHeadcountJob::dispatch($company);
            $this->line("  Dispatched: {$company->name}");
        }

        $this->newLine();
        $this->info("Done! {$companies->count()} jobs dispatched to queue.");
        $this->comment("Run 'php artisan queue:work' to process the jobs.");

        return self::SUCCESS;
    }

    private function assignFetchDays(): int
    {
        $unassigned = Company::whereNotNull('linkedin_url')
            ->whereNull('headcount_fetch_day')
            ->get();

        if ($unassigned->isEmpty()) {
            return 0;
        }

        // Get current distribution to balance assignments
        $distribution = Company::whereNotNull('headcount_fetch_day')
            ->selectRaw('headcount_fetch_day, COUNT(*) as count')
            ->groupBy('headcount_fetch_day')
            ->pluck('count', 'headcount_fetch_day')
            ->toArray();

        // Fill in missing days with 0
        for ($day = 1; $day <= 25; $day++) {
            if (!isset($distribution[$day])) {
                $distribution[$day] = 0;
            }
        }

        $assigned = 0;
        foreach ($unassigned as $company) {
            // Find day with fewest companies
            $minDay = array_keys($distribution, min($distribution))[0];
            $company->update(['headcount_fetch_day' => $minDay]);
            $distribution[$minDay]++;
            $assigned++;
        }

        return $assigned;
    }

    private function showDistribution(): void
    {
        $distribution = Company::whereNotNull('headcount_fetch_day')
            ->selectRaw('headcount_fetch_day, COUNT(*) as count')
            ->groupBy('headcount_fetch_day')
            ->orderBy('headcount_fetch_day')
            ->get();

        $this->newLine();
        $this->info('Company Distribution by Fetch Day:');
        $this->table(
            ['Day', 'Companies'],
            $distribution->map(fn ($row) => [$row->headcount_fetch_day, $row->count])->toArray()
        );

        $total = $distribution->sum('count');
        $this->info("Total: {$total} companies across 25 days");
    }
}
