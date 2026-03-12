<?php

namespace App\Console\Commands;

use App\Models\OpenSourceProject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshOssStars extends Command
{
    protected $signature = 'app:refresh-oss-stars {--limit=0 : Max projects to refresh (0 = all)}';

    protected $description = 'Refresh star counts, forks, and last commit dates for all OSS projects';

    public function handle(): int
    {
        $token = config('services.github.token') ?: env('GITHUB_TOKEN');
        $limit = (int) $this->option('limit');

        $query = OpenSourceProject::query()->orderBy('updated_at', 'asc');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $projects = $query->get();
        $this->info("Refreshing {$projects->count()} OSS projects...");

        $bar = $this->output->createProgressBar($projects->count());
        $updated = 0;
        $errors = 0;

        foreach ($projects as $project) {
            try {
                $request = Http::timeout(15)
                    ->withHeaders([
                        'Accept' => 'application/vnd.github+json',
                        'X-GitHub-Api-Version' => '2022-11-28',
                    ]);

                if ($token) {
                    $request = $request->withToken($token);
                }

                $response = $request->get("https://api.github.com/repos/{$project->github_owner}/{$project->github_repo}");

                if ($response->successful()) {
                    $repo = $response->json();
                    $project->update([
                        'stars' => $repo['stargazers_count'] ?? $project->stars,
                        'forks' => $repo['forks_count'] ?? $project->forks,
                        'watchers' => $repo['watchers_count'] ?? $project->watchers,
                        'last_commit_at' => $repo['pushed_at'] ?? $project->last_commit_at,
                        'primary_language' => $repo['language'] ?? $project->primary_language,
                        'license' => $repo['license']['spdx_id'] ?? $project->license,
                        'topics' => $repo['topics'] ?? $project->topics,
                    ]);
                    $updated++;
                } elseif ($response->status() === 403) {
                    // Rate limited
                    $resetAt = $response->header('X-RateLimit-Reset');
                    $waitSeconds = $resetAt ? max(1, (int) $resetAt - time()) : 60;
                    $this->warn("\nRate limited. Waiting {$waitSeconds}s...");
                    sleep(min($waitSeconds, 300));

                    continue;
                } else {
                    Log::debug("Failed to refresh {$project->github_owner}/{$project->github_repo}: {$response->status()}");
                    $errors++;
                }
            } catch (\Throwable $e) {
                Log::debug("Error refreshing {$project->github_owner}/{$project->github_repo}: {$e->getMessage()}");
                $errors++;
            }

            $bar->advance();
            sleep(1); // Rate limit: ~1 request per second
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done: {$updated} updated, {$errors} errors");

        return self::SUCCESS;
    }
}
