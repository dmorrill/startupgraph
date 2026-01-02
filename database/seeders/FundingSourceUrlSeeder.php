<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\FundingRound;
use Illuminate\Database\Seeder;

class FundingSourceUrlSeeder extends Seeder
{
    public function run(): void
    {
        $sourceUrls = [
            // OpenAI
            'openai' => [
                ['round_type' => 'seed', 'source_url' => 'https://www.crunchbase.com/funding_round/openai-grant--7f013dea'],
                ['round_type' => 'series_d', 'source_url' => 'https://news.microsoft.com/2019/07/22/openai-forms-exclusive-computing-partnership-with-microsoft-to-build-new-azure-ai-supercomputing-technologies/'],
                ['round_type' => 'strategic', 'amount' => 10000000000, 'source_url' => 'https://blogs.microsoft.com/blog/2023/01/23/microsoftandopenaiextendpartnership/'],
                ['round_type' => 'series_f', 'source_url' => 'https://techcrunch.com/2025/03/31/openai-raises-40b-at-300b-post-money-valuation/'],
            ],

            // SpaceX
            'spacex' => [
                ['round_type' => 'series_a', 'source_url' => 'https://www.crunchbase.com/funding_round/space-exploration-technologies-series-a--5e0c8c9f'],
                ['round_type' => 'series_d', 'source_url' => 'https://www.crunchbase.com/funding_round/space-exploration-technologies-series-d--2d5b0e9f'],
                ['round_type' => 'series_e', 'source_url' => 'https://www.crunchbase.com/funding_round/space-exploration-technologies-series-e--f0e65e78'],
                ['round_type' => 'series_h', 'source_url' => 'https://www.crunchbase.com/funding_round/space-exploration-technologies-series-h--9a96c2d6'],
                ['round_type' => 'series_i', 'source_url' => 'https://techcrunch.com/2020/08/18/spacex-raises-1-9-billion-in-largest-funding-found-to-date/'],
                ['round_type' => 'series_j', 'amount' => 1160000000, 'source_url' => 'https://techcrunch.com/2021/02/23/spacexs-new-850-million-raise-confirmed-in-sec-filing/'],
                ['round_type' => 'series_j_extension', 'source_url' => 'https://www.cnbc.com/2022/05/26/spacex-starlink-and-starship-drive-1point68-billion-funding-round.html'],
                ['round_type' => 'series_k', 'source_url' => 'https://www.cnbc.com/2023/01/20/spacex-closes-750-million-round-at-137-billion-valuation.html'],
                ['round_type' => 'secondary', 'source_url' => 'https://www.bloomberg.com/news/articles/2024-12-11/spacex-to-buy-back-shares-at-350-billion-valuation-in-tender'],
            ],

            // Anthropic
            'anthropic' => [
                ['round_type' => 'series_a', 'source_url' => 'https://www.crunchbase.com/funding_round/anthropic-series-a--c16fad00'],
                ['round_type' => 'series_b', 'source_url' => 'https://techcrunch.com/2023/04/06/anthropics-5b-4-year-plan-to-take-on-openai/'],
                ['round_type' => 'series_c', 'source_url' => 'https://www.anthropic.com/news/series-c'],
                ['round_type' => 'strategic', 'amount' => 4000000000, 'source_url' => 'https://www.aboutamazon.com/news/company-news/amazon-anthropic-ai-investment'],
                ['round_type' => 'strategic', 'amount' => 2000000000, 'source_url' => 'https://cloud.google.com/blog/topics/partners/google-cloud-partners-with-anthropic-ai-startup'],
                ['round_type' => 'series_d', 'source_url' => 'https://www.menlovc.com/news/anthropic-series-d'],
                ['round_type' => 'strategic', 'amount' => 4000000000, 'announced_date' => '2024-11', 'source_url' => 'https://www.aboutamazon.com/news/company-news/amazon-completes-investment-in-anthropic'],
                ['round_type' => 'series_f', 'source_url' => 'https://techcrunch.com/2025/03/03/anthropic-raises-3-5b-to-fuel-its-ai-ambitions/'],
            ],

            // Stripe
            'stripe' => [
                ['round_type' => 'seed', 'source_url' => 'https://www.crunchbase.com/funding_round/stripe-seed--a3d5a0e0'],
                ['round_type' => 'series_a', 'source_url' => 'https://www.sequoiacap.com/companies/stripe/'],
                ['round_type' => 'series_b', 'source_url' => 'https://www.crunchbase.com/funding_round/stripe-series-b--a20fe3c0'],
                ['round_type' => 'series_c', 'source_url' => 'https://techcrunch.com/2014/12/02/stripe-reportedly-raising-round-that-would-value-it-at-3-5-billion/'],
                ['round_type' => 'series_d', 'source_url' => 'https://techcrunch.com/2016/11/25/payments-company-stripe-is-raising-new-funding-at-9b-valuation/'],
                ['round_type' => 'series_e', 'source_url' => 'https://stripe.com/newsroom/news/stripe-series-e'],
                ['round_type' => 'series_f', 'source_url' => 'https://stripe.com/newsroom/news/stripe-series-f'],
                ['round_type' => 'series_g', 'source_url' => 'https://techcrunch.com/2020/04/16/stripe-raises-600m-at-36b-valuation-in-series-g-extension/'],
            ],

            // Databricks
            'databricks' => [
                ['round_type' => 'series_a', 'source_url' => 'https://www.crunchbase.com/funding_round/databricks-series-a--36d9d2b6'],
                ['round_type' => 'series_b', 'source_url' => 'https://www.crunchbase.com/funding_round/databricks-series-b--1e7d4a0f'],
                ['round_type' => 'series_c', 'source_url' => 'https://www.crunchbase.com/funding_round/databricks-series-c--ad6dce1f'],
                ['round_type' => 'series_d', 'source_url' => 'https://www.crunchbase.com/funding_round/databricks-series-d--99e93c50'],
                ['round_type' => 'series_e', 'source_url' => 'https://www.databricks.com/company/newsroom/press-releases/databricks-raises-250m-series-e'],
                ['round_type' => 'series_f', 'source_url' => 'https://www.databricks.com/company/newsroom/press-releases/databricks-raises-400m-series-f'],
                ['round_type' => 'series_g', 'source_url' => 'https://techcrunch.com/2021/02/01/databricks-raises-1b-at-28b-valuation-as-it-reaches-425m-arr/'],
                ['round_type' => 'series_h', 'source_url' => 'https://www.databricks.com/company/newsroom/press-releases/databricks-raises-1-6-billion-series-h-38-billion-valuation'],
                ['round_type' => 'series_i', 'source_url' => 'https://news.crunchbase.com/ai-robotics/databricks-funding-valuation-nvda/'],
            ],

            // Canva
            'canva' => [
                ['round_type' => 'seed', 'source_url' => 'https://www.crunchbase.com/funding_round/canva-seed--b8f9e9b0'],
                ['round_type' => 'series_a', 'source_url' => 'https://techcrunch.com/2015/10/06/design-platform-canva-scores-15-million-series-a-valued-at-165-million/'],
                ['round_type' => 'series_b', 'source_url' => 'https://techcrunch.com/2018/01/08/new-sequoia-china-investment-values-australian-design-company-canva-at-1-billion/'],
                ['round_type' => 'series_c', 'source_url' => 'https://techcrunch.com/2019/05/20/graphic-design-platform-canva-valued-at-2-5b-with-new-funds/'],
                ['round_type' => 'series_d', 'source_url' => 'https://techcrunch.com/2020/06/22/canva-raises-60-million-on-a-6-billion-valuation/'],
                ['round_type' => 'series_e', 'source_url' => 'https://techcrunch.com/2021/09/14/canva-raises-200-million-at-a-40-billion-valuation/'],
            ],

            // Rippling
            'rippling' => [
                ['round_type' => 'series_a', 'source_url' => 'https://www.crunchbase.com/funding_round/rippling-series-a--6a3f2400'],
                ['round_type' => 'series_b', 'source_url' => 'https://techcrunch.com/2020/08/04/rippling-nabs-145m-at-a-1-35b-valuation-to-build-out-its-all-in-one-platform-for-employee-data/'],
                ['round_type' => 'series_c', 'source_url' => 'https://techcrunch.com/2021/10/21/parker-conrads-rippling-is-now-valued-at-6-5-billion-more-than-zenefits-at-its-peak/'],
                ['round_type' => 'series_d', 'source_url' => 'https://www.rippling.com/blog/rippling-raises-series-d'],
                ['round_type' => 'series_e', 'source_url' => 'https://techcrunch.com/2023/03/17/a-500-million-term-sheet-in-12-hours-how-rippling-struck-a-deal-as-svb-was-melting-down/'],
                ['round_type' => 'series_f', 'source_url' => 'https://techcrunch.com/2024/04/22/ripplings-parker-conrad-on-the-companys-brand-new-round-its-brand-new-sf-lease-and-also-its-brand-new-critic/'],
            ],

            // Perplexity
            'perplexity' => [
                ['round_type' => 'series_a', 'source_url' => 'https://techcrunch.com/2023/04/04/ai-powered-search-engine-perplexity-ai-lands-26m-launches-ios-app/'],
                ['round_type' => 'series_b', 'source_url' => 'https://techcrunch.com/2024/01/04/ai-powered-search-engine-perplexity-ai-now-valued-at-520m-raises-70m/'],
                ['round_type' => 'series_b_extension', 'source_url' => 'https://www.perplexity.ai/hub/blog/perplexity-raises-series-b-funding-round'],
                ['round_type' => 'series_c', 'source_url' => 'https://techcrunch.com/2024/04/23/perplexity-is-raising-250m-at-2-point-5-3b-valuation-ai-search-sources-say/'],
                ['round_type' => 'series_d', 'source_url' => 'https://techcrunch.com/2024/12/19/perplexity-has-reportedly-closed-a-500m-funding-round/'],
                ['round_type' => 'series_e', 'source_url' => 'https://techcrunch.com/2025/09/10/perplexity-reportedly-raised-200m-at-20b-valuation/'],
            ],

            // Figma
            'figma' => [
                ['round_type' => 'seed', 'source_url' => 'https://www.crunchbase.com/funding_round/figma-seed--c5cc7e50'],
                ['round_type' => 'series_a', 'source_url' => 'https://techcrunch.com/2015/12/03/figma-vs-goliath/'],
                ['round_type' => 'series_b', 'source_url' => 'https://www.crunchbase.com/funding_round/figma-series-b--c0de4a0f'],
                ['round_type' => 'series_c', 'source_url' => 'https://techcrunch.com/2019/02/14/figma-gets-40-million-series-c-to-put-design-tools-in-the-cloud/'],
                ['round_type' => 'series_d', 'source_url' => 'https://techcrunch.com/2020/04/30/figma-raises-50-million-series-d-led-by-andreessen-horowitz/'],
                ['round_type' => 'series_e', 'source_url' => 'https://www.figma.com/blog/figmas-series-e/'],
                ['round_type' => 'series_f', 'source_url' => 'https://www.figma.com/blog/figma-series-f/'],
            ],

            // Notion
            'notion' => [
                ['round_type' => 'seed', 'source_url' => 'https://www.crunchbase.com/funding_round/notion-so-seed--a9b72e30'],
                ['round_type' => 'series_a', 'source_url' => 'https://www.notion.so/blog/notion-raises-10m-at-800m-valuation'],
                ['round_type' => 'series_b', 'source_url' => 'https://techcrunch.com/2020/04/01/notion-hits-2-billion-valuation-in-new-raise/'],
                ['round_type' => 'series_c', 'source_url' => 'https://www.notion.so/blog/notion-raises-50-million-at-2-billion-valuation'],
                ['round_type' => 'series_d', 'source_url' => 'https://www.notion.so/blog/series-d'],
            ],
        ];

        $updated = 0;
        $notFound = 0;

        foreach ($sourceUrls as $companySlug => $rounds) {
            $company = Company::where('slug', $companySlug)->first();
            if (!$company) {
                $this->command->warn("Company not found: {$companySlug}");
                continue;
            }

            foreach ($rounds as $roundData) {
                $query = FundingRound::where('company_id', $company->id)
                    ->where('round_type', $roundData['round_type']);

                // If amount is specified, use it to disambiguate rounds of the same type
                if (isset($roundData['amount'])) {
                    $query->where('amount', $roundData['amount']);
                }

                // If announced_date prefix is specified, use it
                if (isset($roundData['announced_date'])) {
                    $query->where('announced_date', 'like', $roundData['announced_date'] . '%');
                }

                $round = $query->first();

                if ($round) {
                    $round->update(['source_url' => $roundData['source_url']]);
                    $updated++;
                } else {
                    $this->command->warn("Round not found: {$companySlug} - {$roundData['round_type']}");
                    $notFound++;
                }
            }
        }

        $this->command->info("Updated {$updated} funding rounds with source URLs.");
        if ($notFound > 0) {
            $this->command->warn("{$notFound} rounds could not be matched.");
        }
    }
}
