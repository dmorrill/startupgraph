<?php

namespace Database\Seeders;

use App\Models\Investor;
use App\Models\FundingRound;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InvestorSeeder extends Seeder
{
    public function run(): void
    {
        $investors = [
            ['name' => 'Sequoia Capital', 'type' => 'vc', 'website' => 'https://www.sequoiacap.com', 'description' => 'Legendary Silicon Valley venture capital firm backing iconic companies from Apple to Stripe.', 'portfolio_count' => 1500],
            ['name' => 'Andreessen Horowitz', 'type' => 'vc', 'website' => 'https://a16z.com', 'description' => 'Software-focused VC firm known for bold bets on transformative technology companies.', 'portfolio_count' => 1000],
            ['name' => 'Accel', 'type' => 'vc', 'website' => 'https://www.accel.com', 'description' => 'Global venture capital firm investing in seed through growth stage technology companies.', 'portfolio_count' => 800],
            ['name' => 'Founders Fund', 'type' => 'vc', 'website' => 'https://foundersfund.com', 'description' => 'Peter Thiel-founded VC firm investing in revolutionary technology companies.', 'portfolio_count' => 300],
            ['name' => 'Lightspeed Venture Partners', 'type' => 'vc', 'website' => 'https://lsvp.com', 'description' => 'Multi-stage VC firm with global presence investing in enterprise and consumer tech.', 'portfolio_count' => 500],
            ['name' => 'Khosla Ventures', 'type' => 'vc', 'website' => 'https://www.khoslaventures.com', 'description' => 'Deep tech and climate-focused VC firm founded by Sun Microsystems co-founder.', 'portfolio_count' => 400],
            ['name' => 'General Catalyst', 'type' => 'vc', 'website' => 'https://www.generalcatalyst.com', 'description' => 'Venture capital firm focused on early and growth stage investments in technology.', 'portfolio_count' => 600],
            ['name' => 'Index Ventures', 'type' => 'vc', 'website' => 'https://www.indexventures.com', 'description' => 'European-founded global VC backing companies from seed to IPO.', 'portfolio_count' => 400],
            ['name' => 'Tiger Global Management', 'type' => 'vc', 'website' => 'https://www.tigerglobal.com', 'description' => 'Crossover fund investing in public and private technology companies globally.', 'portfolio_count' => 350],
            ['name' => 'Coatue Management', 'type' => 'vc', 'website' => 'https://www.coatue.com', 'description' => 'Technology-focused investment firm spanning public and private markets.', 'portfolio_count' => 300],
            ['name' => 'Y Combinator', 'type' => 'accelerator', 'website' => 'https://www.ycombinator.com', 'description' => 'Premier startup accelerator that has funded Airbnb, Stripe, DoorDash, and 4000+ startups.', 'portfolio_count' => 4000],
            ['name' => 'GV (Google Ventures)', 'type' => 'corporate', 'website' => 'https://www.gv.com', 'description' => 'Google parent Alphabet\'s venture capital arm investing across life science and technology.', 'portfolio_count' => 500],
            ['name' => 'Intel Capital', 'type' => 'corporate', 'website' => 'https://www.intelcapital.com', 'description' => 'Intel\'s global investment organization backing disruptive technology companies.', 'portfolio_count' => 350],
            ['name' => 'Salesforce Ventures', 'type' => 'corporate', 'website' => 'https://www.salesforceventures.com', 'description' => 'Strategic investment arm of Salesforce focused on enterprise cloud ecosystem.', 'portfolio_count' => 400],
            ['name' => 'Naval Ravikant', 'type' => 'angel', 'website' => 'https://nav.al', 'description' => 'Prolific angel investor and AngelList co-founder who has backed 200+ startups.', 'portfolio_count' => 200],
            ['name' => 'Ron Conway', 'type' => 'angel', 'website' => null, 'description' => 'Legendary Silicon Valley angel investor known as the godfather of angel investing.', 'portfolio_count' => 700],
            ['name' => 'Elad Gil', 'type' => 'angel', 'website' => 'https://eladgil.com', 'description' => 'Entrepreneur and angel investor, author of High Growth Handbook.', 'portfolio_count' => 150],
            ['name' => 'NEA (New Enterprise Associates)', 'type' => 'vc', 'website' => 'https://www.nea.com', 'description' => 'One of the largest and most active VCs globally, investing across technology and healthcare.', 'portfolio_count' => 1000],
            ['name' => 'Benchmark', 'type' => 'vc', 'website' => 'https://www.benchmark.com', 'description' => 'Elite early-stage VC firm known for equal partnership and iconic investments.', 'portfolio_count' => 300],
            ['name' => 'Greylock Partners', 'type' => 'vc', 'website' => 'https://greylock.com', 'description' => 'Storied VC firm specializing in early-stage consumer and enterprise investments.', 'portfolio_count' => 400],
        ];

        foreach ($investors as $data) {
            Investor::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, ['slug' => Str::slug($data['name'])])
            );
        }

        // Link some investors to existing funding rounds
        $allInvestors = Investor::all();
        $rounds = FundingRound::all();

        if ($rounds->count() && $allInvestors->count()) {
            foreach ($rounds as $round) {
                // Skip if already has investors
                if ($round->investors()->count()) continue;

                // Randomly assign 1-3 investors
                $count = rand(1, 3);
                $selected = $allInvestors->random(min($count, $allInvestors->count()));
                foreach ($selected as $i => $investor) {
                    $round->investors()->attach($investor->id, ['is_lead' => $i === 0]);
                }
            }
        }
    }
}
