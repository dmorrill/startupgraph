<?php

namespace App\Console\Commands;

use App\Models\FundingRound;
use App\Models\ScheduledTaskExecution;
use App\Services\TechCrunchService;
use Illuminate\Console\Command;

class ScrapeTechCrunchFunding extends Command
{
    protected $signature = 'schedule:funding
                            {--dry-run : Show what would be created without saving}';

    protected $description = 'Scrape TechCrunch for funding announcements of tracked companies';

    public function handle(TechCrunchService $techCrunchService): int
    {
        $isDryRun = $this->option('dry-run');

        $execution = null;
        if (! $isDryRun) {
            $execution = ScheduledTaskExecution::create([
                'task_type' => 'funding_scrape',
                'status' => 'running',
                'started_at' => now(),
            ]);
        }

        try {
            $this->info('Fetching TechCrunch fundraising articles...');

            $result = $techCrunchService->scrapeFundraisingArticles();

            if (! $result['success']) {
                throw new \RuntimeException($result['error'] ?? 'Failed to scrape TechCrunch');
            }

            $articles = $result['articles'];
            $this->info('Found '.count($articles).' funding-related articles.');

            if (empty($articles)) {
                $execution?->update([
                    'status' => 'success',
                    'completed_at' => now(),
                    'metadata' => ['articles_found' => 0, 'matches' => 0, 'created' => 0],
                ]);
                $this->info('No articles to process.');

                return self::SUCCESS;
            }

            // Match articles to tracked companies
            $matches = $techCrunchService->matchArticlesToCompanies($articles);
            $this->info("Matched {$matches->count()} articles to tracked companies.");

            if ($matches->isEmpty()) {
                $execution?->update([
                    'status' => 'success',
                    'completed_at' => now(),
                    'metadata' => [
                        'articles_found' => count($articles),
                        'matches' => 0,
                        'created' => 0,
                    ],
                ]);
                $this->info('No matches found for tracked companies.');

                return self::SUCCESS;
            }

            $created = 0;
            $skipped = 0;

            foreach ($matches as $match) {
                $this->newLine();
                $this->line("Company: {$match['company_name']}");
                $this->line("Article: {$match['article_title']}");

                $fundingInfo = $match['funding_info'];
                if (isset($fundingInfo['amount'])) {
                    $this->line('Amount: $'.number_format($fundingInfo['amount']));
                }
                if (isset($fundingInfo['round_type'])) {
                    $this->line("Round: {$fundingInfo['round_type']}");
                }

                if ($isDryRun) {
                    $this->comment('[Dry run - not saving]');

                    continue;
                }

                // Check if this round already exists (avoid duplicates)
                $exists = FundingRound::where('company_id', $match['company_id'])
                    ->where('source_url', $match['article_url'])
                    ->exists();

                if ($exists) {
                    $this->comment('  Already exists, skipping.');
                    $skipped++;

                    continue;
                }

                // Create the funding round
                FundingRound::create([
                    'company_id' => $match['company_id'],
                    'round_type' => $fundingInfo['round_type'] ?? 'unknown',
                    'amount' => $fundingInfo['amount'] ?? null,
                    'currency' => 'USD',
                    'announced_date' => now()->toDateString(),
                    'source_url' => $match['article_url'],
                ]);

                $this->info('  Created new funding round!');
                $created++;
            }

            $execution?->update([
                'status' => 'success',
                'completed_at' => now(),
                'metadata' => [
                    'articles_found' => count($articles),
                    'matches' => $matches->count(),
                    'created' => $created,
                    'skipped' => $skipped,
                ],
            ]);

            $this->newLine();
            $this->info("Done! Created: {$created}, Skipped (duplicates): {$skipped}");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $execution?->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            $this->error("Error: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
