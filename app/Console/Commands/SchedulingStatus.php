<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\ScheduledTaskExecution;
use Illuminate\Console\Command;

class SchedulingStatus extends Command
{
    protected $signature = 'schedule:status
                            {--days=7 : Number of days to look back for executions}
                            {--task= : Filter by task type (headcount_fetch, funding_scrape)}';

    protected $description = 'Show scheduling status and recent task execution history';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $taskType = $this->option('task');

        $this->showSummary($days, $taskType);
        $this->showRecentFailures($days, $taskType);
        $this->showCompanyDistribution();

        return self::SUCCESS;
    }

    private function showSummary(int $days, ?string $taskType): void
    {
        $this->info("=== Execution Summary (Last {$days} days) ===");
        $this->newLine();

        $query = ScheduledTaskExecution::recent($days);

        if ($taskType) {
            $query->forTaskType($taskType);
        }

        $stats = $query->selectRaw("
            task_type,
            COUNT(*) as total,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running
        ")
            ->groupBy('task_type')
            ->get();

        if ($stats->isEmpty()) {
            $this->comment('No task executions found.');
            $this->newLine();

            return;
        }

        $this->table(
            ['Task Type', 'Total', 'Success', 'Failed', 'Running', 'Success Rate'],
            $stats->map(function ($row) {
                $rate = $row->total > 0
                    ? round(($row->success / $row->total) * 100, 1).'%'
                    : 'N/A';

                return [
                    $row->task_type,
                    $row->total,
                    $row->success,
                    $row->failed,
                    $row->running,
                    $rate,
                ];
            })->toArray()
        );

        $this->newLine();
    }

    private function showRecentFailures(int $days, ?string $taskType): void
    {
        $this->info('=== Recent Failures ===');
        $this->newLine();

        $query = ScheduledTaskExecution::recent($days)
            ->failed()
            ->with('company')
            ->orderBy('created_at', 'desc')
            ->limit(10);

        if ($taskType) {
            $query->forTaskType($taskType);
        }

        $failures = $query->get();

        if ($failures->isEmpty()) {
            $this->comment('No failures found. Great!');
            $this->newLine();

            return;
        }

        $this->table(
            ['Time', 'Task', 'Company', 'Error'],
            $failures->map(function ($execution) {
                $companyName = $execution->company?->name ?? 'N/A';
                $error = $execution->error_message
                    ? (strlen($execution->error_message) > 50
                        ? substr($execution->error_message, 0, 47).'...'
                        : $execution->error_message)
                    : 'Unknown error';

                return [
                    $execution->created_at->format('M j H:i'),
                    $execution->task_type,
                    $companyName,
                    $error,
                ];
            })->toArray()
        );

        $this->newLine();
    }

    private function showCompanyDistribution(): void
    {
        $this->info('=== Company Distribution by Fetch Day ===');
        $this->newLine();

        $withLinkedIn = Company::whereNotNull('linkedin_url')->count();
        $assigned = Company::whereNotNull('headcount_fetch_day')->count();
        $unassigned = Company::whereNotNull('linkedin_url')
            ->whereNull('headcount_fetch_day')
            ->count();

        $this->line("Companies with LinkedIn URL: {$withLinkedIn}");
        $this->line("Assigned to fetch days: {$assigned}");
        $this->line("Unassigned: {$unassigned}");
        $this->newLine();

        if ($assigned === 0) {
            $this->comment('No companies assigned yet. Run: php artisan schedule:headcounts --assign-only');
            $this->newLine();

            return;
        }

        $distribution = Company::whereNotNull('headcount_fetch_day')
            ->selectRaw('headcount_fetch_day, COUNT(*) as count')
            ->groupBy('headcount_fetch_day')
            ->orderBy('headcount_fetch_day')
            ->get();

        // Show distribution in a compact format
        $rows = [];
        foreach ($distribution->chunk(5) as $chunk) {
            $row = [];
            foreach ($chunk as $item) {
                $row[] = "Day {$item->headcount_fetch_day}: {$item->count}";
            }
            $rows[] = $row;
        }

        foreach ($rows as $row) {
            $this->line('  '.implode('  |  ', $row));
        }

        $this->newLine();

        // Show today's schedule
        $today = min(now()->day, 25);
        $todayCount = Company::where('headcount_fetch_day', $today)->count();
        $this->line("Today is day {$today}: {$todayCount} companies scheduled");

        // Show last fetch times
        $recentlyFetched = Company::whereNotNull('headcount_fetched_at')
            ->where('headcount_fetched_at', '>=', now()->subDays(7))
            ->count();
        $neverFetched = Company::whereNotNull('linkedin_url')
            ->whereNull('headcount_fetched_at')
            ->count();

        $this->line("Fetched in last 7 days: {$recentlyFetched}");
        $this->line("Never fetched: {$neverFetched}");
        $this->newLine();
    }
}
