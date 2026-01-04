<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Investor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Additional funding data for companies that were missing funding rounds.
 * Data researched from TechCrunch, Crunchbase, and other public sources (Jan 2026).
 */
class AdditionalFundingDataSeeder extends Seeder
{
    public function run(): void
    {
        $investors = $this->createInvestors();

        // AI/ML Companies
        $this->seedXaiFunding($investors);
        $this->seedScaleAiFunding($investors);
        $this->seedCohereFunding($investors);
        $this->seedMistralAiFunding($investors);
        $this->seedHuggingFaceFunding($investors);
        $this->seedRunwayFunding($investors);
        $this->seedHarveyFunding($investors);
        $this->seedFigureAiFunding($investors);

        // Fintech Companies
        $this->seedPlaidFunding($investors);
        $this->seedBrexFunding($investors);
        $this->seedRampFunding($investors);
        $this->seedChimeFunding($investors);

        // Enterprise/Security
        $this->seedWizFunding($investors);
        $this->seedAndurilFunding($investors);

        // Developer Tools
        $this->seedVercelFunding($investors);
        $this->seedAirtableFunding($investors);

        // Space
        $this->seedRelativitySpaceFunding($investors);
    }

    private function createInvestors(): array
    {
        $investorData = [
            // Major VCs not yet in database
            ['name' => 'Accel', 'type' => 'vc', 'website' => 'https://accel.com'],
            ['name' => 'Altimeter Capital', 'type' => 'vc', 'website' => 'https://altimeter.com'],
            ['name' => 'CRV', 'type' => 'vc', 'website' => 'https://crv.com'],
            ['name' => 'Bedrock Capital', 'type' => 'vc', 'website' => 'https://bedrock.co'],
            ['name' => 'Tiger Global', 'type' => 'vc', 'website' => 'https://tigerglobal.com'],
            ['name' => 'General Atlantic', 'type' => 'vc', 'website' => 'https://generalatlantic.com'],
            ['name' => 'Wellington Management', 'type' => 'growth', 'website' => 'https://wellington.com'],
            ['name' => 'XN', 'type' => 'vc', 'website' => 'https://xn.com'],
            ['name' => 'D1 Capital Partners', 'type' => 'vc', 'website' => 'https://d1cap.com'],
            ['name' => 'Vista Equity Partners', 'type' => 'pe', 'website' => 'https://vistaequitypartners.com'],
            ['name' => 'Silver Lake', 'type' => 'pe', 'website' => 'https://silverlake.com'],
            ['name' => 'Franklin Templeton', 'type' => 'growth', 'website' => 'https://franklintempleton.com'],
            ['name' => 'Parkway Venture Capital', 'type' => 'vc', 'website' => 'https://parkwayvc.com'],
            ['name' => 'ARK Invest', 'type' => 'growth', 'website' => 'https://ark-invest.com'],
            ['name' => 'OpenAI Startup Fund', 'type' => 'corporate', 'website' => 'https://openai.com'],
            ['name' => 'Inovia Capital', 'type' => 'vc', 'website' => 'https://inovia.vc'],
            ['name' => 'Radical Ventures', 'type' => 'vc', 'website' => 'https://radicalventures.com'],
            ['name' => 'ASML', 'type' => 'corporate', 'website' => 'https://asml.com'],
            ['name' => 'AMD Ventures', 'type' => 'corporate', 'website' => 'https://amd.com'],
            ['name' => 'Valor Equity Partners', 'type' => 'vc', 'website' => 'https://valorep.com'],
            ['name' => 'Oracle', 'type' => 'corporate', 'website' => 'https://oracle.com'],
            ['name' => 'Lux Capital', 'type' => 'vc', 'website' => 'https://luxcapital.com'],
            ['name' => 'Sound Ventures', 'type' => 'vc', 'website' => 'https://sound.ventures'],
            ['name' => 'Amazon', 'type' => 'corporate', 'website' => 'https://amazon.com'],
            ['name' => 'Intel Capital', 'type' => 'corporate', 'website' => 'https://intelcapital.com'],
            ['name' => 'Qualcomm Ventures', 'type' => 'corporate', 'website' => 'https://qualcommventures.com'],
            ['name' => 'IBM Ventures', 'type' => 'corporate', 'website' => 'https://ibm.com'],
            ['name' => 'Forerunner Ventures', 'type' => 'vc', 'website' => 'https://forerunnerventures.com'],
            ['name' => 'Menlo Ventures', 'type' => 'vc', 'website' => 'https://menlovc.com'],
            ['name' => 'Access Industries', 'type' => 'pe', 'website' => 'https://accessindustries.com'],
            ['name' => 'K5 Global', 'type' => 'vc', 'website' => 'https://k5global.com'],
            ['name' => 'Tribe Capital', 'type' => 'vc', 'website' => 'https://tribecap.co'],
            ['name' => 'Social Capital', 'type' => 'vc', 'website' => 'https://socialcapital.com'],
            ['name' => 'Vy Capital', 'type' => 'vc', 'website' => 'https://vycapital.com'],
            ['name' => 'Kingdom Holding', 'type' => 'sovereign', 'website' => 'https://kingdom.com.sa'],
            ['name' => 'QIA', 'type' => 'sovereign', 'website' => 'https://qia.qa'],
            ['name' => 'Cyberstarts', 'type' => 'vc', 'website' => 'https://cyberstarts.com'],
            ['name' => 'LG Technology Ventures', 'type' => 'corporate', 'website' => 'https://lg.com'],
            ['name' => 'T-Mobile Ventures', 'type' => 'corporate', 'website' => 'https://t-mobile.com'],
            ['name' => 'Brookfield Asset Management', 'type' => 'pe', 'website' => 'https://brookfield.com'],
            ['name' => 'Macquarie Capital', 'type' => 'growth', 'website' => 'https://macquarie.com'],
            ['name' => 'Ribbit Capital', 'type' => 'vc', 'website' => 'https://ribbitcap.com'],
            ['name' => 'J.P. Morgan', 'type' => 'growth', 'website' => 'https://jpmorgan.com'],
            ['name' => 'American Express', 'type' => 'corporate', 'website' => 'https://americanexpress.com'],
            ['name' => 'WCM Investment Management', 'type' => 'growth', 'website' => 'https://wcminvest.com'],
            ['name' => 'MVP Ventures', 'type' => 'vc', 'website' => 'https://mvpvc.com'],
            ['name' => 'US Innovative Technology Fund', 'type' => 'vc', 'website' => null],
            ['name' => 'Counterpoint Global', 'type' => 'growth', 'website' => 'https://morganstanley.com'],
            ['name' => 'Sands Capital', 'type' => 'growth', 'website' => 'https://sandscapital.com'],
            ['name' => 'Meta', 'type' => 'corporate', 'website' => 'https://meta.com'],
            ['name' => 'Cisco Ventures', 'type' => 'corporate', 'website' => 'https://cisco.com'],
            ['name' => 'ServiceNow Ventures', 'type' => 'corporate', 'website' => 'https://servicenow.com'],
            ['name' => 'DFJ Growth', 'type' => 'vc', 'website' => 'https://dfj.com'],
            ['name' => 'Sutter Hill Ventures', 'type' => 'vc', 'website' => 'https://shv.com'],
            ['name' => 'Alpha Wave Global', 'type' => 'vc', 'website' => 'https://alphawaveglobal.com'],
            ['name' => 'Robinhood Ventures', 'type' => 'corporate', 'website' => 'https://robinhood.com'],
            ['name' => 'BoxGroup', 'type' => 'vc', 'website' => 'https://boxgroup.com'],
            ['name' => 'Neo', 'type' => 'vc', 'website' => 'https://neo.com'],
            ['name' => 'Avenir Growth Capital', 'type' => 'vc', 'website' => 'https://avenirgrowth.com'],
            ['name' => 'Declaration Partners', 'type' => 'vc', 'website' => 'https://declarationpartners.com'],
            ['name' => 'GGV Capital', 'type' => 'vc', 'website' => 'https://ggvc.com'],
            ['name' => 'GV', 'type' => 'corporate', 'website' => 'https://gv.com'],
            ['name' => '8VC', 'type' => 'vc', 'website' => 'https://8vc.com'],
            ['name' => 'Geodesic Capital', 'type' => 'vc', 'website' => 'https://geodesiccap.com'],
            ['name' => 'Caffeinated Capital', 'type' => 'vc', 'website' => 'https://caffeinatedcapital.com'],
            ['name' => 'WndrCo', 'type' => 'vc', 'website' => 'https://wndrco.com'],
            ['name' => 'MSD Capital', 'type' => 'family_office', 'website' => 'https://msdcapital.com'],
            ['name' => 'Morgan Stanley', 'type' => 'growth', 'website' => 'https://morganstanley.com'],
            ['name' => 'Soroban Capital', 'type' => 'hedge_fund', 'website' => 'https://sorobancap.com'],
            ['name' => 'Centricus', 'type' => 'pe', 'website' => 'https://centricus.com'],

            // Additional VCs referenced in funding rounds
            ['name' => 'Spark Capital', 'type' => 'vc', 'website' => 'https://sparkcapital.com'],
            ['name' => 'New Enterprise Associates', 'type' => 'vc', 'website' => 'https://nea.com'],
            ['name' => 'Playground Global', 'type' => 'vc', 'website' => 'https://playground.global'],
            ['name' => 'Bond Capital', 'type' => 'vc', 'website' => 'https://bondcap.com'],
            ['name' => 'Betaworks', 'type' => 'vc', 'website' => 'https://betaworks.com'],
            ['name' => 'SV Angel', 'type' => 'vc', 'website' => 'https://svangel.com'],

            // Notable angels/individuals
            ['name' => 'Eric Schmidt', 'type' => 'angel', 'website' => null],
            ['name' => 'Xavier Niel', 'type' => 'angel', 'website' => null],
            ['name' => 'Mark Cuban', 'type' => 'angel', 'website' => null],
            ['name' => 'Jared Leto', 'type' => 'angel', 'website' => null],
            ['name' => 'Elad Gil', 'type' => 'angel', 'website' => 'https://eladgil.com'],
            ['name' => 'Jeff Bezos', 'type' => 'angel', 'website' => null],
        ];

        // First get existing investors
        $investors = [];
        $existingInvestors = Investor::all();
        foreach ($existingInvestors as $investor) {
            $investors[$investor->name] = $investor;
        }

        // Create new ones
        foreach ($investorData as $data) {
            $data['slug'] = Str::slug($data['name']);
            $investor = Investor::firstOrCreate(['slug' => $data['slug']], $data);
            $investors[$data['name']] = $investor;
        }

        return $investors;
    }

