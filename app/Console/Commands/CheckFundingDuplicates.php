<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\FundingRoundDeduplicationService;
use Illuminate\Console\Command;

class CheckFundingDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'funding:check-duplicates
                            {--company= : Check duplicates for a specific company slug}
                            {--date-tolerance=30 : Number of days for date comparison}
                            {--amount-tolerance=10 : Percentage tolerance for amount comparison}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for potential duplicate funding rounds in the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $service = new FundingRoundDeduplicationService;

        // Apply custom tolerances if provided
        $dateTolerance = (int) $this->option('date-tolerance');
        $amountTolerance = ((float) $this->option('amount-tolerance')) / 100;

        $service->setDateTolerance($dateTolerance);
        $service->setAmountTolerance($amountTolerance);

        $this->info('Checking for potential duplicate funding rounds...');
        $this->info("Date tolerance: {$dateTolerance} days");
        $this->info('Amount tolerance: '.($amountTolerance * 100).'%');
        $this->newLine();

        if ($companySlug = $this->option('company')) {
            return $this->checkForCompany($service, $companySlug);
        }

        return $this->checkAll($service);
    }

    /**
     * Check duplicates for a specific company.
     */
    protected function checkForCompany(FundingRoundDeduplicationService $service, string $slug): int
    {
        $company = Company::where('slug', $slug)->first();

        if (! $company) {
            $this->error("Company with slug '{$slug}' not found.");

            return 1;
        }

        $duplicates = $service->findDuplicatesForCompany($company);

        if ($duplicates->isEmpty()) {
            $this->info("No potential duplicates found for {$company->name}.");

            return 0;
        }

        $this->warn('Found '.$duplicates->count()." potential duplicate pair(s) for {$company->name}:");
        $this->newLine();

        $this->outputDuplicates($duplicates);

        return 0;
    }

    /**
     * Check duplicates for all companies.
     */
    protected function checkAll(FundingRoundDeduplicationService $service): int
    {
        $results = $service->findAllDuplicates();

        if ($results->isEmpty()) {
            $this->info('No potential duplicates found across all companies.');

            return 0;
        }

        $totalPairs = $results->sum(fn ($r) => $r['duplicates']->count());
        $this->warn("Found {$totalPairs} potential duplicate pair(s) across ".$results->count().' company(ies):');
        $this->newLine();

        foreach ($results as $result) {
            $this->info('Company: '.$result['company']->name);
            $this->outputDuplicates($result['duplicates']);
            $this->newLine();
        }

        return 0;
    }

    /**
     * Output duplicate pairs to the console.
     */
    protected function outputDuplicates($duplicates): void
    {
        foreach ($duplicates as $pair) {
            $round1 = $pair['round1'];
            $round2 = $pair['round2'];

            $this->table(
                ['Property', 'Round 1', 'Round 2'],
                [
                    ['ID', $round1->id, $round2->id],
                    ['Type', $round1->round_type, $round2->round_type],
                    ['Date', $round1->announced_date->format('Y-m-d'), $round2->announced_date->format('Y-m-d')],
                    ['Amount', $this->formatAmount($round1->amount), $this->formatAmount($round2->amount)],
                    ['Source URL', $round1->source_url ?? '(none)', $round2->source_url ?? '(none)'],
                ]
            );

            $this->line("  Date difference: {$pair['date_diff_days']} days");
            if ($pair['amount_diff_percent'] !== null) {
                $this->line("  Amount difference: {$pair['amount_diff_percent']}%");
            }
            $this->newLine();
        }
    }

    /**
     * Format an amount for display.
     */
    protected function formatAmount(?float $amount): string
    {
        if ($amount === null) {
            return '(none)';
        }

        if ($amount >= 1_000_000_000) {
            return '$'.round($amount / 1_000_000_000, 2).'B';
        }

        if ($amount >= 1_000_000) {
            return '$'.round($amount / 1_000_000, 1).'M';
        }

        return '$'.number_format($amount);
    }
}
