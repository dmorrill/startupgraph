<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BatchTagCompanies extends Command
{
    protected $signature = 'companies:batch-tag
                            {--dry-run : Show what would be tagged without making changes}
                            {--overwrite : Replace existing tags instead of merging}';

    protected $description = 'Automatically tag companies based on their descriptions and categories';

    /**
     * Keyword-to-tag mapping for auto-detection.
     */
    private array $keywordMap = [
        'saas' => ['saas', 'software as a service', 'cloud platform', 'subscription software'],
        'marketplace' => ['marketplace', 'two-sided', 'platform connecting', 'matching platform'],
        'fintech' => ['fintech', 'financial', 'banking', 'payments', 'lending', 'insurance', 'neobank'],
        'healthcare' => ['health', 'medical', 'clinical', 'patient', 'pharma', 'biotech', 'telemedicine'],
        'ai-ml' => ['artificial intelligence', 'machine learning', 'deep learning', 'neural', 'ai-powered', 'ai platform', 'llm', 'generative ai'],
        'developer-tools' => ['developer', 'api', 'sdk', 'devops', 'ci/cd', 'infrastructure', 'developer tools'],
        'consumer' => ['consumer', 'social media', 'dating', 'food delivery', 'e-commerce', 'retail'],
        'enterprise' => ['enterprise', 'b2b', 'workforce', 'erp', 'crm', 'business software'],
        'climate-energy' => ['climate', 'solar', 'energy', 'renewable', 'carbon', 'sustainability', 'cleantech'],
        'robotics' => ['robot', 'autonomous', 'drone', 'automation', 'warehouse automation'],
        'security' => ['security', 'cybersecurity', 'encryption', 'identity', 'authentication', 'zero trust'],
        'crypto-web3' => ['crypto', 'blockchain', 'web3', 'defi', 'nft', 'decentralized'],
        'edtech' => ['education', 'edtech', 'learning', 'tutoring', 'online course'],
        'b2b' => ['b2b', 'enterprise', 'business-to-business'],
        'b2c' => ['b2c', 'consumer', 'direct-to-consumer'],
        'open-source' => ['open source', 'open-source', 'oss'],
        'api-first' => ['api-first', 'api platform', 'api infrastructure'],
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $overwrite = $this->option('overwrite');

        // Ensure tags exist
        $this->call('db:seed', ['--class' => 'Database\\Seeders\\TagSeeder']);

        $tags = Tag::all()->keyBy('slug');
        $companies = Company::with('tags')->get();

        $tagged = 0;
        $skipped = 0;

        foreach ($companies as $company) {
            $matchedSlugs = $this->detectTags($company);

            if (empty($matchedSlugs)) {
                $skipped++;
                continue;
            }

            $tagIds = $tags->whereIn('slug', $matchedSlugs)->pluck('id')->toArray();

            if (empty($tagIds)) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $tagNames = $tags->whereIn('slug', $matchedSlugs)->pluck('name')->join(', ');
                $this->line("  <info>{$company->name}</info> → {$tagNames}");
                $tagged++;
                continue;
            }

            if ($overwrite) {
                $company->tags()->sync($tagIds);
            } else {
                $company->tags()->syncWithoutDetaching($tagIds);
            }

            $tagged++;
        }

        $action = $dryRun ? 'Would tag' : 'Tagged';
        $this->info("{$action} {$tagged} companies, skipped {$skipped}");

        return self::SUCCESS;
    }

    private function detectTags(Company $company): array
    {
        $text = strtolower(implode(' ', array_filter([
            $company->description,
            $company->name,
            $company->category,
            is_array($company->product_highlights) ? implode(' ', $company->product_highlights) : '',
        ])));

        $matched = [];

        foreach ($this->keywordMap as $tagSlug => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($text, $keyword)) {
                    $matched[] = $tagSlug;
                    break;
                }
            }
        }

        // Map existing category field to tags
        if ($company->category) {
            $categoryTagMap = [
                'ai_ml' => 'ai-ml',
                'fintech' => 'fintech',
                'enterprise' => 'enterprise',
                'healthcare' => 'healthcare',
                'robotics' => 'robotics',
                'space' => 'space',
                'climate' => 'climate-energy',
                'consumer' => 'consumer',
                'developer_tools' => 'developer-tools',
                'defense' => 'defense',
            ];

            if (isset($categoryTagMap[$company->category])) {
                $matched[] = $categoryTagMap[$company->category];
            }
        }

        return array_unique($matched);
    }
}
