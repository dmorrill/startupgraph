<?php

namespace App\Services;

use App\Contracts\CompanyDiscoverySource;
use App\Models\Company;
use App\Services\Discovery\CrunchbaseDiscoverySource;
use App\Services\Discovery\HackerNewsDiscoverySource;
use App\Services\Discovery\ProductHuntDiscoverySource;
use App\Services\Discovery\TechCrunchDiscoverySource;
use App\Services\Discovery\WellfoundDiscoverySource;
use App\Services\Discovery\YCombinatorDiscoverySource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DiscoverCompaniesService
{
    /** @var array<string, CompanyDiscoverySource> */
    private array $sources = [];

    public function __construct(
        TechCrunchDiscoverySource $techCrunch,
        YCombinatorDiscoverySource $yCombinator,
        CrunchbaseDiscoverySource $crunchbase,
        WellfoundDiscoverySource $wellfound,
        HackerNewsDiscoverySource $hackerNews,
        ProductHuntDiscoverySource $productHunt,
    ) {
        $this->registerSource($techCrunch);
        $this->registerSource($yCombinator);
        $this->registerSource($crunchbase);
        $this->registerSource($wellfound);
        $this->registerSource($hackerNews);
        $this->registerSource($productHunt);
    }

    public function registerSource(CompanyDiscoverySource $source): void
    {
        $this->sources[$source->name()] = $source;
    }

    public function getAvailableSources(): array
    {
        return array_keys($this->sources);
    }

    /**
     * Run discovery from specified source(s).
     *
     * @return array{discovered: array, created: array, existing: array, errors: array}
     */
    public function run(string $sourceName = 'all', int $days = 7, bool $dryRun = false): array
    {
        $results = [
            'discovered' => [],
            'created' => [],
            'existing' => [],
            'errors' => [],
        ];

        $sourcesToRun = $sourceName === 'all'
            ? $this->sources
            : [$sourceName => $this->sources[$sourceName] ?? null];

        foreach ($sourcesToRun as $name => $source) {
            if (! $source) {
                $results['errors'][] = "Unknown source: {$name}";

                continue;
            }

            try {
                $candidates = $source->discover($days);
                Log::info("Discovery [{$name}]: found ".count($candidates).' candidates');

                foreach ($candidates as $candidate) {
                    $companyName = $candidate['name'] ?? null;
                    if (! $companyName) {
                        continue;
                    }

                    $results['discovered'][] = array_merge($candidate, ['source' => $name]);

                    // Check if company already exists (by name or domain)
                    $existing = Company::whereRaw('LOWER(name) = ?', [strtolower($companyName)])->first();

                    if (! $existing && ! empty($candidate['website'])) {
                        $domain = $this->extractDomain($candidate['website']);
                        if ($domain) {
                            $existing = Company::where('website', 'LIKE', "%{$domain}%")->first();
                        }
                    }

                    if ($existing) {
                        $results['existing'][] = [
                            'name' => $companyName,
                            'source' => $name,
                            'existing_id' => $existing->id,
                        ];

                        continue;
                    }

                    if ($dryRun) {
                        $results['created'][] = array_merge($candidate, [
                            'source' => $name,
                            'dry_run' => true,
                        ]);

                        continue;
                    }

                    // Create the company
                    $company = Company::create([
                        'name' => $companyName,
                        'slug' => $this->generateUniqueSlug($companyName),
                        'description' => $candidate['description'] ?? null,
                        'website' => $candidate['website'] ?? null,
                        'is_indie' => $candidate['is_indie'] ?? false,
                        'solo_builder' => $candidate['solo_builder'] ?? false,
                        'submission_url' => $candidate['source_url'] ?? null,
                    ]);

                    // If we have funding info, create a funding round
                    if (isset($candidate['funding_amount']) || isset($candidate['funding_round'])) {
                        $company->fundingRounds()->create([
                            'round_type' => $candidate['funding_round'] ?? 'unknown',
                            'amount' => $candidate['funding_amount'] ?? null,
                            'currency' => 'USD',
                            'announced_date' => now()->toDateString(),
                            'source_url' => $candidate['source_url'] ?? null,
                        ]);
                    }

                    $results['created'][] = array_merge($candidate, [
                        'source' => $name,
                        'company_id' => $company->id,
                    ]);

                    Log::info("Discovery: created company '{$companyName}' from {$name}");
                }
            } catch (\Exception $e) {
                $results['errors'][] = "[{$name}] {$e->getMessage()}";
                Log::error("Discovery [{$name}] error: {$e->getMessage()}");
            }
        }

        return $results;
    }

    private function extractDomain(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return null;
        }

        // Strip www.
        return preg_replace('/^www\./', '', strtolower($host));
    }

    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (Company::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
