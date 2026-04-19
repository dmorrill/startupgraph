<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\NewsMention;
use App\Models\ScheduledTaskExecution;
use App\Services\NewsSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchNewsMentionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        public Company $company
    ) {}

    public function handle(NewsSearchService $newsSearchService): void
    {
        $execution = ScheduledTaskExecution::create([
            'task_type' => 'news_fetch',
            'company_id' => $this->company->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $result = $newsSearchService->searchCompanyNews($this->company);

            if (! $result['success']) {
                throw new \RuntimeException($result['error'] ?? 'Unknown error');
            }

            $articles = $result['articles'];
            $created = 0;
            $skipped = 0;

            foreach ($articles as $article) {
                // Validate URL before storing
                if (! filter_var($article['url'], FILTER_VALIDATE_URL)) {
                    Log::warning("Invalid URL skipped: {$article['url']}");
                    $skipped++;

                    continue;
                }

                // Use firstOrCreate for atomic deduplication (prevents race conditions)
                $mention = NewsMention::firstOrCreate(
                    ['url' => $article['url']], // Unique key
                    [
                        'company_id' => $this->company->id,
                        'title' => $article['title'],
                        'source' => $article['source'],
                        'published_date' => $article['published_date'],
                        'summary' => $article['summary'],
                    ]
                );

                if ($mention->wasRecentlyCreated) {
                    $created++;
                } else {
                    $skipped++;
                }
            }

            $execution->update([
                'status' => 'success',
                'completed_at' => now(),
                'metadata' => [
                    'articles_found' => count($articles),
                    'created' => $created,
                    'skipped' => $skipped,
                ],
            ]);

            Log::info("News fetch completed for {$this->company->name}: {$created} new, {$skipped} skipped");

        } catch (\Exception $e) {
            $execution->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::warning("News fetch failed for {$this->company->name}: {$e->getMessage()}");

            throw $e; // Re-throw to trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("News fetch job permanently failed for {$this->company->name}: {$exception->getMessage()}");
    }
}