    private function seedXaiFunding(array $investors): void
    {
        $company = Company::where('slug', 'xai')->first();
        if (!$company) {
            $this->command->warn('Company not found: xai');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 134700000,
                'announced_date' => '2023-12-01',
                'source_url' => 'https://www.sec.gov/cgi-bin/browse-edgar?action=getcompany&company=x.ai',
                'investors' => [],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 6000000000,
                'announced_date' => '2024-05-01',
                'pre_money_valuation' => 18000000000,
                'source_url' => 'https://news.crunchbase.com/ai/xai-raises-series-b-unicorn-musk/',
                'investors' => ['Valor Equity Partners', 'Vy Capital', 'Andreessen Horowitz', 'Sequoia Capital', 'Fidelity Investments', 'Kingdom Holding'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 6000000000,
                'announced_date' => '2024-12-23',
                'pre_money_valuation' => 44000000000,
                'source_url' => 'https://www.bloomberg.com/news/articles/2024-12-05/musk-s-xai-wraps-up-6-billion-in-funding-in-latest-round',
                'investors' => ['Fidelity Investments', 'BlackRock', 'Sequoia Capital'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 10000000000,
                'announced_date' => '2025-09-01',
                'pre_money_valuation' => 190000000000,
                'source_url' => 'https://news.crunchbase.com/ai/generative-ai-elon-musk-xai-debt-equity/',
                'investors' => [],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedScaleAiFunding(array $investors): void
    {
        $company = Company::where('slug', 'scale-ai')->first();
        if (!$company) {
            $this->command->warn('Company not found: scale-ai');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 4500000,
                'announced_date' => '2016-08-01',
                'investors' => ['Y Combinator'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 5000000,
                'announced_date' => '2017-08-01',
                'investors' => ['Accel', 'Index Ventures'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 18000000,
                'announced_date' => '2018-08-01',
                'investors' => ['Accel', 'Index Ventures'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 100000000,
                'announced_date' => '2019-08-01',
                'pre_money_valuation' => 950000000,
                'investors' => ['Founders Fund', 'Accel', 'Index Ventures'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 155000000,
                'announced_date' => '2021-02-01',
                'pre_money_valuation' => 3550000000,
                'investors' => ['Tiger Global', 'Index Ventures', 'Accel'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 325000000,
                'announced_date' => '2021-04-01',
                'pre_money_valuation' => 6875000000,
                'investors' => ['Tiger Global', 'Greenoaks', 'Index Ventures', 'Dragoneer Investment Group'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 1000000000,
                'announced_date' => '2024-05-21',
                'pre_money_valuation' => 12800000000,
                'source_url' => 'https://techcrunch.com/2024/05/21/data-labeling-startup-scale-ai-raises-1b-as-valuation-doubles-to-13-8b/',
                'investors' => ['Accel', 'Amazon', 'Meta', 'Nvidia', 'Cisco Ventures', 'AMD Ventures', 'Intel Capital', 'ServiceNow Ventures', 'DFJ Growth', 'Elad Gil'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedCohereFunding(array $investors): void
    {
        $company = Company::where('slug', 'cohere')->first();
        if (!$company) {
            $this->command->warn('Company not found: cohere');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'series_a',
                'amount' => 40000000,
                'announced_date' => '2021-09-01',
                'investors' => ['Index Ventures'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 125000000,
                'announced_date' => '2022-02-01',
                'investors' => ['Tiger Global', 'Index Ventures'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 270000000,
                'announced_date' => '2023-06-01',
                'pre_money_valuation' => 1930000000,
                'source_url' => 'https://techcrunch.com/2023/06/08/cohere-raises-270m-to-expand-its-enterprise-focused-generative-ai-platform/',
                'investors' => ['Inovia Capital', 'Nvidia', 'Oracle', 'Salesforce Ventures', 'Index Ventures'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 500000000,
                'announced_date' => '2024-07-22',
                'pre_money_valuation' => 5000000000,
                'source_url' => 'https://techcrunch.com/2024/07/22/cohere-raises-500m-to-beat-back-generative-ai-rivals/',
                'investors' => ['AMD Ventures', 'Cisco Ventures', 'Nvidia', 'Oracle', 'Salesforce Ventures', 'Fidelity Investments'],
            ],
            [
                'round_type' => 'series_d_extension',
                'amount' => 600000000,
                'announced_date' => '2025-08-01',
                'pre_money_valuation' => 6200000000,
                'source_url' => 'https://cohere.com/blog/august-2025-funding-round',
                'investors' => ['Inovia Capital', 'Radical Ventures', 'AMD Ventures', 'Nvidia', 'Salesforce Ventures'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedMistralAiFunding(array $investors): void
    {
        $company = Company::where('slug', 'mistral-ai')->first();
        if (!$company) {
            $this->command->warn('Company not found: mistral-ai');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 113000000,
                'announced_date' => '2023-06-01',
                'pre_money_valuation' => 147000000,
                'source_url' => 'https://techcrunch.com/2023/06/13/mistral-ai-raises-113m-seed-round-to-build-ai-models/',
                'investors' => ['Lightspeed Venture Partners', 'Index Ventures', 'Eric Schmidt', 'Xavier Niel'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 415000000,
                'announced_date' => '2023-12-11',
                'pre_money_valuation' => 1585000000,
                'source_url' => 'https://techcrunch.com/2023/12/11/mistral-ai-a-paris-based-openai-rival-closed-its-415-million-funding-round/',
                'investors' => ['Andreessen Horowitz', 'Lightspeed Venture Partners', 'Salesforce Ventures', 'General Catalyst', 'Elad Gil'],
            ],
            [
                'round_type' => 'strategic',
                'amount' => 16300000,
                'announced_date' => '2024-02-01',
                'source_url' => 'https://mistral.ai/news/microsoft-partnership/',
                'investors' => ['Microsoft'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 645000000,
                'announced_date' => '2024-06-01',
                'pre_money_valuation' => 5555000000,
                'source_url' => 'https://techcrunch.com/2024/06/11/mistral-ai-raises-640-million-at-6b-valuation/',
                'investors' => ['General Catalyst', 'Lightspeed Venture Partners', 'Andreessen Horowitz', 'Nvidia', 'Salesforce Ventures', 'Databricks', 'IBM Ventures'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 1870000000,
                'announced_date' => '2025-09-08',
                'pre_money_valuation' => 11000000000,
                'source_url' => 'https://mistral.ai/news/mistral-ai-raises-1-7-b-to-accelerate-technological-progress-with-ai',
                'investors' => ['ASML', 'Andreessen Horowitz', 'General Catalyst', 'Index Ventures', 'Lightspeed Venture Partners', 'Nvidia'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedHuggingFaceFunding(array $investors): void
    {
        $company = Company::where('slug', 'hugging-face')->first();
        if (!$company) {
            $this->command->warn('Company not found: hugging-face');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 4000000,
                'announced_date' => '2017-05-01',
                'investors' => ['Betaworks', 'SV Angel'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 15000000,
                'announced_date' => '2019-12-01',
                'investors' => ['Lux Capital', 'Betaworks'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 40000000,
                'announced_date' => '2021-03-01',
                'investors' => ['Lux Capital', 'Coatue Management'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 100000000,
                'announced_date' => '2022-05-01',
                'pre_money_valuation' => 1900000000,
                'investors' => ['Lux Capital', 'Sequoia Capital', 'Coatue Management'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 235000000,
                'announced_date' => '2023-08-24',
                'pre_money_valuation' => 4265000000,
                'source_url' => 'https://techcrunch.com/2023/08/24/hugging-face-raises-235m-from-investors-including-salesforce-and-nvidia/',
                'investors' => ['Salesforce Ventures', 'Google', 'Amazon', 'Nvidia', 'Intel Capital', 'AMD Ventures', 'Qualcomm Ventures', 'IBM Ventures', 'Sound Ventures', 'Lux Capital'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedRunwayFunding(array $investors): void
    {
        $company = Company::where('slug', 'runway')->first();
        if (!$company) {
            $this->command->warn('Company not found: runway');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 8500000,
                'announced_date' => '2019-11-01',
                'investors' => [],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 35000000,
                'announced_date' => '2021-12-01',
                'investors' => ['Coatue Management', 'Lux Capital'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 50000000,
                'announced_date' => '2022-05-01',
                'investors' => ['Coatue Management', 'Lux Capital'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 141000000,
                'announced_date' => '2023-06-01',
                'pre_money_valuation' => 1359000000,
                'source_url' => 'https://techcrunch.com/2023/06/29/runway-raises-141m-at-1-5b-valuation-for-ai-video-generation/',
                'investors' => ['Google', 'Nvidia', 'Salesforce Ventures'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 308000000,
                'announced_date' => '2025-04-03',
                'pre_money_valuation' => 2692000000,
                'source_url' => 'https://runwayml.com/news/runway-series-d-funding',
                'investors' => ['General Atlantic', 'Fidelity Investments', 'Baillie Gifford', 'SoftBank Vision Fund', 'Nvidia'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedHarveyFunding(array $investors): void
    {
        $company = Company::where('slug', 'harvey')->first();
        if (!$company) {
            $this->command->warn('Company not found: harvey');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 5000000,
                'announced_date' => '2022-11-01',
                'investors' => ['OpenAI Startup Fund'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 21000000,
                'announced_date' => '2023-04-01',
                'investors' => ['Sequoia Capital'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 80000000,
                'announced_date' => '2023-12-01',
                'source_url' => 'https://techcrunch.com/2023/12/18/legal-ai-startup-harvey-raises-80m-series-b/',
                'investors' => ['Elad Gil', 'Kleiner Perkins'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 100000000,
                'announced_date' => '2024-07-01',
                'source_url' => 'https://techcrunch.com/2024/07/23/harvey-raises-100m-series-c-led-by-google-ventures/',
                'investors' => ['GV'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 300000000,
                'announced_date' => '2025-02-12',
                'pre_money_valuation' => 2700000000,
                'source_url' => 'https://www.harvey.ai/blog/harvey-raises-series-d',
                'investors' => ['Sequoia Capital'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 300000000,
                'announced_date' => '2025-05-01',
                'pre_money_valuation' => 4700000000,
                'source_url' => 'https://www.nerdlawyer.ai/nerd-lawyer/harveyai',
                'investors' => ['Kleiner Perkins', 'Coatue Management'],
            ],
            [
                'round_type' => 'series_e_extension',
                'amount' => 160000000,
                'announced_date' => '2025-12-04',
                'pre_money_valuation' => 7840000000,
                'source_url' => 'https://techcrunch.com/2025/12/04/legal-ai-startup-harvey-confirms-8b-valuation/',
                'investors' => ['Andreessen Horowitz'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedFigureAiFunding(array $investors): void
    {
        $company = Company::where('slug', 'figure-ai')->first();
        if (!$company) {
            $this->command->warn('Company not found: figure-ai');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'series_a',
                'amount' => 70000000,
                'announced_date' => '2023-05-01',
                'investors' => ['Parkway Venture Capital'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 675000000,
                'announced_date' => '2024-02-29',
                'pre_money_valuation' => 1925000000,
                'source_url' => 'https://www.therobotreport.com/figure-ai-raises-675m-to-commercialize-humanoids/',
                'investors' => ['Microsoft', 'OpenAI Startup Fund', 'Nvidia', 'Amazon', 'Jeff Bezos', 'Intel Capital', 'ARK Invest', 'Parkway Venture Capital'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 1000000000,
                'announced_date' => '2025-09-01',
                'pre_money_valuation' => 38000000000,
                'source_url' => 'https://www.figure.ai/news/series-c',
                'investors' => ['Parkway Venture Capital', 'Brookfield Asset Management', 'Nvidia', 'Macquarie Capital', 'Intel Capital', 'LG Technology Ventures', 'Salesforce Ventures', 'T-Mobile Ventures', 'Qualcomm Ventures'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedPlaidFunding(array $investors): void
    {
        $company = Company::where('slug', 'plaid')->first();
        if (!$company) {
            $this->command->warn('Company not found: plaid');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 2800000,
                'announced_date' => '2013-09-01',
                'investors' => ['Spark Capital'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 12500000,
                'announced_date' => '2014-10-01',
                'investors' => ['Spark Capital', 'New Enterprise Associates'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 44000000,
                'announced_date' => '2016-06-01',
                'investors' => ['Goldman Sachs', 'New Enterprise Associates', 'Spark Capital'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 250000000,
                'announced_date' => '2018-12-11',
                'pre_money_valuation' => 2400000000,
                'source_url' => 'https://techcrunch.com/2018/12/11/plaid-raises-250m-at-a-2-7b-valuation/',
                'investors' => ['Andreessen Horowitz', 'Kleiner Perkins', 'Index Ventures', 'New Enterprise Associates', 'Spark Capital', 'Coatue Management'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 425000000,
                'announced_date' => '2021-04-07',
                'pre_money_valuation' => 12975000000,
                'source_url' => 'https://techcrunch.com/2021/04/07/plaid-raises-425m-series-d-from-altimeter-as-it-charts-a-post-visa-future/',
                'investors' => ['Altimeter Capital', 'Silver Lake', 'Ribbit Capital', 'Andreessen Horowitz', 'Index Ventures', 'Kleiner Perkins', 'New Enterprise Associates', 'Spark Capital', 'J.P. Morgan', 'American Express'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 575000000,
                'announced_date' => '2025-04-03',
                'pre_money_valuation' => 5525000000,
                'source_url' => 'https://techcrunch.com/2025/04/03/fintech-plaid-raises-575m-at-6-1b-valuation-says-it-will-not-go-public-in-2025/',
                'investors' => ['Franklin Templeton', 'Fidelity Investments', 'BlackRock', 'New Enterprise Associates', 'Ribbit Capital'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedBrexFunding(array $investors): void
    {
        $company = Company::where('slug', 'brex')->first();
        if (!$company) {
            $this->command->warn('Company not found: brex');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 6500000,
                'announced_date' => '2017-04-01',
                'investors' => ['Y Combinator'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 7000000,
                'announced_date' => '2017-11-01',
                'investors' => ['Y Combinator', 'Ribbit Capital'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 57000000,
                'announced_date' => '2018-06-01',
                'investors' => ['Y Combinator', 'Ribbit Capital', 'Greenoaks'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 125000000,
                'announced_date' => '2018-10-01',
                'pre_money_valuation' => 975000000,
                'source_url' => 'https://techcrunch.com/2018/10/04/brex-raises-125-million-at-over-1b-valuation/',
                'investors' => ['DST Global', 'IVP', 'Greenoaks', 'Y Combinator', 'Ribbit Capital'],
            ],
            [
                'round_type' => 'series_c_extension',
                'amount' => 100000000,
                'announced_date' => '2019-06-01',
                'pre_money_valuation' => 2500000000,
                'investors' => ['Kleiner Perkins', 'DST Global', 'IVP'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 425000000,
                'announced_date' => '2021-04-01',
                'pre_money_valuation' => 6975000000,
                'source_url' => 'https://techcrunch.com/2021/04/27/brex-more-than-doubles-valuation-to-7-4b-with-new-425m-funding-round/',
                'investors' => ['Tiger Global', 'Baillie Gifford', 'Durable Capital Partners', 'General Catalyst', 'GIC'],
            ],
            [
                'round_type' => 'series_d_extension',
                'amount' => 300000000,
                'announced_date' => '2022-01-01',
                'pre_money_valuation' => 12000000000,
                'source_url' => 'https://techcrunch.com/2022/01/11/brex-confirms-12-3b-valuation-hires-meta-exec-to-serve-as-its-chief-product-officer/',
                'investors' => ['Greenoaks', 'TCV'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedRampFunding(array $investors): void
    {
        $company = Company::where('slug', 'ramp')->first();
        if (!$company) {
            $this->command->warn('Company not found: ramp');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 7000000,
                'announced_date' => '2019-08-01',
                'investors' => ['Founders Fund'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 25000000,
                'announced_date' => '2020-03-01',
                'investors' => ['Founders Fund', 'Khosla Ventures'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 115000000,
                'announced_date' => '2021-03-01',
                'pre_money_valuation' => 1485000000,
                'source_url' => 'https://techcrunch.com/2021/03/29/ramp-hits-1-6b-valuation-confirms-115m-series-b/',
                'investors' => ['Thrive Capital', 'Stripe', 'Redpoint Ventures', 'BoxGroup', 'Neo'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 300000000,
                'announced_date' => '2021-08-01',
                'pre_money_valuation' => 3600000000,
                'source_url' => 'https://techcrunch.com/2021/08/04/ramp-raises-300m-series-c-at-3-9b-valuation/',
                'investors' => ['Spark Capital', 'Lux Capital', 'Coatue Management'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 200000000,
                'announced_date' => '2022-02-01',
                'pre_money_valuation' => 7900000000,
                'source_url' => 'https://techcrunch.com/2022/02/22/ramp-reaches-8-1b-valuation-a-year-after-being-valued-at-1-6b/',
                'investors' => ['Avenir Growth Capital', 'Altimeter Capital', 'Vista Equity Partners', 'Declaration Partners'],
            ],
            [
                'round_type' => 'series_d_extension',
                'amount' => 300000000,
                'announced_date' => '2023-08-01',
                'pre_money_valuation' => 5500000000,
                'source_url' => 'https://techcrunch.com/2023/08/02/fintech-ramp-cuts-its-valuation-by-28-with-new-300m-round/',
                'investors' => ['Sands Capital', 'Thrive Capital'],
            ],
            [
                'round_type' => 'series_d_extension',
                'amount' => 150000000,
                'announced_date' => '2024-04-01',
                'pre_money_valuation' => 7500000000,
                'source_url' => 'https://www.fintechnexus.com/fintech-nexus-newsletter-april-18-2024-ramp-closes-150m-funding-round/',
                'investors' => ['Khosla Ventures', 'Founders Fund', 'Sequoia Capital', 'Greylock', '8VC'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 500000000,
                'announced_date' => '2025-07-01',
                'pre_money_valuation' => 22000000000,
                'source_url' => 'https://techstartups.com/2025/11/17/ramp-hits-32b-valuation-with-new-300m-funding-round/',
                'investors' => ['ICONIQ Growth'],
            ],
            [
                'round_type' => 'series_e_extension',
                'amount' => 300000000,
                'announced_date' => '2025-11-17',
                'pre_money_valuation' => 31700000000,
                'source_url' => 'https://techstartups.com/2025/11/17/ramp-hits-32b-valuation-with-new-300m-funding-round/',
                'investors' => ['Lightspeed Venture Partners', 'Founders Fund', 'Coatue Management', 'D1 Capital Partners', 'Thrive Capital', 'GIC', 'Sutter Hill Ventures', 'Bessemer Venture Partners', 'Alpha Wave Global', 'Robinhood Ventures'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedChimeFunding(array $investors): void
    {
        $company = Company::where('slug', 'chime')->first();
        if (!$company) {
            $this->command->warn('Company not found: chime');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 3000000,
                'announced_date' => '2014-06-01',
                'investors' => ['Forerunner Ventures'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 8000000,
                'announced_date' => '2014-11-01',
                'investors' => ['Forerunner Ventures'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 18000000,
                'announced_date' => '2017-09-01',
                'investors' => ['Forerunner Ventures', 'Menlo Ventures'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 70000000,
                'announced_date' => '2018-05-01',
                'pre_money_valuation' => 430000000,
                'investors' => ['Menlo Ventures', 'Forerunner Ventures'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 200000000,
                'announced_date' => '2019-03-01',
                'pre_money_valuation' => 1300000000,
                'source_url' => 'https://techcrunch.com/2019/03/05/chime-raises-200-million-at-1-5-billion-valuation/',
                'investors' => ['ICONIQ Growth', 'General Atlantic', 'Menlo Ventures', 'DST Global'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 500000000,
                'announced_date' => '2020-09-01',
                'pre_money_valuation' => 14000000000,
                'source_url' => 'https://techcrunch.com/2020/09/18/chime-raises-another-485m/',
                'investors' => ['DST Global', 'General Atlantic', 'Access Industries', 'Tiger Global', 'Coatue Management'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 1100000000,
                'announced_date' => '2021-08-06',
                'pre_money_valuation' => 14000000000,
                'source_url' => 'https://www.cnbc.com/2021/08/13/chime-earns-big-valuation-jump-in-latest-financing-nears-ipo.html',
                'investors' => ['Sequoia Capital', 'SoftBank Vision Fund', 'General Atlantic', 'Tiger Global', 'Dragoneer Investment Group'],
            ],
            [
                'round_type' => 'series_g',
                'amount' => 750000000,
                'announced_date' => '2021-08-13',
                'pre_money_valuation' => 24250000000,
                'source_url' => 'https://www.cnbc.com/2021/08/13/chime-earns-big-valuation-jump-in-latest-financing-nears-ipo.html',
                'investors' => ['SoftBank Vision Fund', 'Sequoia Capital', 'General Atlantic', 'Tiger Global', 'Dragoneer Investment Group'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedWizFunding(array $investors): void
    {
        $company = Company::where('slug', 'wiz')->first();
        if (!$company) {
            $this->command->warn('Company not found: wiz');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'series_a',
                'amount' => 100000000,
                'announced_date' => '2020-12-09',
                'investors' => ['Sequoia Capital', 'Index Ventures', 'Cyberstarts'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 130000000,
                'announced_date' => '2021-05-25',
                'pre_money_valuation' => 1570000000,
                'investors' => ['Greenoaks', 'Sequoia Capital', 'Index Ventures', 'Cyberstarts'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 250000000,
                'announced_date' => '2022-02-01',
                'pre_money_valuation' => 5750000000,
                'source_url' => 'https://techcrunch.com/2022/02/14/wiz-raises-250m-at-6b-valuation/',
                'investors' => ['Insight Partners', 'Greenoaks', 'Sequoia Capital', 'Index Ventures'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 300000000,
                'announced_date' => '2023-02-27',
                'pre_money_valuation' => 9700000000,
                'source_url' => 'https://techcrunch.com/2023/02/27/wiz-raises-300m-at-10b-valuation/',
                'investors' => ['Lightspeed Venture Partners', 'Greenoaks', 'Index Ventures'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 1000000000,
                'announced_date' => '2024-05-07',
                'pre_money_valuation' => 11000000000,
                'source_url' => 'https://techcrunch.com/2024/05/07/wiz-raises-1b-at-12b-valuation-expanding-through-acquisitions/',
                'investors' => ['Andreessen Horowitz', 'Lightspeed Venture Partners', 'Thrive Capital', 'Greylock', 'Wellington Management', 'Greenoaks', 'Index Ventures', 'Sequoia Capital', 'Salesforce Ventures'],
            ],
            [
                'round_type' => 'series_e_extension',
                'amount' => 100000000,
                'announced_date' => '2024-08-07',
                'pre_money_valuation' => 12000000000,
                'investors' => ['SoftBank Vision Fund'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedAndurilFunding(array $investors): void
    {
        $company = Company::where('slug', 'anduril-industries')->first();
        if (!$company) {
            $this->command->warn('Company not found: anduril-industries');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 17000000,
                'announced_date' => '2017-08-01',
                'investors' => ['Founders Fund'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 41000000,
                'announced_date' => '2018-06-01',
                'investors' => ['Founders Fund', 'Andreessen Horowitz'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 114000000,
                'announced_date' => '2019-09-01',
                'pre_money_valuation' => 1786000000,
                'investors' => ['Founders Fund', 'Andreessen Horowitz', 'General Catalyst'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 200000000,
                'announced_date' => '2020-07-01',
                'pre_money_valuation' => 1700000000,
                'investors' => ['Andreessen Horowitz', 'Founders Fund', 'General Catalyst', 'Valor Equity Partners'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 450000000,
                'announced_date' => '2021-06-17',
                'pre_money_valuation' => 4250000000,
                'source_url' => 'https://techcrunch.com/2021/06/17/anduril-raises-450-million-at-4-6-billion-valuation/',
                'investors' => ['Founders Fund', 'Andreessen Horowitz', 'General Catalyst', 'D1 Capital Partners', '8VC'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 1480000000,
                'announced_date' => '2022-12-01',
                'pre_money_valuation' => 7020000000,
                'source_url' => 'https://techcrunch.com/2022/12/12/anduril-raises-1-48b-at-8-5b-valuation/',
                'investors' => ['WCM Investment Management', 'MVP Ventures', 'Lightspeed Venture Partners', 'US Innovative Technology Fund'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 1500000000,
                'announced_date' => '2024-08-07',
                'pre_money_valuation' => 12500000000,
                'source_url' => 'https://techcrunch.com/2024/08/07/anduril-raises-1-5b-to-hyper-scale-defense-production/',
                'investors' => ['Founders Fund', 'Sands Capital', 'Fidelity Investments', 'Counterpoint Global', 'Baillie Gifford', 'Altimeter Capital', 'Franklin Templeton'],
            ],
            [
                'round_type' => 'series_g',
                'amount' => 2500000000,
                'announced_date' => '2025-06-05',
                'pre_money_valuation' => 28000000000,
                'source_url' => 'https://www.cnbc.com/2025/06/05/anduril-valuation-founders-fund.html',
                'investors' => ['Founders Fund'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedVercelFunding(array $investors): void
    {
        $company = Company::where('slug', 'vercel')->first();
        if (!$company) {
            $this->command->warn('Company not found: vercel');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 2100000,
                'announced_date' => '2016-11-01',
                'investors' => [],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 21000000,
                'announced_date' => '2020-04-21',
                'investors' => ['CRV', 'Accel'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 40000000,
                'announced_date' => '2020-12-16',
                'investors' => ['Bedrock Capital', 'Geodesic Capital', 'CRV', 'Accel'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 102000000,
                'announced_date' => '2021-06-23',
                'pre_money_valuation' => 998000000,
                'source_url' => 'https://vercel.com/blog/vercel-funding-series-c-and-unicorn',
                'investors' => ['Bedrock Capital', 'Tiger Global', 'CRV', 'Accel', 'GV'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 150000000,
                'announced_date' => '2021-11-01',
                'pre_money_valuation' => 2350000000,
                'source_url' => 'https://vercel.com/blog/vercel-funding-series-d-and-valuation',
                'investors' => ['GGV Capital', 'Accel', 'Bedrock Capital', 'CRV', 'Geodesic Capital', 'Greenoaks', 'GV', '8VC', 'Salesforce Ventures', 'Tiger Global'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 250000000,
                'announced_date' => '2024-05-01',
                'pre_money_valuation' => 3000000000,
                'source_url' => 'https://news.crunchbase.com/cloud/vercels-cloud-web-applications-funding-valuation-accel/',
                'investors' => ['Accel', 'CRV', 'GV', 'Bedrock Capital', 'Geodesic Capital', 'Tiger Global', '8VC'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 300000000,
                'announced_date' => '2025-09-30',
                'pre_money_valuation' => 9000000000,
                'investors' => ['Accel', 'GIC'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedAirtableFunding(array $investors): void
    {
        $company = Company::where('slug', 'airtable')->first();
        if (!$company) {
            $this->command->warn('Company not found: airtable');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 3100000,
                'announced_date' => '2013-03-01',
                'investors' => ['Caffeinated Capital'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 7600000,
                'announced_date' => '2015-03-01',
                'investors' => ['CRV', 'Caffeinated Capital'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 52000000,
                'announced_date' => '2018-03-01',
                'pre_money_valuation' => 1048000000,
                'source_url' => 'https://techcrunch.com/2018/03/15/airtable-raises-52-million-to-put-the-database-market-into-play/',
                'investors' => ['CRV', 'Caffeinated Capital', 'Thrive Capital', 'Benchmark', 'Coatue Management'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 100000000,
                'announced_date' => '2018-11-01',
                'pre_money_valuation' => 1000000000,
                'investors' => ['Thrive Capital', 'Coatue Management', 'CRV', 'Caffeinated Capital', 'Benchmark'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 185000000,
                'announced_date' => '2020-09-01',
                'pre_money_valuation' => 2400000000,
                'source_url' => 'https://techcrunch.com/2020/09/14/airtable-raises-185-million-at-a-2-58-billion-valuation/',
                'investors' => ['Thrive Capital', 'D1 Capital Partners', 'Coatue Management', 'CRV', 'Benchmark'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 270000000,
                'announced_date' => '2021-03-15',
                'pre_money_valuation' => 5500000000,
                'source_url' => 'https://techcrunch.com/2021/03/15/airtable-is-now-valued-at-5-77b-with-a-fresh-270-million-in-series-e-funding/',
                'investors' => ['Greenoaks', 'WndrCo', 'Caffeinated Capital', 'CRV', 'Thrive Capital'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 735000000,
                'announced_date' => '2021-12-13',
                'pre_money_valuation' => 10265000000,
                'source_url' => 'https://www.cnbc.com/2021/12/13/low-code-software-start-up-airtable-worth-11-billion-in-new-funding.html',
                'investors' => ['XN', 'Franklin Templeton', 'J.P. Morgan', 'MSD Capital', 'Salesforce Ventures', 'Silver Lake', 'Benchmark', 'Caffeinated Capital', 'Coatue Management', 'D1 Capital Partners', 'Greenoaks', 'ICONIQ Growth', 'Thrive Capital'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedRelativitySpaceFunding(array $investors): void
    {
        $company = Company::where('slug', 'relativity-space')->first();
        if (!$company) {
            $this->command->warn('Company not found: relativity-space');
            return;
        }
        if ($company->fundingRounds()->exists()) return;

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 2500000,
                'announced_date' => '2016-05-01',
                'investors' => ['Y Combinator', 'Social Capital'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 9500000,
                'announced_date' => '2017-03-01',
                'investors' => ['Social Capital', 'Y Combinator'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 35000000,
                'announced_date' => '2018-10-01',
                'investors' => ['Social Capital', 'Y Combinator', 'Playground Global'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 140000000,
                'announced_date' => '2019-10-01',
                'pre_money_valuation' => 1010000000,
                'source_url' => 'https://techcrunch.com/2019/10/01/relativity-space-enters-the-unicorn-club/',
                'investors' => ['Bond Capital', 'Tribe Capital', 'Social Capital', 'Y Combinator', 'Playground Global'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 500000000,
                'announced_date' => '2020-11-01',
                'pre_money_valuation' => 1800000000,
                'source_url' => 'https://techcrunch.com/2020/11/23/relativity-space-raises-500-million-to-build-entire-rockets-with-3d-printing/',
                'investors' => ['Fidelity Investments', 'K5 Global', 'Tiger Global', 'Baillie Gifford'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 650000000,
                'announced_date' => '2021-06-08',
                'pre_money_valuation' => 3550000000,
                'source_url' => 'https://techcrunch.com/2021/06/08/relativity-space-launches-its-valuation-to-4-2b-with-650m-in-new-funding/',
                'investors' => ['Fidelity Investments', 'BlackRock', 'Centricus', 'Coatue Management', 'Soroban Capital', 'Baillie Gifford', 'K5 Global', 'Tiger Global', 'Tribe Capital', 'Mark Cuban', 'Jared Leto'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function createFundingRounds(Company $company, array $rounds, array $investors): void
    {
        foreach ($rounds as $roundData) {
            $investorNames = $roundData['investors'] ?? [];
            unset($roundData['investors']);

            $roundData['company_id'] = $company->id;

            $round = FundingRound::create($roundData);

            foreach ($investorNames as $index => $name) {
                if (isset($investors[$name])) {
                    $round->investors()->attach($investors[$name]->id, [
                        'is_lead' => $index === 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $this->command->warn("Investor not found: {$name} (for {$company->name} {$round->round_type})");
                }
            }
        }
    }
}
