<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\HeadcountSnapshot;
use App\Models\ScheduledTaskExecution;
use App\Services\LinkedInService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchCompanyHeadcountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        public Company $company
    ) {}

    public function handle(LinkedInService $linkedInService): void
    {
        $execution = ScheduledTaskExecution::create([
            'task_type' => 'headcount_fetch',
            'company_id' => $this->company->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            if (! $this->company->linkedin_url) {
                throw new \RuntimeException('Company has no LinkedIn URL');
            }

            $result = $linkedInService->fetchHeadcount($this->company->linkedin_url);

            if (! $result['success']) {
                throw new \RuntimeException($result['error'] ?? 'Unknown error');
            }

            $headcount = $result['headcount'];

            // Update company's current headcount and fetch timestamp
            $this->company->update([
                'current_headcount' => $headcount,
                'headcount_fetched_at' => now(),
            ]);

            // Create snapshot if different from last one
            $lastSnapshot = $this->company->headcountSnapshots()
                ->orderBy('recorded_date', 'desc')
                ->first();

            $snapshotCreated = false;
            if (! $lastSnapshot || $lastSnapshot->headcount !== $headcount) {
                HeadcountSnapshot::create([
                    'company_id' => $this->company->id,
                    'headcount' => $headcount,
                    'recorded_date' => now()->toDateString(),
                    'source' => 'linkedin',
                ]);
                $snapshotCreated = true;
            }

            $execution->update([
                'status' => 'success',
                'completed_at' => now(),
                'metadata' => [
                    'headcount' => $headcount,
                    'snapshot_created' => $snapshotCreated,
                ],
            ]);

            Log::info("Headcount fetched for {$this->company->name}: {$headcount}");

        } catch (\Exception $e) {
            $execution->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::warning("Headcount fetch failed for {$this->company->name}: {$e->getMessage()}");

            throw $e; // Re-throw to trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Headcount fetch job permanently failed for {$this->company->name}: {$exception->getMessage()}");
    }
}
